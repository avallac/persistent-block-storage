#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use \AVAllAC\PersistentBlockStorage\Command\CheckVolumeCommand;

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerStorageManagerProvider());

$application = new \Symfony\Component\Console\Application();
$application->add(new CheckVolumeCommand(null, $pimple['serverStorageManager']));
$application->run();
