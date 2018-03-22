#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use \AVAllAC\PersistentBlockStorage\Command\DeepCheckVolumeCommand;
use \AVAllAC\PersistentBlockStorage\Command\BlockCheckVolumeCommand;

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple['fileController'] = null;
$pimple['volumeController'] = null;
$pimple['statusController'] = null;
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerStorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreRoutingProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreUrlGeneratorProvider());

$application = new \Symfony\Component\Console\Application();
$application->add(new BlockCheckVolumeCommand(null, $pimple['serverStorageManager']));
$application->add(new DeepCheckVolumeCommand(null, $pimple['serverStorageManager'], $pimple['urlGenerator']));
$application->run();
