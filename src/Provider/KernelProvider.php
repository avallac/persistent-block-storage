<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\Kernel;
use AVAllAC\PersistentBlockStorage\Service\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class KernelProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['Kernel'] = function () use ($pimple) {
            $username = $pimple['config']['auth']['username'] ?? null;
            $password = $pimple['config']['auth']['password'] ?? null;
            return new Kernel($pimple['Router'], $pimple['StatWriter'], $pimple[Logger::class], $username, $password);
        };
    }
}
