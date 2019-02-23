<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Exception\CantOpenFileException;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException;
use AVAllAC\PersistentBlockStorage\Exception\ResourceNotFoundException;
use AVAllAC\PersistentBlockStorage\Exception\VolumeReadException;
use AVAllAC\PersistentBlockStorage\Exception\VolumeWriteException;
use AVAllAC\PersistentBlockStorage\Model\StoragePosition;

class ServerStorageManager
{
    private $volumes;
    private $blockSize;

    public function __construct(array $volumes, int $blockSize)
    {
        $this->blockSize = $blockSize;
        // TODO DTO
        $this->volumes = [];
        foreach ($volumes as $id => $volume) {
            if (!file_exists($volume['path'])) {
                throw new ResourceNotFoundException();
            }
            $this->volumes[$id] = [
                'resource' => null,
                'path' => $volume['path'],
                'hash' => $volume['hash']
            ];
        }
    }

    /**
     * @param int $id
     * @return mixed
     * @throws IncorrectVolumeException
     * @throws CantOpenFileException
     */
    public function getVolumeResource(int $id)
    {
        if (!$this->volumeAvailable($id)) {
            throw new IncorrectVolumeException();
        }
        if ($this->volumes[$id]['resource']) {
            return $this->volumes[$id]['resource'];
        } else {
            $fRes = fopen($this->volumes[$id]['path'], 'c+b');
            if (!$fRes) {
                throw new CantOpenFileException();
            }
            $this->volumes[$id]['resource'] = $fRes;
            return $fRes;
        }
    }

    /**
     * @param int $id
     * @return string
     * @throws IncorrectVolumeException
     */
    public function getVolumePath(int $id) : string
    {
        if (!$this->volumeAvailable($id)) {
            throw new IncorrectVolumeException();
        }
        return $this->volumes[$id]['path'];
    }

    /**
     * @param int $id
     * @return int
     * @throws IncorrectVolumeException
     */
    public function getVolumeSize(int $id) : int
    {
        if (!$this->volumeAvailable($id)) {
            throw new IncorrectVolumeException();
        }
        return $this->blockSize;
    }

    /**
     * @param int $id
     * @return string
     * @throws IncorrectVolumeException
     */
    public function getVolumeHash(int $id) : string
    {
        if (!$this->volumeAvailable($id)) {
            throw new IncorrectVolumeException();
        }
        return $this->volumes[$id]['hash'];
    }

    /**
     * @param int $id
     * @return bool
     */
    public function volumeAvailable(int $id) : bool
    {
        return isset($this->volumes[$id]);
    }

    /**
     * @param StoragePosition $position
     * @return string
     * @throws IncorrectVolumeException
     * @throws IncorrectVolumePositionException
     * @throws VolumeReadException
     * @throws CantOpenFileException
     */
    public function read(StoragePosition $position) : string
    {
        if ($this->correctPosition($position)) {
            fseek($this->getVolumeResource($position->getVolume()), $position->getSeek());
            $result = fread($this->getVolumeResource($position->getVolume()), $position->getSize());
            if ($result === false) {
                throw new VolumeReadException();
            }
            return $result;
        } else {
            throw new IncorrectVolumePositionException();
        }
    }

    /**
     * @param StoragePosition $position
     * @param string $data
     * @return int
     * @throws IncorrectVolumeException
     * @throws IncorrectVolumePositionException
     * @throws VolumeWriteException
     * @throws CantOpenFileException
     */
    public function write(StoragePosition $position, string $data) : int
    {
        if ($this->correctPosition($position)) {
            fseek($this->getVolumeResource($position->getVolume()), $position->getSeek());
            $result = fwrite($this->getVolumeResource($position->getVolume()), $data);
            if ($result !== $position->getSize()) {
                throw new VolumeWriteException('fwrite != data size');
            }
            return $result;
        } else {
            throw new IncorrectVolumePositionException();
        }
    }

    /**
     * @return array
     */
    public function export() : array
    {
        $export = [];
        foreach ($this->volumes as $id => $volume) {
            $export[$id] = [
                'path' => $volume['path']
            ];
        }
        return $export;
    }

    /**
     * @param StoragePosition $pos
     * @return bool
     * @throws IncorrectVolumeException
     */
    private function correctPosition(StoragePosition $pos) : bool
    {
        if (!$this->volumeAvailable($pos->getVolume())) {
            return false;
        }
        if (($pos->getSeek() + $pos->getSize()) >= $this->blockSize) {
            return false;
        }
        return true;
    }
}