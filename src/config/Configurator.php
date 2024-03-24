<?php

namespace Vengine\Cache\config;

use RuntimeException;
use Vengine\Cache\config\ext\FileDriverConfig;
use Vengine\Cache\Exceptions\AliasException;
use Vengine\Cache\Exceptions\BuildConfigException;
use Vengine\Cache\Exceptions\UniqueOptionException;
use Vengine\Cache\Interfaces\ConfiguratorInterface;
use Vengine\Cache\Storage\DriverStorage;
use Vengine\Cache\Storage\ConfigTypes;

class Configurator implements ConfiguratorInterface
{
    protected string $defaultDriver;

    protected array $driverOptions = [];

    private array $aliasList = [];

    private array $systemAliasList = [];

    protected array $settingList = DriverStorage::SETTINGS;

    /**
     * @throws AliasException
     * @throws UniqueOptionException
     */
    public function __construct(string $defaultCacheDriver = DriverStorage::FILE_DRIVER)
    {
        $this->setAlias('root', $_SERVER['DOCUMENT_ROOT'] ?: __DIR__, true);
        $this->setDefaultDriver($defaultCacheDriver);
        $this->addOption('config_file_ext', FileDriverConfig::class);
        $this->addOption('test', 'test');
    }

    /**
     * @throws BuildConfigException
     * @throws UniqueOptionException
     */
    public function buildConfig(string $driver): DriverConfig
    {
        if (empty($driver)) {
            throw new BuildConfigException('empty driver name');
        }

        $config = new DriverConfig($driver);
        $instancePath = $config->getInstancePath();

        if (file_exists($instancePath)) {
            return @unserialize(file_get_contents($instancePath));
        }

        if (empty($this->settingList[$driver])) {
            return $config;
        }

        $settings = $this->settingList[$driver];
        if (!empty($settings['export']) && is_array($settings['export'])) {
            $exportAllowed = [
                'configs',
                'options'
            ];

            foreach ($settings['export'] as $ek => $ev) {
                if (!in_array($ek, $exportAllowed, true)) {
                    continue;
                }

                if ($ek === 'configs' && is_array($ev)) {
                    $this->recursiveExportConfigs($ev, $driver);
                }

                if ($ek === 'options' && is_array($ev)) {
                    foreach ($ev as $option) {
                        if (is_string($option)) {
                            $optionValue = $this->getOption($option);

                            if (empty($optionValue)) {
                                continue;
                            }

                            $config->addOption($option, $optionValue);

                            continue;
                        }

                        if (
                            is_array($option)
                            && !empty($option['type'])
                            && $option['type'] === ConfigTypes::CONFIG_EXT
                        ) {
                            $extConfig = $this->getOption($option['name']);

                            if (
                                !empty($extConfig)
                                && class_exists($extConfig)
                                && method_exists($extConfig, 'fromArray')
                            ) {
                                $currentConfigData = $config->toArray();

                                $extConfig = $extConfig::fromArray($currentConfigData);

                                if ($extConfig instanceof DriverConfig) {
                                    $config = $extConfig;

                                    $config->setExtension($config::class);
                                }
                            }
                        }
                    }
                }
            }

            if (!empty($this->settingList[$driver]['export'])) {
                unset($this->settingList[$driver]['export']);
            }
        }

        $driverConfig = $this->settingList[$driver];
        foreach ($driverConfig as $dck => $dcv) {
            $prepareValue = $this->prepareProperty($dcv);
            if (empty($prepareValue)) {
                continue;
            }

            $config->{$dck} = $prepareValue;
        }

        if (!file_exists($instancePath)) {
            file_put_contents($instancePath, serialize($config));
        }

        return $config;
    }

    public function recursiveExportConfigs(array $export, string $driver = ''): void
    {
        foreach ($export as $cfg) {
            if (empty($this->settingList[$cfg])) {
                continue;
            }

            unset($this->settingList[$driver]['export']);

            $this->configMerge($this->settingList[$driver], $this->settingList[$cfg]);

            if (
                !empty($this->settingList[$driver]['export'])
                && !empty($this->settingList[$driver]['export']['configs'])
                && is_array($this->settingList[$driver]['export']['configs'])
            ) {
                $this->recursiveExportConfigs($this->settingList[$driver]['export']['configs'], $driver);
            }
        }
    }

