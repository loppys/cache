<?php

namespace Vengine\Cache\Drivers;

use DateInterval;

class RoutesCacheDriver extends AbstractDriver
{
    protected function hasValue(string $key): bool
    {
        return file_exists($this->config->getFolder() . $key);
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