<?php

require_once __DIR__.'/../vendor/autoload.php';

use \React\Http\Server;
use \WyriHaximus\React\Http\Middleware\WebrootPreloadMiddleware;

$q = [];
$loop = React\EventLoop\Factory::create();

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple['Loop'] = React\EventLoop\Factory::create();
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\LoggerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\MicroTimeProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\TwigProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\DatabaseProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\HeaderStorageProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerDeliveryRoutingProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerAdminRoutingProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ClientForServerAPIProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\StatProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreStorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\AverageTimeProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreControllersProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreRoutingProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreVolumesSummaryProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\KernelProvider(), ['Router' => $pimple['CoreRouter']]);

/** @var \AVAllAC\PersistentBlockStorage\Service\Logger $logger */
$logger = $pimple[\AVAllAC\PersistentBlockStorage\Service\Logger::class];
$logger->initConsoleOutput();
$webRoot = __DIR__ . '/../webroot/';
$server = new Server([new WebrootPreloadMiddleware($webRoot), [$pimple['Kernel'], 'handle']]);
$socket = new React\Socket\Server($pimple['config']['core']['bind'], $pimple['Loop']);
$server->listen($socket);
$logger->info(
    'init',
    'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress())
);
while (true) {
    $pimple['Loop']->run();
}
