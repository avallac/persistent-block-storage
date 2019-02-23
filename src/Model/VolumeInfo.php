<?php

namespace AVAllAC\PersistentBlockStorage\Model;

class VolumeInfo
{
    protected $id;
    protected $server;

    public function __construct(int $id, ServerInfo $server)
    {
        $this->id = $id;
        $this->server = $server;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ServerInfo
     */
    public function getServer(): ServerInfo
    {
        return $this->server;
    }
}