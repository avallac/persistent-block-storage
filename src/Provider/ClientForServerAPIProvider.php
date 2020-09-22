<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\ClientForServerAPI;
use Clue\React\Buzz\Browser;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ClientForServerAPIProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['ServerAPI'] = function () use ($pimple) {
            $httpClient = new Browser($pimple['Loop']);
            return new ClientForServerAPI(
                $httpClient,
                $pimple['ServerAdminUrlGenerator'],
                $pimple['ServerDeliveryUrlGenerator'],
                $pimple['CoreStorageManager']
            );
        };
    }
}
