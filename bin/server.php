<?php

require_once __DIR__.'/../vendor/autoload.php';

use \React\Http\Server;

$loop = React\EventLoop\Factory::create();

$processManager = new \AVAllAC\PersistentBlockStorage\ProcessManager();
$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\MicroTimeProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\KernelProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\DeliveryKernelProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\StorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ControllersProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\RoutingProvider());
//var_dump(\Symfony\Component\Yaml\Yaml::dump($pimple['config'], 3, 2));exit;

$server = new Server([$pimple['deliveryKernel'], 'handle']);
$adminServer = new Server([$pimple['kernel'], 'handle']);

$loop->addPeriodicTimer(1, function () {
    pcntl_signal_dispatch();
});

pcntl_signal(SIGHUP, function () use ($processManager) {
    if ($processManager->isMaster()) {
        $processManager->signalToChildren(SIGHUP);
        $processManager->waitChildren();
    }
});

$socket = new React\Socket\Server('0.0.0.0:8080', $loop);
$server->listen($socket);
print 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
$processManager->fork(2);
if ($processManager->isMaster()) {
    $socketAdmin = new React\Socket\Server('0.0.0.0:8081', $loop);
    $adminServer->listen($socketAdmin);
}
$loop->run();
