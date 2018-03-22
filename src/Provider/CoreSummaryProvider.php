<?php

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\CoreSummary;
use GuzzleHttp\Client;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreSummaryProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['coreSummary'] = function () use ($pimple) {
            return new CoreSummary(new Client(), $pimple['coreStorageManager']);
        };
    }
}
