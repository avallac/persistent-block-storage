<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\DeliveryKernel;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DeliveryKernelProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['deliveryKernel'] = function () use ($pimple) {
            return new DeliveryKernel($pimple['serverStorageManager']);
        };
    }
}
