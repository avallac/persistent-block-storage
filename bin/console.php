#!/usr/bin/env php
<?php

require_once __DIR__.'/../vendor/autoload.php';

$pimple = new Pimple\Container();
$pimple['config'] = \Symfony\Component\Yaml\Yaml::parseFile(__DIR__ . '/../etc/config.yml');
$pimple->register(new \AVAllAC\PersistentBlockStorage\Provider\StorageManagerProvider());

$application = new \Symfony\Component\Console\Application();
$application->add(new \AVAllAC\PersistentBlockStorage\Command\CheckVolumeCommand(null, $pimple['storageManager']));
$application->run();
