<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Controller\ServerStatusController;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServerControllersProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['statusController'] = function () use ($pimple) {
            return new ServerStatusController($pimple['microTime'], $pimple['serverStorageManager']);
        };
    }
}