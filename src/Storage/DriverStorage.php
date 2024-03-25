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

    public const MEMCACHE_DRIVER = MemcacheDriver::class;

    public const CONFIG_DRIVER = ConfigCacheDriver::class;

    public const REDIS_DRIVER = RedisCacheDriver::class;

    public const TEMPLATE_DRIVER = TemplateCacheDriver::class;

    public const ROUTES_DRIVER = RoutesCacheDriver::class;

    public const DEFAULT_DRIVERS = [
        'file' => self::FILE_DRIVER,
        'memcache' => self::MEMCACHE_DRIVER,
        'config' => self::CONFIG_DRIVER,
        'redis' => self::REDIS_DRIVER,
    ];

    public const SPECIFIC_DRIVERS = [
        'template' => self::TEMPLATE_DRIVER,
        'routes' => self::ROUTES_DRIVER,
    ];

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
        DriverStorage::TEMPLATE_DRIVER => [
            'export' => [
                'configs' => [
                    'default'
                ]
            ]
        ],
        DriverStorage::CONFIG_DRIVER => [
            'export' => [
                'configs' => [
                    DriverStorage::FILE_DRIVER
                ],
                'options' => [
                    [
                        'name' => 'config_file_ext',
                        'type' => ConfigTypes::CONFIG_EXT
                    ]
                ]
            ]
        ],
        DriverStorage::ROUTES_DRIVER => [
            'export' => [
                'configs' => [
                    DriverStorage::FILE_DRIVER
                ],
                'options' => [
                    [
                        'name' => 'config_file_ext',
                        'type' => ConfigTypes::CONFIG_EXT
                    ]
                ]
            ]
        ],
    ];
}
