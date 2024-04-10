<?php

namespace Vengine\Cache\Drivers;

use DateInterval;
use Vengine\Cache\config\DriverConfig;
use Vengine\Cache\config\ext\FileDriverConfig;

class FileDriver extends AbstractDriver
{
    protected FileDriverConfig|DriverConfig $config;

    protected function hasValue(string $key): bool
    {
        return !empty($this->get($key));
    }

    protected function getValue(string $key): mixed
    {
        $path = $this->getPath($key);
        if (file_exists($path) && !$this->isExpire($path)) {
            return file_get_contents($path);
        }

        return null;
    }

    protected function setValue(string $key, mixed $data, DateInterval|int|null $ttl = null): bool
    {
        $path = $this->getPath($key);
        if (file_exists($path) && !$this->isExpire($path)) {
            return false;
        }

        if (file_put_contents($path, $data, LOCK_EX) !== false) {
            $this->setExpirationTime($path, $ttl);
        }

        return true;
    }

    protected function deleteValue(string $key): bool
    {
        return unlink($this->getPath($key));
    }

    public function clear(): bool
    {
        if (!$this->config instanceof FileDriverConfig) {
            return false;
        }

        foreach (scandir($this->config->getFolder()) as $f) {
            if (is_file($f) && $this->isExpire($f)) {
                $this->deleteValue($f);
            }
        }

        return true;
    }

    public function getPath(string $key): string
    {
        return $this->config->getFolder() . DIRECTORY_SEPARATOR . md5(sha1($key)) . '.drc';
    }

    protected function setExpirationTime($fullPath, $timeout): bool
    {
        return @touch($fullPath, $timeout);
    }

    protected function isExpire($fullPath): bool
    {
        return filemtime($fullPath) < time();
    }
}
