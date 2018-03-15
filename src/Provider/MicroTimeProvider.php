<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\MicroTime;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MicroTimeProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['microTime'] = new MicroTime();
    }
}