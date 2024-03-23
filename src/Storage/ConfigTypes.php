<?php

namespace Vengine\Cache\Storage;

class ConfigTypes
{
    public const DEFINE = -1;

    public const DIR = 1;

    public const FILE = 2;

    /**
     * not detected automatically
     */
    public const PROPERTY = 3;

    /**
     * Class
     */
    public const CLS = 4;

    /**
     * There will be an attempt to call object::getInstance()
     */
    public const OBJ = 5;

    public const CONFIG_EXT = 6;
}