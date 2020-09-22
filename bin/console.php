#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use \AVAllAC\PersistentBlockStorage\Command\DeepCheckVolumeCommand;
use \AVAllAC\PersistentBlockStorage\Command\ReadVolumeInformationCommand;

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\LoggerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerStorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreRoutingProvider());

$application = new \Symfony\Component\Console\Application();
$application->add(new DeepCheckVolumeCommand(null, $pimple['ServerStorageManager'], $pimple['CoreUrlGenerator']));
$application->add(new ReadVolumeInformationCommand());
$application->run();
