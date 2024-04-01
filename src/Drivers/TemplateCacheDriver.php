<?php

namespace Vengine\Cache\Drivers;

use RuntimeException;

class TemplateCacheDriver extends FileDriver
{
    protected bool $useSerialize = false;

    protected function getPath(string $key): string
    {
        $fileName = md5(sha1($key));
        $subDir = substr($fileName, 3, 3);
        $subSubDir = substr($fileName, 1, 2); // :D

        $fullPath = $this->config->getFolder() . DIRECTORY_SEPARATOR . $subDir . DIRECTORY_SEPARATOR . $subSubDir;

        if (!is_dir($fullPath) && !mkdir($fullPath, 0777, true) && !is_dir($fullPath)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $fullPath));
        }

        return $fullPath . DIRECTORY_SEPARATOR . $fileName . '.php';
    }
}