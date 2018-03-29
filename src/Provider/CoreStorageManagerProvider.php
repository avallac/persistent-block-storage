<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\CoreStorageManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreStorageManagerProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['coreStorageManager'] = function () use ($pimple) {
            $servers = $pimple['config']['manager']['servers'];
            return new CoreStorageManager($servers);
        };
    }
}