    public function configMerge(array &$baseConfig, array $exportConfig): void
    {
        $baseConfig = array_merge($baseConfig, $exportConfig);
    }

    /**
     * @throws BuildConfigException
     */
    protected function prepareProperty(array $propertyInfo = []): mixed
    {
        if (empty($propertyInfo)) {
            throw new RuntimeException('empty property info');
        }

        if (empty($propertyInfo['type'])) {
            $propertyInfo['type'] = $this->propertyDefine($propertyInfo['value'] ?? null);
        }

        switch ($propertyInfo['type']) {
            case ConfigTypes::PROPERTY:
                if (empty($propertyInfo['value'])) {
                    throw new BuildConfigException('property value empty');
                }

                return $propertyInfo['value'];
            case ConfigTypes::DIR:
                $alias = null;
                if (!empty($propertyInfo['alias'])) {
                    $alias = $this->getAlias($propertyInfo['alias']);
                }

                if ($alias !== null) {
                    $path = $alias . $propertyInfo['value'];

                    if (is_dir($path)) {
                        return $path;
                    }

                    if (!mkdir($path) && !is_dir($path)) {
                        throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
                    }

                    return $path;
                }

                $path = $propertyInfo['value'];
                if (is_dir($path)) {
                    return $path;
                }

                if (!mkdir($path) && !is_dir($path)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
                }

                return $path ?? '';
            default:
                throw new RuntimeException('failed to process property');
        }
    }

    /**
     * @throws BuildConfigException
     */
    private function propertyDefine(mixed $property): int
    {
        if ($property === null) {
            throw new BuildConfigException('property define impossible');
        }

        if (is_object($property)) {
            return ConfigTypes::OBJ;
        }

        if (class_exists($property)) {
            return ConfigTypes::CLS;
        }

        if (is_dir($property)) {
            return ConfigTypes::DIR;
        }

        if (is_file($property)) {
            return ConfigTypes::FILE;
        }

        return ConfigTypes::PROPERTY;
    }

    public function getDefaultDriver(): string
    {
        return $this->defaultDriver;
    }

    public function setDefaultDriver(string $defaultDriver): static
    {
        $this->defaultDriver = $defaultDriver;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function addOption(string $name, mixed $value): static
    {
        if (array_key_exists($name, $this->driverOptions)) {
            throw new UniqueOptionException("option {$name} already exists");
        }

        $this->driverOptions[$name] = $value;

        return $this;
    }

    public function getOption(string $name): mixed
    {
        return $this->driverOptions[$name] ?? null;
    }

    public function getOptions(): array
    {
        return $this->driverOptions;
    }

    public function getAllSettings(): array
    {
        return $this->settingList;
    }

    public function getSetting(string $driver, string $settingName): mixed
    {
        if (!empty($this->settingList[$driver]) && !empty($this->settingList[$driver][$settingName])) {
            return $this->settingList[$driver][$settingName];
        }

        return null;
    }

    public function setSetting(string $driver, string $settingName, mixed $value): static
    {
        if (empty($this->settingList[$driver])) {
            $this->settingList[$driver] = [];
        }

        $this->settingList[$driver][$settingName] = $value;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setAlias(string $name, mixed $value, bool $system = false): static
    {
        if (array_key_exists($name, $this->systemAliasList)) {
            throw new AliasException('Changing the system alias is prohibited');
        }

        if ($system) {
            if (!empty($this->aliasList[$name])) {
                throw new AliasException('system alias must be unique');
            }

            $this->systemAliasList[$name] = true;
        }

        $this->aliasList[$name] = $value;

        return $this;
    }

    public function getAlias(string $name): mixed
    {
        return $this->aliasList[$name];
    }

    public function getAliasList(): array
    {
        return $this->aliasList;
    }
}
