<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;

class ServerDeliveryKernel
{
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
     * @param ServerRequestInterface $request
     * @return Response
     * @throws \AVAllAC\PersistentBlockStorage\Exception\CantOpenFileException
     * @throws \AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException
     * @throws \AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException
     * @throws \AVAllAC\PersistentBlockStorage\Exception\VolumeReadException
     */
    public function handle(ServerRequestInterface $request)
    {
        $volume = $request->getQueryParams()['volume'] ?? 0;
        $seek = $request->getQueryParams()['seek'] ?? 0;
        $size = $request->getQueryParams()['size'] ?? 0;
        $position = new StoragePosition((int)$volume, (int)$seek, (int)$size);
        $body = $this->storageManager->readBlock($position);
        return new Response(200, ['Content-Type' => 'text/plain'], $body);
    }
}