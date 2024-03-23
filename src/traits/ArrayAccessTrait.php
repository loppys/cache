<?php

namespace Vengine\Cache\traits;

trait ArrayAccessTrait
{
    use MagicMethodsTrait;

    public function offsetExists($offset): bool
    {
        return $this->__isset($offset);
    }

    #[\ReturnTypeWillChange]
    public function offsetGet(mixed $offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value): void
    {
        $this->__set($offset, $value);
    }

    public function offsetUnset($offset): void
    {
        $this->__set($offset, null);
    }
}
