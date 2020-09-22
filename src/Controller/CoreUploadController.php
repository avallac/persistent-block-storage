<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\ClientForServerAPI;
use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use AVAllAC\PersistentBlockStorage\Service\Logger;
use AVAllAC\PersistentBlockStorage\Service\ServerStorageManager;
use function Clue\React\Block\await;
use React\Http\Response;
use RingCentral\Psr7\Request;
use React\EventLoop\LoopInterface;
use Exception;

class CoreUploadController extends BaseController
{
    protected $serverAPI;
    protected $headerStorage;
    protected $loop;
    protected $logger;

    /**
     * CoreUploadController constructor.
     * @param ClientForServerAPI $serverAPI
     * @param HeaderStorage $headerStorage
     * @param LoopInterface $loop
     * @param Logger $logger
     */
    public function __construct(
        ClientForServerAPI $serverAPI,
        HeaderStorage $headerStorage,
        LoopInterface $loop,
        Logger $logger
    ) {
        $this->serverAPI = $serverAPI;
        $this->headerStorage = $headerStorage;
        $this->loop = $loop;
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function upload(Request $request) : Response
    {
        if ($request->getMethod() !== 'PUT') {
            $this->logger->debug(
                'CoreUploadController',
                'incorrect method '. $request->getMethod(),
                ['path' => $request->getUri()->getPath()]
            );
            return $this->textResponse(405, 'Method Not Allowed');
        }
        $data = $request->getBody()->getContents();
        $md5 = md5($data);
        try {
            $this->headerStorage->beginTransaction();
            if (!$this->headerStorage->checkExistsValid($md5)) {
                $newRecord = false;
                $storagePosition = $this->headerStorage->search($md5);
                if (!$storagePosition) {
                    $storagePosition = $this->headerStorage->insert($md5, strlen($data) + ServerStorageManager::FILE_HEAD_SIZE);
                    $newRecord = true;
                }
                $promise = $this->serverAPI->upload($storagePosition, $data);
                $response = await($promise, $this->loop);
                $this->logger->debug(
                    'CoreUploadController',
                    'upload data',
                    [
                        'md5' => $md5,
                        'size' => $storagePosition->getSize(),
                        'seek' => $storagePosition->getSeek(),
                        'volume' => $storagePosition->getVolume(),
                        'isNew' => $newRecord,
                        'statusCode' => $response->getStatusCode()
                    ]
                );
                if ($md5 !== $response->getHeader('md5')[0]) {
                    $this->logger->error(
                        'CoreUploadController',
                        'md5 mismatch',
                        ['md5' => $md5, 'expect' => $response->getHeader('md5')[0]]
                    );
                }
                if ($response->getStatusCode() === 200) {
                    if (!$newRecord) {
                        $this->headerStorage->markOk($md5);
                    }
                    $this->headerStorage->commit();
                    return $this->textResponse(200, 'OK');
                } else {
                    return $this->textResponse(503, 'Error');
                }
            } else {
                $this->logger->debug(
                    'CoreUploadController',
                    'exists hash',
                    ['md5' => $md5]
                );
                $this->headerStorage->commit();
                return $this->textResponse(200, 'OK');
            }
        } catch (Exception $e) {
            $this->headerStorage->rollBack();
            return $this->textResponse(503, $e->getMessage() . ' ' . get_class($e));
        }
    }
}
