<?php

namespace Vengine\Cache\Interfaces;

use Vengine\Cache\config\DriverConfig;
use Vengine\Cache\Exceptions\AliasException;
use Vengine\Cache\Exceptions\BuildConfigException;
use Vengine\Cache\Exceptions\UniqueOptionException;

interface ConfiguratorInterface
{
    /**
     * @throws BuildConfigException
     * @throws UniqueOptionException
     */
    public function buildConfig(string $driver): DriverConfig;

    public function getDefaultDriver(): string;

    public function setDefaultDriver(string $defaultDriver): static;

    /**
     * @throws UniqueOptionException
     */
    public function addOption(string $name, mixed $value): static;

    public function getOption(string $name): mixed;

    public function getOptions(): array;

    public function getSetting(string $driver, string $settingName): mixed;

    public function setSetting(string $driver, string $settingName, mixed $value): static;

    /**
     * @throws AliasException
     */
    public function setAlias(string $name, mixed $value, bool $system = false): static;

    public function getAlias(string $name): mixed;

    public function getAliasList(): array;
}