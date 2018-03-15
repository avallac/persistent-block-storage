<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

class MicroTime
{
    private $initTime;

    public function __construct()
    {
        $this->initTime = microtime(true);
    }

    public function getInitTime() : float
    {
        return $this->initTime;
    }

    public function get() : float
    {
        return microtime(true);
    }
}
