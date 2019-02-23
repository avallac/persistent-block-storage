<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\ClientForServerAPI;
use AVAllAC\PersistentBlockStorage\Service\CoreStorageManager;
use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use AVAllAC\PersistentBlockStorage\Service\Imagick;
use function Clue\React\Block\await;
use React\EventLoop\LoopInterface;
use React\Http\Response;
use RingCentral\Psr7\Request;

class CoreDeliveryController extends BaseController
{
    private $headerStorage;
    private $storageManager;
    private $loop;
    private $imagick;
    private $serverAPI;

    public function __construct(
        ClientForServerAPI $serverAPI,
        HeaderStorage $headerStorage,
        LoopInterface $loop,
        CoreStorageManager $storageManager,
        Imagick $imagick
    ) {
        $this->serverAPI = $serverAPI;
        $this->headerStorage = $headerStorage;
        $this->storageManager = $storageManager;
        $this->loop = $loop;
        $this->imagick = $imagick;
    }

    /**
     * @param Request $request
     * @param string $hash
     * @param string $type
     * @return Response
     * @throws \Exception
     */
    public function getOriginal(Request $request, string $hash, string $type) : Response
    {
        if ($storagePosition = $this->headerStorage->search($hash)) {
            $promise = $this->serverAPI->getFile($hash, $storagePosition);
            $response = await($promise, $this->loop);
            return $this->jpegResponse(200, $response->getBody()->getContents());
        } else {
            return $this->textResponse(404, 'Not Found');
        }
    }

    /**
     * @param Request $request
     * @param string $format
     * @param string $hash
     * @param string $type
     * @return Response
     * @throws \Exception
     */
    public function getResized(Request $request, string $format, string $hash, string $type) : Response
    {
        if ($storagePosition = $this->headerStorage->search($hash)) {
            $promise = $this->serverAPI->getFile($hash, $storagePosition);
            $response = await($promise, $this->loop);
            $data = $response->getBody()->getContents();
            return $this->jpegResponse(200, $this->imagick->thumb($format, $data));
        } else {
            return $this->textResponse(404, 'Not Found');
        }
    }
}
