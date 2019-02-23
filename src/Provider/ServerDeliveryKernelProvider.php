<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\ServerDeliveryKernel;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServerDeliveryKernelProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['ServerDeliveryKernel'] = function () use ($pimple) {
            return new ServerDeliveryKernel($pimple['ServerStorageManager']);
        };
    }
}
