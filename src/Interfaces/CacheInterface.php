<?php

namespace Vengine\Cache\Interfaces;

use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Vengine\Cache\config\DriverConfig;

interface CacheInterface extends PsrCacheInterface
{
    public function setConfig(DriverConfig $config): static;

    public function getConfig(): DriverConfig;
}