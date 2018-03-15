<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\Kernel;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class KernelProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['kernel'] = function () use ($pimple) {
            $auth = $pimple['config']['auth'];
            return new Kernel($pimple['router'], $auth['username'], $auth['password']);
        };
    }
}
