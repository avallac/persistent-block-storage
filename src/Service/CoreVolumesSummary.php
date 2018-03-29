<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use function Clue\React\Block\awaitAll;
use React\EventLoop\LoopInterface;
use RingCentral\Psr7\Response;

class CoreVolumesSummary
{
    private $serverAPI;
    private $storageManager;
    private $loop;

    /**
     * CoreVolumesSummary constructor.
     * @param ClientForServerAPI $serverAPI
     * @param CoreStorageManager $storageManager
     * @param LoopInterface $loop
     */
    public function __construct(ClientForServerAPI $serverAPI, CoreStorageManager $storageManager, LoopInterface $loop)
    {
        $this->serverAPI = $serverAPI;
        $this->storageManager = $storageManager;
        $this->loop = $loop;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function compile() : array
    {
        $volumes = [];
        foreach ($this->storageManager->getVolumes() as $id => $v) {
            $volumes[$id] = [
                'id' => $id,
                'routeTo' =>  str_replace('http://', '', $v['server']),
                'announced' => []
            ];
        }
        $response = $this->serverAPI->volumes();
        $result = awaitAll($response, $this->loop);
        /** @var Response $answer */
        foreach ($result as $deliveryUrl => $answer) {
            $serverInfo = json_decode($answer->getBody()->getContents(), true);
            foreach ($serverInfo['volumes'] as $id => $v) {
                $volumes[$id]['announced'][] = [
                    'server' => str_replace('http://', '', $deliveryUrl),
                    'dev' => $v['path']
                ];
            }
        }
        return $volumes;
    }
}
