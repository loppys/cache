<?php

namespace Vengine\Cache\Drivers;

use Vengine\Cache\config\DriverConfig;
use Vengine\Cache\Exceptions\ConfigNotFoundException;
use Vengine\Cache\Interfaces\CacheInterface;
use RuntimeException;
use DateInterval;
use DateTime;

abstract class AbstractDriver implements CacheInterface
{
    private array $magicPropertyList = [];

    protected DriverConfig $config;

    protected bool $configIgnore = false;

    protected int $lifetime = 900;

    /**
     * @throws ConfigNotFoundException
     */
    public function __construct(?DriverConfig $config = null, bool $configIgnore = false)
    {
        $this->configIgnore = $configIgnore;

        if (!$configIgnore) {
            if ($config === null) {
                throw new ConfigNotFoundException("config is null");
            }

            $this->setConfig($config);
        } else {
            $this->setConfig(new DriverConfig('dummy'));
        }
    }

    public function has(string $key): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        return $this->hasValue($this->buildKey($key));
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        $value = $this->getValue($this->buildKey($key));
        return $value === null ? $default : @unserialize($value);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        return $this->setValue(
            $this->buildKey($key),
            serialize($value),
            $this->prepareTTL($ttl)
        );
    }

    public function delete(string $key): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        return $this->deleteValue($this->buildKey($key));
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        if (!$this->config->isEnabled()) {
            return [false];
        }

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        $result = true;
        foreach ($values as $key => $value) {
            $result = $this->set($key, $value, $ttl) && $result;
        }
        return $result;
    }

    public function deleteMultiple($keys, $default = null): bool
    {
        if (!$this->config->isEnabled()) {
            return false;
        }

        $result = true;
        foreach ($keys as $key) {
            $result = $this->delete($key) && $result;
        }

        return $result;
    }

    public function setConfig(DriverConfig $config): static
    {
        $this->config = $config;

        return $this;
    }

    public function getConfig(): DriverConfig
    {
        if ($this->configIgnore && empty($this->config)) {
            return new DriverConfig('dummy');
        }

        return $this->config;
    }

    public function __set(string $name, $value): void
    {
        $this->magicPropertyList[$name] = $value;
    }

    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->magicPropertyList);
    }

    public function __get(string $name)
    {
        return $this->magicPropertyList[$name] ?? null;
    }

    public function buildKey(mixed $key): string
    {
        if (!is_string($key)) {
            $key = serialize($key);
        }

        return md5($key);
    }

    protected function prepareTTL($ttl): int
    {
        return $this->TTLToInt($ttl) + time();
    }

    protected function TTLToInt($ttl): int
    {
        if (is_object($ttl)) {
            if ($ttl instanceof DateInterval) {
                return (new DateTime())->setTimestamp(0)->add($ttl)->getTimestamp();
            }

            throw new RuntimeException('Support only DateInterval objects');
        }

        if ($ttl) {
            return (int)$ttl;
        }

        return $this->lifetime;
    }

    abstract protected function hasValue(string $key): bool;

    abstract protected function getValue(string $key): mixed;

    abstract protected function setValue(string $key, mixed $data, null|int|DateInterval $ttl = null): bool;

    abstract protected function deleteValue(string $key): bool;
}