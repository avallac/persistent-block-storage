<?php

namespace AVAllAC\PersistentBlockStorage\Model;

class VolumeInfo
{
    const HEADER_SIZE = 64;
    protected $id;
    protected $server;
    protected $uid;
    protected $size;

    public function __construct(int $id, ServerInfo $server, string $uid, int $size)
    {
        $this->id = $id;
        $this->server = $server;
        $this->uid = $uid;
        $this->size = $size;
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

    public function getUid()
    {
        return $this->uid;
    }

    public function getSize()
    {
        return $this->size;
    }
}