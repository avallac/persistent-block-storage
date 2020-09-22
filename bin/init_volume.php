#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

use \AVAllAC\PersistentBlockStorage\Command\InitVolumeResourceCommand;

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\LoggerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\ServerStorageManagerProvider());
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\CoreRoutingProvider());

$application = new \Symfony\Component\Console\Application();
$application->add(new InitVolumeResourceCommand(null, $pimple['CoreUrlGenerator']));
$application->run();
