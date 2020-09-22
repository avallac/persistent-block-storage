<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use AVAllAC\PersistentBlockStorage\Service\Logger;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException;
use AVAllAC\PersistentBlockStorage\Exception\VolumeWriteException;
use React\Http\Response;
use RingCentral\Psr7\Request;

class ServerUploadController extends BaseController
{
    public const UPLOAD = 'upload';

    protected $storageManager;
    protected $logger;

    /**
     * ServerUploadController constructor.
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
     * @throws VolumeWriteException
     */
    public function upload(Request $request, string $md5, string $volume, string $seek, string $size) : Response
    {
        $volume = (int)$volume;
        $seek = (int)$seek;
        $size = (int)$size;
        $data = $request->getBody()->getContents();
        if ($request->getMethod() !== 'PUT') {
            $this->logger->debug(
                'ServerUploadController',
                'incorrect method '. $request->getMethod(),
                ['path' => $request->getUri()->getPath()]
            );
            return $this->textResponse(405, 'Method Not Allowed');
        }
        if (!$this->storageManager->volumeAvailable($volume)) {
            $this->logger->debug(
                'ServerUploadController',
                'volume is not available #'. $volume
            );
            return $this->textResponse(400, 'volume unavailable');
        }
        if (($size === (strlen($data) + ServerStorageManager::FILE_HEAD_SIZE)) && ($md5 === md5($data))) {
            $position = new StoragePosition($volume, $seek, $size);
            $this->storageManager->write($position, $data, $md5);
            $this->logger->debug(
                'ServerUploadController',
                'write to volume #'. $volume . ' successful',
                [
                    'size' => $size,
                    'md5' => $md5,
                    'seek' => $seek
                ]
            );
            return $this->textResponse(200, 'OK', $md5);
        }
        return $this->textResponse(400, 'Data corruption');
    }
}
