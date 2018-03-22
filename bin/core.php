<?php

require_once __DIR__.'/../vendor/autoload.php';

use \React\Http\Server;
use \WyriHaximus\React\Http\Middleware\WebrootPreloadMiddleware;

$loop = React\EventLoop\Factory::create();

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple['loop'] = React\EventLoop\Factory::create();
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\TwigProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\DatabaseProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\KernelProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\HeaderStorageProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreControllersProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreStorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreRoutingProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreSummaryProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ImagickProvider());

$webRoot = __DIR__ . '/../webroot/';
$server = new Server([new WebrootPreloadMiddleware($webRoot), [$pimple['kernel'], 'handle']]);
$socket = new React\Socket\Server($pimple['config']['manager']['bind'], $pimple['loop']);
$server->listen($socket);
print 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
$pimple['loop']->run();
