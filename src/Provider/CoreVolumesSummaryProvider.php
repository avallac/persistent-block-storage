<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\CoreVolumesSummary;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreVolumesSummaryProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['CoreVolumesSummary'] = function () use ($pimple) {
            return new CoreVolumesSummary($pimple['ServerAPI'], $pimple['CoreStorageManager'], $pimple['Loop']);
        };
    }
}
