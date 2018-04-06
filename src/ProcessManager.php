<?php

namespace AVAllAC\PersistentBlockStorage;

class ProcessManager
{
    private $children;
    private $master;

    public function __construct()
    {
        pcntl_signal(SIGHUP, function () {
            if ($this->isMaster()) {
                $this->signalToChildren(SIGHUP);
                $this->waitChildren();
            }
        });
    }

    public function fork(int $needChildProcess)
    {
        $this->master = true;
        for ($num = 0; $num < $needChildProcess; $num++) {
            $pid = pcntl_fork();
            if ($pid == -1) {
                die('Не удалось породить дочерний процесс');
            } elseif ($pid) {
                $this->children[$num] = $pid;
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
