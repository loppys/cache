<?php

namespace Vengine\Cache\Storage;

use Vengine\Cache\Drivers\ConfigCacheDriver;
use Vengine\Cache\Drivers\FileDriver;
use Vengine\Cache\Drivers\MemcacheDriver;
use Vengine\Cache\Drivers\RedisCacheDriver;
use Vengine\Cache\Drivers\RoutesCacheDriver;
use Vengine\Cache\Drivers\TemplateCacheDriver;

class DriverStorage
{
    public const FILE_DRIVER = FileDriver::class;

    public const DEFAULT_DRIVERS = [
        'file' => self::FILE_DRIVER,
    ];

    public const SPECIFIC_DRIVERS = [];

    public const SETTINGS = [
        'default' => [
            'enabled' => [
                'value' => true,
                'type' => ConfigTypes::PROPERTY
            ],
            'lifetime' => [
                // 1 hour
                'value' => 3600,
                'type' => ConfigTypes::PROPERTY
            ]
        ],
        DriverStorage::FILE_DRIVER => [
            'export' => [
                'configs' => [
                    'default'
                ],
                'options' => [
                    [
                        'name' => 'config_file_ext',
                        'type' => ConfigTypes::CONFIG_EXT
                    ]
                ]
            ],
            'folder' => [
                'alias' => 'root',
                'value' => '/_cache/files/',
                'type' => ConfigTypes::DIR,
            ]
        ],
    ];
}
