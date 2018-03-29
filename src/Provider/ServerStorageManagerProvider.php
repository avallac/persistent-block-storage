<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServerStorageManagerProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['serverStorageManager'] = function () use ($pimple) {
            $volumes = $pimple['config']['server']['volumes'];
            return new ServerStorageManager($volumes);
        };
    }
}
