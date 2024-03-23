<?php

namespace Vengine\Cache\traits;

trait MagicMethodsTrait
{
    public function __get($name): mixed
    {
        return $this->smartGet($name);
    }

    public function __set($name, $value)
    {
        return $this->smartSet($name, $value);
    }

    public function smartGet($name): mixed
    {
        $method = 'get' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        if (property_exists($this, $name)) {
            return $this->{$name};
        }

        $this->smartGetError($name);

        return null;
    }

    protected function smartGetError($name): void
    {
        trigger_error(
            sprintf('Unknown property %s::%s', get_class($this), $name),
            E_USER_WARNING
        );
    }

    public function smartSet($name, $value): mixed
    {
        $method = 'set' . ucfirst($name);
        if (method_exists($this, $method)) {
            return $this->{$method}($value) ?? true;
        }

        if (!property_exists($this, $name)) {
            $this->smartSetError($name);
        }

        return $this->{$name} = $value;
    }

    protected function smartSetError($name): void
    {
        trigger_error(
            sprintf('Unknown property %s::%s', get_class($this), $name),
            E_USER_WARNING
        );
    }

    public function __isset(string $name): bool
    {
        return $this->smartIsset($name);
    }

    public function smartIsset($name): bool
    {
        return $this->hasProperty($name)
            ||  $this->__get($name) !== null;
    }

    public function __unset(string $name): void
    {
        $this->smartUnset($name);
    }

    public function smartUnset($name): void
    {
        $this->__set($name, null);
    }

    /**
     * @see canGetProperty()
     * @see canSetProperty()
     */
    public function hasProperty(string $name, bool $checkVars = true): bool
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }

    /**
     * @see canSetProperty()
     */
    public function canGetProperty(string $name, bool $checkVars = true): bool
    {
        return method_exists($this, 'get' . $name) || ($checkVars && property_exists($this, $name));
    }

    /**
     * @see canGetProperty()
     */
    public function canSetProperty(string $name, bool $checkVars = true): bool
    {
        return method_exists($this, 'set' . $name) || ($checkVars && property_exists($this, $name));
    }

    public function hasMethod(string $name): bool
    {
        return method_exists($this, $name);
    }
}
