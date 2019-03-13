<?php

namespace AVAllAC\PersistentBlockStorage\Model;

class AverageTimeCollector
{
    protected $sum = 0;
    protected $count = 0;

    public function addValue($time)
    {
        $this->sum += $time;
        $this->count++;
    }

    public function getAverageValue()
    {
        return $this->sum/$this->count;
    }
}