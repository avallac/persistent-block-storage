<?php

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use RingCentral\Psr7\Request;

class VolumeController extends BaseController
{
    private $headerStorage;

    public function __construct(HeaderStorage $headerStorage)
    {
        $this->headerStorage = $headerStorage;
    }

    public function serialize(Request $request, string $volume)
    {
        return $this->jsonResponse(200, $this->headerStorage->export((int)$volume));
    }
}
