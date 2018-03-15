<?php

namespace AVAllAC\PersistentBlockStorage\Service;

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;

class DeliveryKernel
{
    private $storageManager;

    public function __construct(StorageManager $storageManager)
    {
        $this->storageManager = $storageManager;
    }

    public function handle(ServerRequestInterface $request)
    {
        $seek = $request->getQueryParams()['seek'] ?? null;
        $size = $request->getQueryParams()['size'] ?? null;
        $volume = $request->getQueryParams()['volume'] ?? null;
        if (isset($seek) && isset($size) && isset($volume)) {
            $body = $this->storageManager->read($volume, $seek, $size);
            return new Response(200, ['Content-Type' => 'text/plain'], $body);
        } else {
            return new Response(404, ['Content-Type' => 'text/plain'], 'error');
        }
    }
}