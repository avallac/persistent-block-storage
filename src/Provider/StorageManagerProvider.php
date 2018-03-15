<?php

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\StorageManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class StorageManagerProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['storageManager'] = function () use ($pimple) {
            $volumes = $pimple['config']['server']['volumes'];
            return new StorageManager($volumes);
        };
    }
}
