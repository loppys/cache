```
composer require vengine/cache

$manager = new \Vengine\Cache\CacheManager();

//Easy connect
$manager->{driver name}->set();
// example $manager->template->set()

//Manual create driver
$driver = $manager->createDriver('template', ['option' => 'example']);
$driver->set();

```
