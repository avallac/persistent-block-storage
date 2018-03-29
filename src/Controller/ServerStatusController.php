<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\MicroTime;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use React\Http\Response;

class ServerStatusController extends BaseController
{
    private $microTime;
    private $storageManager;

    /**
     * ServerStatusController constructor.
     * @param MicroTime $microTime
     * @param ServerStorageManager $storageManager
     */
    public function __construct(MicroTime $microTime, ServerStorageManager $storageManager)
    {
        $this->microTime = $microTime;
        $this->storageManager = $storageManager;
    }

    /**
     * @return Response
     */
    public function status() : Response
    {
        return $this->jsonResponse(200, [
            'uptime' => $this->microTime->get() - $this->microTime->getInitTime(),
            'volumes' => $this->storageManager->export()
        ]);
    }
}
