<?php

namespace Vengine\Cache\Exceptions;

use Psr\SimpleCache\CacheException;
use Exception;

class BaseException extends Exception implements CacheException
{
}