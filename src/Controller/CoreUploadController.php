<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Exception\ServerAPIException;
use AVAllAC\PersistentBlockStorage\Service\ClientForServerAPI;
use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use function Clue\React\Block\await;
use React\Http\Response;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use RingCentral\Psr7\Request;

class CoreUploadController extends BaseController
{
    private $serverAPI;
    private $headerStorage;
    private $runningPromise;
    private $loop;

    /**
     * CoreUploadController constructor.
     * @param ClientForServerAPI $serverAPI
     * @param HeaderStorage $headerStorage
     * @param $loop
     */
    public function __construct(ClientForServerAPI $serverAPI, HeaderStorage $headerStorage, $loop)
    {
        $this->serverAPI = $serverAPI;
        $this->headerStorage = $headerStorage;
        $this->loop = $loop;
        $this->runningPromise = null;
    }

    /**
     * @param Request $request
     * @return PromiseInterface
     */
    public function upload(Request $request) : PromiseInterface
    {
        if ($request->getMethod() !== 'PUT') {
            return $this->textResponse(405, 'Method Not Allowed');
        }
        $data = $request->getBody()->getContents();
        $promise =  new Promise(function ($resolve, $reject) use ($data) {
            while ($this->runningPromise) {
                await($this->runningPromise, $this->loop);
            }
            $md5 = md5($data);
            try {
                $this->headerStorage->beginTransaction();
                if (!$this->headerStorage->checkExists($md5)) {
                    $storagePosition = $this->headerStorage->insert($md5, strlen($data));
                    $promise = $this->serverAPI->upload($storagePosition, $data);
                    $this->runningPromise = $promise;
                    await($promise, $this->loop);
                }
                $resolve(new Response(200, [], 'OK'));
                $this->headerStorage->commit();
            } catch (\Exception $e) {
                $this->headerStorage->rollBack();
                throw $e;
            } finally {
                $this->runningPromise = null;
            }
        });
        return $promise;
    }
}
