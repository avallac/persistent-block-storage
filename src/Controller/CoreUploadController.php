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
        $code = rand(1000, 9999);
        if ($request->getMethod() !== 'PUT') {
            return $this->textResponse(405, 'Method Not Allowed');
        }
        $data = $request->getBody()->getContents();
        print microtime(true) . ':' . $code . " INIT\n";
        while ($this->runningPromise) {
            await($this->runningPromise, $this->loop);
        }
        print microtime(true) . ':' . $code . " START\n";
        $promise = new Promise(function ($resolve, $reject) use ($data, $code) {
            $md5 = md5($data);
            try {
                $this->headerStorage->beginTransaction();
                if (!$this->headerStorage->checkValid($md5)) {
                    $newRecord = false;
                    $storagePosition = $this->headerStorage->search($md5);
                    if (!$storagePosition) {
                        $storagePosition = $this->headerStorage->insert($md5, strlen($data));
                        $newRecord = true;
                    }
                    print microtime(true) . ':' . $code . " REQUEST\n";
                    $request = $this->serverAPI->upload($storagePosition, $data);
                    $request->then(function () use ($resolve, $code, $md5, $newRecord) {
                        if (!$newRecord) {
                            $this->headerStorage->markOk($md5);
                        }
                        $this->headerStorage->commit();
                        print microtime(true) . ':' . $code . " REQUEST END\n";
                        $resolve(new Response(200, [], 'OK'));
                    }, function () use ($resolve, $code) {
                        $this->headerStorage->rollBack();
                        print microtime(true) . ':' . $code . " REQUEST ERROR\n";
                        $resolve(new Response(503, [], 'Error'));
                    });
                } else {
                    $this->headerStorage->commit();
                    $resolve(new Response(200, [], 'OK'));
                }
            } catch (\Exception $e) {
                $this->headerStorage->rollBack();
                var_dump($e->getMessage());
                $resolve(new Response(503, [], 'Error'));
            }
        });
        $this->runningPromise = $promise;
        $promise->then(function () {
            $this->runningPromise = null;
        });
        print microtime(true) . ':' . $code . " END\n";
        return $promise;
    }
}
