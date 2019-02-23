<?php

namespace AVAllAC\PersistentBlockStorage\Service;

use \AVAllAC\PersistentBlockStorage\Model\StatCollector;
use \React\EventLoop\LoopInterface;

class StatReader
{
    /** @var \React\Stream\ReadableResourceStream */
    protected $stream;

    protected $map = [
        1 => 200,
        2 => 400,
        3 => 403,
        4 => 500,
        5 => 503,
    ];

    public function __construct($reader, LoopInterface $loop, StatCollector $statCollector)
    {
        $this->stream = new \React\Stream\ReadableResourceStream($reader, $loop);
        $this->stream->on('data', function ($data) use ($statCollector) {
            $statCollector->add($this->map[ord($data)] ?? 0);
        });
    }

    public function stop()
    {
        $this->stream->close();
    }
}