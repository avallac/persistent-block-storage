<?php

namespace AVAllAC\PersistentBlockStorage\Model;

class VolumeServerInfo
{
    protected $id;
    protected $uid;
    protected $size;
    protected $path;
    protected $resource;

    public function __construct(int $id, string $uid, int $size, string $path)
    {
        $this->id = $id;
        $this->uid = $uid;
        $this->size = $size;
        $this->path = $path;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getResource()
    {
        return $this->resource;
    }

    public function setResource($resource) : self
    {
        $this->resource = $resource;
        return $this;
    }
}