<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\MicroTime;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use AVAllAC\PersistentBlockStorage\Model\StatCollector;
use React\Http\Response;

class ServerStatusController extends BaseController
{
    public const STATUS = 'status';

    /** @var MicroTime */
    private $microTime;
    /** @var ServerStorageManager */
    private $storageManager;
    /** @var StatCollector */
    protected $statCollector;

    /**
     * ServerStatusController constructor.
     * @param MicroTime $microTime
     * @param ServerStorageManager $storageManager
     * @param StatCollector $statCollector
     */
    public function __construct(
        MicroTime $microTime,
        ServerStorageManager $storageManager,
        StatCollector $statCollector
    ) {
        $this->microTime = $microTime;
        $this->storageManager = $storageManager;
        $this->statCollector = $statCollector;
    }

    /**
     * @return Response
     */
    public function status() : Response
    {
        return $this->jsonResponse(
            200,
            [
                'uptime' => $this->microTime->get() - $this->microTime->getInitTime(),
                'volumes' => $this->storageManager->export(),
                'stats' => $this->statCollector->export()
            ]
        );
    }
}
