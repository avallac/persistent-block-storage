<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\Kernel;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class KernelProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['kernel'] = function () use ($pimple) {
            $username = $pimple['config']['auth']['username'] ?? null;
            $password = $pimple['config']['auth']['password'] ?? null;
            return new Kernel($pimple['router'], $username, $password);
        };
    }
}
