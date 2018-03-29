<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

class MicroTime
{
    private $initTime;

    public function __construct()
    {
        $this->initTime = microtime(true);
    }

    /**
     * @return float
     */
    public function getInitTime() : float
    {
        return $this->initTime;
    }

    /**
     * @return float
     */
    public function get() : float
    {
        return microtime(true);
    }
}
