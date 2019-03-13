<?php

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Model\AverageTimeCollector;
use Pimple\ServiceProviderInterface;
use Pimple\Container;

class AverageTimeProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['AverageTimeCollector'] = new AverageTimeCollector();
    }
}