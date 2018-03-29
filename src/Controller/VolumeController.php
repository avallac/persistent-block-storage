<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Controller;

use AVAllAC\PersistentBlockStorage\Service\HeaderStorage;
use RingCentral\Psr7\Request;
use React\Http\Response;

class VolumeController extends BaseController
{
    private $headerStorage;

    /**
     * VolumeController constructor.
     * @param HeaderStorage $headerStorage
     */
    public function __construct(HeaderStorage $headerStorage)
    {
        $this->headerStorage = $headerStorage;
    }

    /**
     * @param Request $request
     * @param string $volume
     * @return Response
     */
    public function serialize(Request $request, string $volume) : Response
    {
        return $this->binResponse(200, $this->headerStorage->export((int)$volume));
    }
}
