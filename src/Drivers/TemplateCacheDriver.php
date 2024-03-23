<?php

namespace Vengine\Cache\Drivers;

use DateInterval;

class TemplateCacheDriver extends AbstractDriver
{
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