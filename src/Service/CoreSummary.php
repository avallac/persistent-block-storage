<?php

namespace AVAllAC\PersistentBlockStorage\Service;

use GuzzleHttp\Client;

class CoreSummary
{
    private $httpClient;
    private $storageManager;

    public function __construct(Client $httpClient, CoreStorageManager $storageManager)
    {
        $this->httpClient = $httpClient;
        $this->storageManager = $storageManager;
    }

    public function compile()
    {
        $volumes = [];
        foreach ($this->storageManager->getVolumes() as $id => $v) {
            $volumes[$id] = [
                'id' => $id,
                'routeTo' =>  str_replace('http://', '', $v['server']),
                'announced' => []
            ];
        }
        $servers = $this->storageManager->getServers();
        foreach ($servers as $num => $server) {
            $response = $this->httpClient->get($server['adminUrl'] . '/status');
            $serverInfo = json_decode($response->getBody()->getContents(), true);
            foreach ($serverInfo['volumes'] as $id => $v) {
                $volumes[$id]['announced'][] = [
                    'server' => str_replace('http://', '', $server['deliveryUrl']),
                    'dev' => $v['path']
                ];
            }
        }
        return $volumes;
    }
}
