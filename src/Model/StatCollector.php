<?php

namespace AVAllAC\PersistentBlockStorage\Model;

class StatCollector
{
    protected $stat = [];

    public function add(int $code) {
        if (isset($this->stat[$code])) {
            $this->stat[$code]++;
        } else {
            $this->stat[$code] = 1;
        }
    }

    public function export()
    {
        return $this->stat;
    }
}