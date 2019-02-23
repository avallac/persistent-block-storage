<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\ClientForServerAPI;
use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use function Clue\React\Block\await;
use React\Http\Response;
use RingCentral\Psr7\Request;
use React\EventLoop\LoopInterface;

class CoreUploadController extends BaseController
{
    private $serverAPI;
    private $headerStorage;
    private $loop;

    /**
     * CoreUploadController constructor.
     * @param ClientForServerAPI $serverAPI
     * @param HeaderStorage $headerStorage
     * @param $loop
     */
    public function __construct(ClientForServerAPI $serverAPI, HeaderStorage $headerStorage, LoopInterface $loop)
    {
        $this->serverAPI = $serverAPI;
        $this->headerStorage = $headerStorage;
        $this->loop = $loop;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function upload(Request $request) : Response
    {
        if ($request->getMethod() !== 'PUT') {
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
                    $storagePosition = $this->headerStorage->insert($md5, strlen($data));
                    $newRecord = true;
                }
                $promise = $this->serverAPI->upload($storagePosition, $data);
                $response = await($promise, $this->loop);
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
                $this->headerStorage->commit();
                return $this->textResponse(200, 'OK');
            }
        } catch (\Exception $e) {
            $this->headerStorage->rollBack();
            return $this->textResponse(503, 'Error');
        }
    }
}
