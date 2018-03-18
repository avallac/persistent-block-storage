<?php

require_once __DIR__.'/../vendor/autoload.php';

use \React\Http\Server;

$processManager = new \AVAllAC\PersistentBlockStorage\ProcessManager();
$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple['loop'] = React\EventLoop\Factory::create();
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\MicroTimeProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\KernelProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\DeliveryKernelProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerStorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerControllersProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerRoutingProvider());

$server = new Server([$pimple['deliveryKernel'], 'handle']);
$adminServer = new Server([$pimple['kernel'], 'handle']);

$pimple['loop']->addPeriodicTimer(1, function () {
    pcntl_signal_dispatch();
});

$socket = new React\Socket\Server($pimple['config']['server']['bind'], $pimple['loop']);
$server->listen($socket);
print 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
$processManager->fork(1);
if ($processManager->isMaster()) {
    $socketAdmin = new React\Socket\Server($pimple['config']['server']['bindAdmin'], $pimple['loop']);
    $adminServer->listen($socketAdmin);
}
$pimple['loop']->run();
