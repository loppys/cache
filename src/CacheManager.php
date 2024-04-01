<?php

namespace Vengine\Cache;

use RuntimeException;
use Vengine\Cache\config\Configurator;
use Vengine\Cache\Drivers\AbstractDriver;
use Vengine\Cache\Drivers\ConfigCacheDriver;
use Vengine\Cache\Drivers\FileDriver;
use Vengine\Cache\Drivers\MemcacheDriver;
use Vengine\Cache\Drivers\RedisCacheDriver;
use Vengine\Cache\Drivers\RoutesCacheDriver;
use Vengine\Cache\Drivers\TemplateCacheDriver;
use Vengine\Cache\Exceptions\BuildConfigException;
use Vengine\Cache\Exceptions\UniqueOptionException;
use Vengine\Cache\Interfaces\ConfiguratorInterface;
use Vengine\Cache\Storage\DriverStorage;

/**
 * @property-read FileDriver $file
 * @property-read MemcacheDriver $memcache
 * @property-read ConfigCacheDriver $config
 * @property-read RedisCacheDriver $redis
 * @property-read TemplateCacheDriver $template
 * @property-read RoutesCacheDriver $routes
 */
class CacheManager
{
    /**
     * @var array<AbstractDriver>
     */
    protected array $drivers = [];

    protected ConfiguratorInterface $configurator;

    public function __construct(?ConfiguratorInterface $configurator = null)
    {
        if ($configurator === null) {
            $configurator = new Configurator();
        }

        $this->configurator = $configurator;
    }

    /**
     * @throws BuildConfigException
     * @throws UniqueOptionException
     */
    public function createDriver(string $name): AbstractDriver
    {
        $driver = $this->getDriver($name);
        if ($driver !== null) {
            if (!$driver->getConfig()->isEnabled()) {
                $this->triggerNotice("cache driver '{$name}' disabled.");
            }

            return $driver;
        }

        if (empty($name) || $name === 'default') {
            $defaultDriver = $this->configurator->getDefaultDriver();

            if (class_exists($defaultDriver)) {
                $config = $this->configurator->buildConfig($defaultDriver);

                if (!$config->isEnabled()) {
                    $this->triggerNotice("cache driver '{$name}' disabled.");
                }

                $this->addDriver($name, new $defaultDriver($config));

                return $this->getDriver($name);
            }

            throw new RuntimeException('default driver not created');
        }

        $allDrivers = DriverStorage::DEFAULT_DRIVERS + DriverStorage::SPECIFIC_DRIVERS;
        if (!array_key_exists($name, $allDrivers)) {
            return $this->createDriver('default');
        }

        $driver = $allDrivers[$name];

        if (empty($driver)) {
            throw new RuntimeException('driver empty');
        }

        if (class_exists($driver)) {
            $config = $this->configurator->buildConfig($driver);
            $this->addDriver($name, new $driver($config));

            if (!$config->isEnabled()) {
                $this->triggerNotice("cache driver '{$name}' disabled.");
            }

            return $this->getDriver($name);
        }

        throw new RuntimeException('driver not created');
    }

    public function addDriver(string $name, AbstractDriver $driver): static
    {
        if (property_exists($driver, 'name')) {
            if (!empty($this->drivers[$driver->name])) {
                return $this;
            }

            $this->drivers[$driver->name] = $driver;
        } else {
            if (!empty($this->drivers[$name])) {
                return $this;
            }

            $this->drivers[$name] = $driver;
        }

        return $this;
    }

    public function getDriver(string $name): ?AbstractDriver
    {
        return $this->drivers[$name] ?? null;
    }

    public function getConfigurator(): ConfiguratorInterface
    {
        return $this->configurator;
    }

    public function __get(string $name)
    {
        return $this->createDriver($name);
    }

    private function triggerNotice(string $message): void
    {
        trigger_error($message);
    }
}
