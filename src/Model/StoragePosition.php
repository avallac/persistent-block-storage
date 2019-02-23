<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Model;

use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException;

class StoragePosition
{
    /** @var int */
    private $volume;
    /** @var int */
    private $seek;
    /** @var int */
    private $size;

    /**
     * StoragePosition constructor.
     * @param int $volume
     * @param int $seek
     * @param int $size
     * @throws IncorrectVolumePositionException
     */
    public function __construct(int $volume, int $seek, int $size)
    {
        if ($volume >= 0) {
            $this->volume = $volume;
        } else {
            throw new IncorrectVolumePositionException();
        }
        if ($seek >= 0) {
            $this->seek = $seek;
        } else {
            throw new IncorrectVolumePositionException();
        }
        if ($size > 0) {
            $this->size = $size;
        } else {
            throw new IncorrectVolumePositionException();
        }
    }

    /**
     * @return int
     */
    public function getSeek() : int
    {
        return $this->seek;
    }

    /**
     * @return int
     */
    public function getVolume() :int
    {
        return $this->volume;
    }

    /**
     * @return int
     */
    public function getSize() :int
    {
        return $this->size;
    }
}
