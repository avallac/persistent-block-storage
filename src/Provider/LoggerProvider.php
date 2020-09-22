<?php

namespace AVAllAC\PersistentBlockStorage\Provider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;
use AVAllAC\PersistentBlockStorage\Service\Logger;

class LoggerProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple[Logger::class] = new Logger($pimple['config']['logLevel'] ?? Logger::WARN);
    }
}