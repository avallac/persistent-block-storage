<?php

namespace AVAllAC\PersistentBlockStorage\Model;

use AVAllAC\PersistentBlockStorage\Service\CoreStorageManager;

class AverageTimeCollector
{
    private $data = [];
    private $storageManager;

    public function __construct(CoreStorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    public function addValue(StoragePosition $storagePosition, $time)
    {
        $serverId = $this->storageManager->getServerId($storagePosition->getVolume());
        if (!isset($this->data[$serverId])) {
            $this->data[$serverId] = [
                'sum' => 0,
                'count' => 0
            ];
        }
        $this->data[$serverId]['sum'] += $time;
        $this->data[$serverId]['count']++;
    }

    public function getAverageValue($serverId)
    {
        if (!isset($this->data[$serverId])) {
            return -1;
        }
        return $this->data[$serverId]['sum']/$this->data[$serverId]['count'];
    }
}