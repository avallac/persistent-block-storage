<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\MicroTime;
use AVAllAC\PersistentBlockStorage\Service\StorageManager;

class StatusController
{
    private $microTime;
    private $storageManager;

    public function __construct(MicroTime $microTime, StorageManager $storageManager)
    {
        $this->microTime = $microTime;
        $this->storageManager = $storageManager;
    }

    public function status() : string
    {
        return json_encode([
            'uptime' => $this->microTime->get() - $this->microTime->getInitTime(),
            'volumes' => $this->storageManager->export()
        ], JSON_FORCE_OBJECT);
    }
}
