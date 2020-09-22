<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Service\Logger;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use GuzzleHttp\Client;

class ServerStorageManagerProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['ServerStorageManager'] = function () use ($pimple) {
            if (isset($pimple['config']['server']['volumes'])) {
                $volumes = $pimple['config']['server']['volumes'];

                $coreUrlGenerator = $pimple['CoreUrlGenerator'];
                $url = $coreUrlGenerator->generate('config');
                $client = new Client();
                $request = $client->request('GET', $url);
                $data = json_decode($request->getBody()->getContents(), true);
                if (empty($data)) {
                    throw new IncorrectVolumeException();
                }

                return new ServerStorageManager($volumes, $data, $pimple[Logger::class]);
            } else {
                return new ServerStorageManager([], [], $pimple[Logger::class]);
            }
        };
    }
}
