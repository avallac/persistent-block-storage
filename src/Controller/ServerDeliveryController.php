<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use React\Http\Response;
use RingCentral\Psr7\Request;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use AVAllAC\PersistentBlockStorage\Model\StoragePosition;

class ServerDeliveryController extends BaseController
{
    public const GET = '/';

    private $storageManager;

    /**
     * DeliveryKernel constructor.
     * @param ServerStorageManager $storageManager
     */
    public function __construct(ServerStorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }


    /**
     * @param Request $request
     * @param string $md5
     * @param string $volume
     * @param string $seek
     * @param string $size
     * @return Response
     * @throws \AVAllAC\PersistentBlockStorage\Exception\CantOpenFileException
     * @throws \AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException
     * @throws \AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException
     * @throws \AVAllAC\PersistentBlockStorage\Exception\VolumeReadException
     */
    public function get(Request $request, string $md5, string $volume, string $seek, string $size)
    {
        $position = new StoragePosition((int)$volume, (int)$seek, (int)$size);
        $body = $this->storageManager->read($position);
        if (md5($body) == $md5) {
            return new Response(200, ['Content-Type' => 'text/plain'], $body);
        } else {
            return new Response(503, ['Content-Type' => 'text/plain'], 'error');
        }
    }
}
