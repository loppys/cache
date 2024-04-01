```
composer require vengine/cache

$manager = new \Vengine\Cache\CacheManager();

//Easy connect
$manager->{driver name} ( $manager->template->... )

//Manual create driver
$driver = $manager->createDriver('template', ['option' => 'example'])
$driver->...

```
