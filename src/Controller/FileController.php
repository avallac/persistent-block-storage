<?php

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\CoreStorageManager;
use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use React\EventLoop\LoopInterface;
use React\HttpClient\Client;
use React\HttpClient\Response;
use React\Promise\Promise;
use RingCentral\Psr7\Request;

class FileController extends BaseController
{
    private $headerStorage;
    private $storageManager;
    private $loop;

    public function __construct(HeaderStorage $headerStorage, LoopInterface $loop, CoreStorageManager $storageManager)
    {
        $this->headerStorage = $headerStorage;
        $this->storageManager = $storageManager;
        $this->loop = $loop;
    }

    public function get(Request $request, string $hash, string $type)
    {
        return new Promise(function ($resolve, $reject) use ($hash) {
            list($volume, $seek, $size) = $this->headerStorage->search($hash);
            $url = $this->storageManager->getUrl($volume, $seek, $size);
            $client = new Client($this->loop);
            $request = $client->request('GET', $url);
            $request->on('response', function (Response $response) use ($resolve) {
                $output = '';
                $response->on('data', function ($chunk) use (&$output) {
                    $output .= $chunk;
                });
                $response->on('end', function () use ($resolve, &$output) {
                    $resolve($this->jpegResponse(200, $output));
                });
            });
            $request->on('error', function () use ($resolve) {
                $resolve($this->textResponse(400, 'error'));
            });
            $request->end();
        });
    }
}
