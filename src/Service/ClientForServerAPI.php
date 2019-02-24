<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;
use AVAllAC\PersistentBlockStorage\Controller\ServerStatusController;
use AVAllAC\PersistentBlockStorage\Controller\ServerUploadController;
use AVAllAC\PersistentBlockStorage\Controller\ServerDeliveryController;
use Clue\React\Buzz\Browser;
use React\Promise\PromiseInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

class ClientForServerAPI
{
    private $httpClient;
    private $serverAdminUrlGenerator;
    private $serverDeliveryUrlGenerator;
    private $storageManager;

    /**
     * ClientForServerAPI constructor.
     * @param Browser $httpClient
     * @param UrlGenerator $serverAdminUrlGenerator
     * @param UrlGenerator $serverDeliveryUrlGenerator
     * @param CoreStorageManager $storageManager
     */
    public function __construct(
        Browser $httpClient,
        UrlGenerator $serverAdminUrlGenerator,
        UrlGenerator $serverDeliveryUrlGenerator,
        CoreStorageManager $storageManager
    ) {
        $this->httpClient = $httpClient;
        $this->serverAdminUrlGenerator = $serverAdminUrlGenerator;
        $this->serverDeliveryUrlGenerator = $serverDeliveryUrlGenerator;
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
        $this->serverAdminUrlGenerator->setContext($request);
        $url = $this->serverAdminUrlGenerator->generate(ServerUploadController::UPLOAD, [
            'md5' => md5($data),
            'volume' => $position->getVolume(),
            'seek' => $position->getSeek(),
            'size' => $position->getSize()
        ]);
        return $this->httpClient->put($url, [], $data);
    }

    public function getServersStats()
    {
        $promises = [];
        $servers = $this->storageManager->getServers();
        foreach ($servers as $server) {
            $request = new RequestContext($server->getAdminUrl());
            $this->serverAdminUrlGenerator->setContext($request);
            $url = $this->serverAdminUrlGenerator->generate(ServerStatusController::STATUS);
            $promises[$server->getId()] = $this->httpClient->get($url);
        }
        return $promises;
    }

    public function getFile(string $hash, StoragePosition $position)
    {
        $deliveryUrl = $this->storageManager->getServerDeliveryUrl($position->getVolume());
        $request = new RequestContext($deliveryUrl);
        $this->serverDeliveryUrlGenerator->setContext($request);
        $url = $this->serverDeliveryUrlGenerator->generate(ServerDeliveryController::GET, [
            'md5' => $hash,
            'volume' => $position->getVolume(),
            'seek' => $position->getSeek(),
            'size' => $position->getSize()
        ]);
        return $this->httpClient->get($url);
    }
}
