<?php

namespace Vengine\Cache\config;

use Vengine\Cache\Exceptions\UniqueOptionException;
use Vengine\Cache\traits\ArrayAccessTrait;
use ArrayAccess;

class DriverConfig implements ArrayAccess
{
    use ArrayAccessTrait;

    protected string $driverName = '';

    protected string $extension = '';

    protected bool $enabled = false;

    protected int $lifetime = 900;

    protected array $options = [];

    private string $_instancePath;
    private int $_instanceLifetime = 900;

    public function __construct(string $name = '')
    {
        $ds = DIRECTORY_SEPARATOR;
        $this->_instancePath = __DIR__ . $ds . '_cache' . $ds . '_instances' . $ds . md5($name) . '.drc';

        $this->setDriverName($name);
    }

    public function getInstancePath(): string
    {
        return $this->_instancePath;
    }

    public function getInstanceLifetime(): int
    {
        return $this->_instanceLifetime;
    }

    public function getDriverName(): string
    {
        return $this->driverName;
    }

    public function setDriverName(string $driverName): static
    {
        $this->driverName = $driverName;

        return $this;
    }

    public function getExtension(): string
    {
        return $this->extension;
    }

    public function setExtension(string $extension): static
    {
        $this->extension = $extension;

        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getLifetime(): int
    {
        return $this->lifetime;
    }

    public function setLifetime(int $lifetime): static
    {
        $this->lifetime = $lifetime;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;

    }

    public function setOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @throws UniqueOptionException
     */
    public function addOption(string $name, mixed $value): static
    {
        if (array_key_exists($name, $this->options)) {
            throw new UniqueOptionException("option {$name} already exists");
        }

        $this->options[$name] = $value;

        return $this;
    }

    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public static function fromArray(array $data): static
    {
        $obj = new static();

        foreach ($data as $key => $value) {
            $obj->{$key} = $value;
        }

        return $obj;
    }
}
