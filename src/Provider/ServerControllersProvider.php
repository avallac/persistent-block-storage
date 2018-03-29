<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Controller\ServerStatusController;
use AVAllAC\PersistentBlockStorage\Controller\ServerUploadController;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServerControllersProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['statusController'] = function () use ($pimple) {
            return new ServerStatusController($pimple['microTime'], $pimple['serverStorageManager']);
        };

        $pimple['serverUploadController'] = function () use ($pimple) {
            return new ServerUploadController($pimple['serverStorageManager']);
        };
    }
}