<?php

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use RingCentral\Psr7\Request;

class ReportController extends BaseController
{
    private $headerStorage;

    public function __construct(
        HeaderStorage $headerStorage
    ) {
        $this->headerStorage = $headerStorage;
    }

    public function report(Request $request, string $hash)
    {
        if ($request->getMethod() !== 'POST') {
            return $this->textResponse(405, 'Method Not Allowed');
        }
        $this->headerStorage->markBroken($hash);
        return $this->textResponse(200, 'OK');
    }
}
