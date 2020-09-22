<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use React\Http\Response;
use RingCentral\Psr7\Request;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use AVAllAC\PersistentBlockStorage\Model\StoragePosition;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException;
use AVAllAC\PersistentBlockStorage\Exception\VolumeReadException;
use AVAllAC\PersistentBlockStorage\Service\Logger;

class ServerDeliveryController extends BaseController
{
    public const GET = '/';

    private $storageManager;
    protected $logger;

    /**
     * DeliveryKernel constructor.
     * @param ServerStorageManager $storageManager
     * @param Logger $logger
     */
    public function __construct(
        ServerStorageManager $storageManager,
        Logger $logger
    ) {
        $this->storageManager = $storageManager;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @param string $md5
     * @param string $volume
     * @param string $seek
     * @param string $size
     * @return Response
     * @throws IncorrectVolumeException
     * @throws IncorrectVolumePositionException
     * @throws VolumeReadException
     */
    public function get(Request $request, string $md5, string $volume, string $seek, string $size)
    {
        $position = new StoragePosition((int)$volume, (int)$seek, (int)$size);
        $body = $this->storageManager->readBlock($position);
        if (md5($body) === $md5) {
            $this->logger->debug(
                'ServerDeliveryController',
                'return a successful response to the client',
                [
                    'position' => $position->toArray(),
                    'md5' => $md5,
                ]
            );
            return new Response(200, ['Content-Type' => 'text/plain'], $body);
        } else {
            $this->logger->error(
                'ServerDeliveryController',
                'md5 in response does not match expected',
                [
                    'position' => $position->toArray(),
                    'md5' => $md5,
                    'realMd5' => md5($body)
                ]
            );
            return new Response(503, ['Content-Type' => 'text/plain'], 'error');
        }
    }
}
