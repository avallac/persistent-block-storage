<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\ClientForServerAPI;
use Clue\React\Buzz\Browser;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use React\HttpClient\Client;

class ClientForServerAPIProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['serverAPI'] = function () use ($pimple) {
            $httpClient = new Browser($pimple['loop']);
            return new ClientForServerAPI($httpClient, $pimple['serverUrlGenerator'], $pimple['coreStorageManager']);
        };
    }
}
