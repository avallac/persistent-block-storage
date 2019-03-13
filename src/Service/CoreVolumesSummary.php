<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\AverageTimeCollector;
use function Clue\React\Block\awaitAll;
use React\EventLoop\LoopInterface;
use RingCentral\Psr7\Response;

class CoreVolumesSummary
{
    private $serverAPI;
    private $storageManager;
    private $loop;
    private $averageTimeCollector;

    public function __construct(
        ClientForServerAPI $serverAPI,
        CoreStorageManager $storageManager,
        LoopInterface $loop,
        AverageTimeCollector $averageTimeCollector
    ) {
        $this->serverAPI = $serverAPI;
        $this->storageManager = $storageManager;
        $this->loop = $loop;
        $this->averageTimeCollector = $averageTimeCollector;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function volumes() : array
    {
        $volumes = [];
        foreach ($this->storageManager->getVolumes() as $volume) {
            $volumes[$volume->getId()] = [
                'id' => $volume->getId(),
                'routeTo' =>  str_replace('http://', '', $volume->getServer()->getAdminUrl()),
                'announced' => []
            ];
        }
        $response = $this->serverAPI->getServersStats();
        $result = awaitAll($response, $this->loop);
        /** @var Response $answer */
        foreach ($result as $adminUrl => $answer) {
            $serverInfo = json_decode($answer->getBody()->getContents(), true);
            foreach ($serverInfo['volumes'] as $id => $v) {
                $volumes[$id]['announced'][] = [
                    'server' => str_replace('http://', '', $adminUrl),
                    'dev' => $v['path']
                ];
            }
        }
        return $volumes;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function servers() : array
    {
        $servers = [];
        foreach ($this->storageManager->getServers() as $server) {
            $servers[$server->getId()] = [
                'id' => $server->getId(),
                'url' => str_replace('http://', '', $server->getAdminUrl()),
            ];
        }
        $response = $this->serverAPI->getServersStats();
        $result = awaitAll($response, $this->loop);
        /** @var Response $answer */
        foreach ($result as $id => $answer) {
            $serverInfo = json_decode($answer->getBody()->getContents(), true);
            $servers[$id]['stats'] = $serverInfo['stats'];
            $servers[$id]['uptime'] = $serverInfo['uptime'];
            $servers[$id]['time'] = $this->averageTimeCollector->getAverageValue($id);
        }
        return $servers;
    }
}
