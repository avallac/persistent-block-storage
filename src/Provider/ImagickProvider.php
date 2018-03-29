<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\Imagick;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ImagickProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['imagick'] = function () use ($pimple) {
            return new Imagick();
        };
    }
}
