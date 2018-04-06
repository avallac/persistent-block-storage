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
     * @throws \Exception
     */
    public function upload(Request $request) : PromiseInterface
    {
        if ($request->getMethod() !== 'PUT') {
            return $this->textResponse(405, 'Method Not Allowed');
        }
        $data = $request->getBody()->getContents();
        while ($this->runningPromise) {
            await($this->runningPromise, $this->loop);
        }
        $this->runningPromise = new Promise(function ($resolve, $reject) use ($data) {
            $md5 = md5($data);
            try {
                $this->headerStorage->beginTransaction();
                if (!$this->headerStorage->checkExists($md5)) {
                    $storagePosition = $this->headerStorage->insert($md5, strlen($data));
                    $request = $this->serverAPI->upload($storagePosition, $data);
                    $request->then(function () use ($resolve) {
                        $this->headerStorage->commit();
                        $this->runningPromise = null;
                        $resolve(new Response(200, [], 'OK'));
                    }, function () use ($resolve) {
                        $this->headerStorage->rollBack();
                        $this->runningPromise = null;
                        $resolve(new Response(503, [], 'Error'));
                    });
                } else {
                    $this->headerStorage->commit();
                    $this->runningPromise = null;
                    $resolve(new Response(200, [], 'OK'));
                }
            } catch (\Exception $e) {
                $this->headerStorage->rollBack();
                $this->runningPromise = null;
                var_dump($e->getMessage());
                $resolve(new Response(503, [], 'Error'));
            }
        });
        return $this->runningPromise;
    }
}
