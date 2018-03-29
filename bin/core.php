<?php

require_once __DIR__.'/../vendor/autoload.php';

use \React\Http\Server;
use \WyriHaximus\React\Http\Middleware\WebrootPreloadMiddleware;

$q = [];
$loop = React\EventLoop\Factory::create();

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple['loop'] = React\EventLoop\Factory::create();
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\MicroTimeProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\TwigProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\DatabaseProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\HeaderStorageProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreControllersProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreStorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreRoutingProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreSummaryProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ImagickProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerStorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerControllersProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerRoutingProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ClientForServerAPIProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\DeliveryKernelProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\KernelProvider(), ['router' => $pimple['coreRouter']]);

$webRoot = __DIR__ . '/../webroot/';
$server = new Server([new WebrootPreloadMiddleware($webRoot), [$pimple['kernel'], 'handle']]);
$socket = new React\Socket\Server($pimple['config']['manager']['bind'], $pimple['loop']);
$server->listen($socket);
print 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
while (true) {
    $pimple['loop']->run();
}