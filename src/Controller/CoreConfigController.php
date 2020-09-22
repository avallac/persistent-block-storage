<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\CoreStorageManager;
use React\Http\Response;
use Exception;

class CoreConfigController extends BaseController
{
    protected $storageManager;

    public function __construct(
        CoreStorageManager $storageManager
    ) {
        $this->storageManager = $storageManager;
    }

    /**
     * @return Response
     * @throws Exception
     */
    public function config() : Response
    {
        $volumes = [];
        foreach ($this->storageManager->getVolumes() as $volume) {
            $volumes[$volume->getId()] = [
                'id' => $volume->getId(),
                'uid' =>  $volume->getUid(),
                'size' =>  $volume->getSize(),
            ];
        }

        return $this->jsonResponse(200, $volumes);
    }
}