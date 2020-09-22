<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\ClientForServerAPI;
use AVAllAC\PersistentBlockStorage\Service\CoreStorageManager;
use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use AVAllAC\PersistentBlockStorage\Model\AverageTimeCollector;
use function Clue\React\Block\await;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use RingCentral\Psr7\Request;
use Exception;

class CoreDeliveryController extends BaseController
{
    private $headerStorage;
    private $storageManager;
    private $loop;
    private $serverAPI;
    private $averageTimeCollector;

    public function __construct(
        ClientForServerAPI $serverAPI,
        HeaderStorage $headerStorage,
        LoopInterface $loop,
        CoreStorageManager $storageManager,
        AverageTimeCollector $averageTimeCollector
    ) {
        $this->serverAPI = $serverAPI;
        $this->headerStorage = $headerStorage;
        $this->storageManager = $storageManager;
        $this->loop = $loop;
        $this->averageTimeCollector = $averageTimeCollector;
    }

    /**
     * @param Request $request
     * @param string $hash
     * @param string $type
     * @return Response
     * @throws Exception
     */
    public function getOriginal(Request $request, string $hash, string $type) : Response
    {
        if ($storagePosition = $this->headerStorage->search($hash)) {
            $start = microtime(true);
            $promise = $this->serverAPI->getFile($hash, $storagePosition);
            $response = await($promise, $this->loop);
            $storagePosition->getVolume();
            $this->averageTimeCollector->addValue($storagePosition, microtime(true) - $start);
            return $this->jpegResponse(200, $response->getBody()->getContents());
        } else {
            return $this->textResponse(404, 'Not Found');
        }
    }
}
