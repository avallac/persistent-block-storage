<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use React\Http\Response;
use RingCentral\Psr7\Request;

class ServerUploadController extends BaseController
{
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
     * @param string $volume
     * @param string $seek
     * @param string $size
     * @return Response
     * @throws \AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException
     * @throws \AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException
     * @throws \AVAllAC\PersistentBlockStorage\Exception\VolumeWriteException
     */
    public function upload(Request $request, string $volume, string $seek, string $size) : Response
    {
        $code = rand(1000, 9999);
        $volume = (int)$volume;
        $seek = (int)$seek;
        $size = (int)$size;
        print microtime(true) . ':' . $code . " INIT $volume/$seek/$size \n";
        $data = $request->getBody()->getContents();
        if ($request->getMethod() !== 'PUT') {
            return $this->textResponse(405, 'Method Not Allowed');
        }
        if (!$this->storageManager->volumeAvailable($volume)) {
            return $this->textResponse(400, 'Bad Request');
        }
        if ($size === strlen($data)) {
            print microtime(true) . ':' . $code . " POS\n";
            $position = new StoragePosition($volume, $seek, strlen($data));
            print microtime(true) . ':' . $code . " WRITE\n";
            $this->storageManager->write($position, $data);
            print microtime(true) . ':' . $code . " RESPONSE\n";
            return $this->textResponse(200, 'OK');
        }
        return $this->textResponse(400, 'Bad Request');
    }
}
