<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\MicroTime;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class MicroTimeProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['MicroTime'] = new MicroTime();
    }
}