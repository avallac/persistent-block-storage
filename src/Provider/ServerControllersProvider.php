<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Controller\ServerStatusController;
use AVAllAC\PersistentBlockStorage\Controller\ServerUploadController;
use AVAllAC\PersistentBlockStorage\Controller\ServerDeliveryController;
use AVAllAC\PersistentBlockStorage\Service\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServerControllersProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['ServerStatusController'] = function () use ($pimple) {
            return new ServerStatusController(
                $pimple['MicroTime'],
                $pimple['ServerStorageManager'],
                $pimple['StatCollector']
            );
        };

        $pimple['ServerUploadController'] = function () use ($pimple) {
            return new ServerUploadController($pimple['ServerStorageManager'], $pimple[Logger::class]);
        };

        $pimple['ServerDeliveryController'] = function () use ($pimple) {
            return new ServerDeliveryController($pimple['ServerStorageManager'], $pimple[Logger::class]);
        };
    }
}