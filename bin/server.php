<?php

require_once __DIR__.'/../vendor/autoload.php';

use \React\Http\Server;
use \AVAllAC\PersistentBlockStorage\Provider\ServerRoutingProvider;
use \AVAllAC\PersistentBlockStorage\Provider\ServerControllersProvider;
use \AVAllAC\PersistentBlockStorage\Provider\KernelProvider;
use \AVAllAC\PersistentBlockStorage\Provider\ServerStorageManagerProvider;
use \AVAllAC\PersistentBlockStorage\Provider\DeliveryKernelProvider;
use \AVAllAC\PersistentBlockStorage\Provider\MicroTimeProvider;

$processManager = new \AVAllAC\PersistentBlockStorage\ProcessManager();
$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple['loop'] = React\EventLoop\Factory::create();
$pimple->register(new MicroTimeProvider());
$pimple->register(new DeliveryKernelProvider());
$pimple->register(new ServerStorageManagerProvider());
$pimple->register(new ServerControllersProvider());
$pimple->register(new ServerRoutingProvider());
$pimple->register(new KernelProvider(), ['router' => $pimple['serverRouter']]);

$server = new Server([$pimple['deliveryKernel'], 'handle']);
$adminServer = new Server([$pimple['kernel'], 'handle']);

$pimple['loop']->addPeriodicTimer(1, function () {
    pcntl_signal_dispatch();
});

$socket = new React\Socket\Server($pimple['config']['server']['bind'], $pimple['loop']);
$server->listen($socket);
print 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
$processManager->fork(8);
if ($processManager->isMaster()) {
    $socketAdmin = new React\Socket\Server($pimple['config']['server']['bindAdmin'], $pimple['loop']);
    $adminServer->listen($socketAdmin);
}
$pimple['loop']->run();
