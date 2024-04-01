<?php

namespace Vengine\Cache\Drivers;

use DateInterval;
use Vengine\Cache\config\DriverConfig;

class RedisCacheDriver extends AbstractDriver
{
    public function __construct(?DriverConfig $config = null, bool $configIgnore = false)
    {
        parent::__construct($config, $configIgnore);

        if (!extension_loaded('redis')) {
            $this->getConfig()->setEnabled(false);
        }
    }

    protected function hasValue(string $key): bool
    {
        return true;
    }

    protected function getValue(string $key): mixed
    {
        return true;
    }

    protected function setValue(string $key, mixed $data, DateInterval|int|null $ttl = null): bool
    {
        return true;
    }

    protected function deleteValue(string $key): bool
    {
        return true;
    }

    public function clear(): bool
    {
        return true;
    }
}