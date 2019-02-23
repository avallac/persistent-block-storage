<?php

require_once __DIR__.'/../vendor/autoload.php';

$processManager = new \AVAllAC\PersistentBlockStorage\ProcessManager();
$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple['Loop'] = React\EventLoop\Factory::create();
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\MicroTimeProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerDeliveryKernelProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerStorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerControllersProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerDeliveryRoutingProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerAdminRoutingProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\StatProvider());

$username = $pimple['config']['auth']['username'] ?? null;
$password = $pimple['config']['auth']['password'] ?? null;

$adminKernel = new AVAllAC\PersistentBlockStorage\Service\Kernel(
    $pimple['ServerAdminRouter'],
    $pimple['StatWriter'],
    $username,
    $password
);
$deliveryKernel = new AVAllAC\PersistentBlockStorage\Service\Kernel(
    $pimple['ServerDeliveryRouter'],
    $pimple['StatWriter'],
    $username,
    $password
);

$adminServer = new \React\Http\Server([$adminKernel, 'handle']);
$deliveryServer = new \React\Http\Server([$deliveryKernel, 'handle']);

$pimple['Loop']->addPeriodicTimer(1, function () {
    pcntl_signal_dispatch();
});

$deliverySocket = new React\Socket\Server($pimple['config']['server']['bind'], $pimple['Loop']);
print 'Listening delivery on ' . str_replace('tcp:', 'http:', $deliverySocket->getAddress()) . PHP_EOL;

$adminSocket = new React\Socket\Server($pimple['config']['server']['bindAdmin'], $pimple['Loop']);
print 'Listening admin on ' . str_replace('tcp:', 'http:', $adminSocket->getAddress()) . PHP_EOL;

$processManager->fork(8);
if ($processManager->isMaster()) {
    $adminServer->listen($adminSocket);
    $deliverySocket->close();
} else {
    $deliveryServer->listen($deliverySocket);
    $adminSocket->close();
    $pimple['StatReader']->stop();
}
$pimple['Loop']->run();
