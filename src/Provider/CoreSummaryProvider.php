<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\CoreVolumesSummary;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreSummaryProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['coreSummary'] = function () use ($pimple) {
            return new CoreVolumesSummary($pimple['serverAPI'], $pimple['coreStorageManager'], $pimple['loop']);
        };
    }
}
