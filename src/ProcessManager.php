<?php

namespace AVAllAC\PersistentBlockStorage;

class ProcessManager
{
    private $children;
    private $master;

    public function fork(int $needProcess)
    {
        foreach (range(1, $needProcess) as $num) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('Не удалось породить дочерний процесс');
            } elseif ($pid) {
                $this->children[$num] = $pid;
                $this->master = true;
            } else {
                $this->master = false;
                return;
            }
        }
    }

    public function signalToChildren($signal)
    {
        foreach ($this->children as $child) {
            posix_kill($child, $signal);
        }
    }

    public function isMaster()
    {
        return $this->master;
    }

    public function waitChildren()
    {
        foreach ($this->children as $child) {
            pcntl_waitpid($child, $status, WNOHANG);
        }
    }
}
