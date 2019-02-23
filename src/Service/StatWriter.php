<?php

namespace AVAllAC\PersistentBlockStorage\Service;

use React\Stream\WritableResourceStream;
use React\EventLoop\LoopInterface;

class StatWriter
{
    /** @var WritableResourceStream */
    protected $stream;
    protected $map = [
        200 => 1,
        400 => 2,
        403 => 3,
        500 => 4,
        503 => 5,
    ];

    public function __construct($stream, LoopInterface $loop)
    {
        $this->stream = new WritableResourceStream($stream, $loop);
    }

    public function addAction(int $code)
    {
        $action = $this->map[$code] ?? 255;
        $this->stream->write(chr($action));
    }
}