<?php

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use RingCentral\Psr7\Request;
use React\Http\Response;

class CoreReportController extends BaseController
{
    private $headerStorage;

    public function __construct(
        HeaderStorage $headerStorage
    ) {
        $this->headerStorage = $headerStorage;
    }

    /**
     * @param Request $request
     * @param string $hash
     * @return Response
     */
    public function report(Request $request, string $hash) : Response
    {
        if ($request->getMethod() !== 'POST') {
            return $this->textResponse(405, 'Method Not Allowed');
        }
        $this->headerStorage->markBroken($hash);
        return $this->textResponse(200, 'OK');
    }
}
