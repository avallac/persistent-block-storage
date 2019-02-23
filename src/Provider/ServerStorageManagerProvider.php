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
        $pimple['ServerStorageManager'] = function () use ($pimple) {
            if (isset($pimple['config']['server']['volumes'])) {
                $volumes = $pimple['config']['server']['volumes'];
                return new ServerStorageManager($volumes, $pimple['config']['blockSize']);
            } else {
                return new ServerStorageManager([], $pimple['config']['blockSize']);
            }
        };
    }
}
