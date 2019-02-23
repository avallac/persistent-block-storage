<?php

namespace AVAllAC\PersistentBlockStorage\Provider;

use \Pimple\Container;
use \Pimple\ServiceProviderInterface;
use \AVAllAC\PersistentBlockStorage\Service\StatWriter;
use \AVAllAC\PersistentBlockStorage\Service\StatReader;
use \AVAllAC\PersistentBlockStorage\Model\StatCollector;

class StatProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
        $pimple['StatCollector'] = new StatCollector();
        $pimple['StatReader'] = new StatReader($sockets[0], $pimple['Loop'], $pimple['StatCollector']);
        $pimple['StatWriter'] = new StatWriter($sockets[1], $pimple['Loop']);
    }
}