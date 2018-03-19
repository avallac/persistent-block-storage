<?php

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\CoreStorageManager;
use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use AVAllAC\PersistentBlockStorage\Service\Imagick;
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
    private $imagick;

    public function __construct(
        HeaderStorage $headerStorage,
        LoopInterface $loop,
        CoreStorageManager $storageManager,
        Imagick $imagick
    ) {
        $this->headerStorage = $headerStorage;
        $this->storageManager = $storageManager;
        $this->loop = $loop;
        $this->imagick = $imagick;
    }

    private function promiseGet(string $hash, ?callable $filter = null)
    {
        return new Promise(function ($resolve, $reject) use ($hash, $filter) {
            list($volume, $seek, $size) = $this->headerStorage->search($hash);
            $url = $this->storageManager->getUrl($volume, $seek, $size);
            $client = new Client($this->loop);
            $request = $client->request('GET', $url);
            $request->on('response', function (Response $response) use ($resolve, $filter) {
                $output = '';
                $response->on('data', function ($chunk) use (&$output) {
                    $output .= $chunk;
                });
                $response->on('end', function () use ($resolve, &$output, $filter) {
                    if ($filter) {
                        $output = $filter($output);
                    }
                    $resolve($this->jpegResponse(200, $output));
                });
            });
            $request->on('error', function () use ($resolve) {
                $resolve($this->textResponse(400, 'error'));
            });
            $request->end();
        });
    }

    public function getOriginal(Request $request, string $hash, string $type)
    {
        return $this->promiseGet($hash);
    }

    public function getResized(Request $request, string $format, string $hash, string $type)
    {
        $filter = function ($input) use ($format) {
            $this->imagick->thumb($format, $input);
        };
        return $this->promiseGet($hash, $filter);
    }
}
