<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Controller\StatusController;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServerControllersProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['statusController'] = function () use ($pimple) {
            return new StatusController($pimple['microTime'], $pimple['serverStorageManager']);
        };
    }
}