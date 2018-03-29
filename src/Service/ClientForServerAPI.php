<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;
use Clue\React\Buzz\Browser;
use React\Promise\PromiseInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

class ClientForServerAPI
{
    private $httpClient;
    private $urlGenerator;
    private $storageManager;

    /**
     * ClientForServerAPI constructor.
     * @param Browser $httpClient
     * @param UrlGenerator $urlGenerator
     * @param CoreStorageManager $storageManager
     */
    public function __construct(Browser $httpClient, UrlGenerator $urlGenerator, CoreStorageManager $storageManager)
    {
        $this->httpClient = $httpClient;
        $this->urlGenerator = $urlGenerator;
        $this->storageManager = $storageManager;
    }

    /**
     * @param StoragePosition $position
     * @param string $data
     * @return PromiseInterface
     */
    public function upload(StoragePosition $position, string $data) : PromiseInterface
    {
        $adminUrl = $this->storageManager->getServerAdminUrl($position->getVolume());
        $request = new RequestContext($adminUrl);
        $this->urlGenerator->setContext($request);
        $url = $this->urlGenerator->generate('upload', [
           'volume' => $position->getVolume(),
           'seek' => $position->getSeek(),
           'size' => $position->getSize()
        ]);
        return $this->httpClient->put($url, [], $data);
    }

    public function volumes()
    {
        $promises = [];
        $servers = $this->storageManager->getServers();
        foreach ($servers as $num => $server) {
            $promises [$server['deliveryUrl']] = $this->httpClient->get($server['adminUrl'] . '/status');
        }
        return $promises;
    }

    public function getFile(StoragePosition $position)
    {
        $deliveryUrl = $this->storageManager->getServerDeliveryUrl($position->getVolume());
        $params = http_build_query([
            'volume' => $position->getVolume(),
            'seek' => $position->getSeek(),
            'size' => $position->getSize()
        ]);
        return $this->httpClient->get($deliveryUrl . '/?' . $params);
    }
}
