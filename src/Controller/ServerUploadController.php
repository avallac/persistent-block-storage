<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use React\Http\Response;
use RingCentral\Psr7\Request;

class ServerUploadController extends BaseController
{
    public const UPLOAD = 'upload';

    private $storageManager;

    /**
     * ServerUploadController constructor.
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
     * @throws \AVAllAC\PersistentBlockStorage\Exception\VolumeWriteException
     */
    public function upload(Request $request, string $md5, string $volume, string $seek, string $size) : Response
    {
        $volume = (int)$volume;
        $seek = (int)$seek;
        $size = (int)$size;
        $data = $request->getBody()->getContents();
        if ($request->getMethod() !== 'PUT') {
            return $this->textResponse(405, 'Method Not Allowed');
        }
        if (!$this->storageManager->volumeAvailable($volume)) {
            return $this->textResponse(400, 'volume unavailable');
        }
        if (($size === strlen($data)) && ($md5 === md5($data))) {
            $position = new StoragePosition($volume, $seek, strlen($data));
            $this->storageManager->write($position, $data);
            return $this->textResponse(200, 'OK', $md5);
        }
        return $this->textResponse(400, 'Data corruption');
    }
}
