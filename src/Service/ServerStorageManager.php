<?php

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Exception\CantOpenFileException;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Exception\ResourceNotFoundException;

class ServerStorageManager
{
    private $volumes;

    /**
     * @param array
     * @throws CantOpenFileException
     * @throws ResourceNotFoundException
     */
    public function __construct(array $volumes)
    {
        $this->volumes = [];
        foreach ($volumes as $id => $volume) {
            if (!file_exists($volume['path'])) {
                throw new ResourceNotFoundException();
            }
            $fRes = fopen($volume['path'], 'rb');
            if (!$fRes) {
                throw new CantOpenFileException();
            }
            $this->volumes[$id] = [
                'size' => $volume['size'],
                'resource' => $fRes,
                'path' => $volume['path'],
                'hash' => $volume['hash']
            ];
        }
    }

    public function getVolumeResource($id)
    {
        if (!$this->volumeAvailable($id)) {
            throw new IncorrectVolumeException();
        }
        return $this->volumes[$id]['resource'];
    }

    public function getVolumePath($id) : string
    {
        if (!$this->volumeAvailable($id)) {
            throw new IncorrectVolumeException();
        }
        return $this->volumes[$id]['path'];
    }

    public function getVolumeSize($id) : int
    {
        if (!$this->volumeAvailable($id)) {
            throw new IncorrectVolumeException();
        }
        return $this->volumes[$id]['size'];
    }

    public function getVolumeHash($id) : string
    {
        if (!$this->volumeAvailable($id)) {
            throw new IncorrectVolumeException();
        }
        return $this->volumes[$id]['hash'];
    }

    public function volumeAvailable($id) : bool
    {
        return isset($this->volumes[$id]);
    }

    public function read($volumeId, $seek, $size) : string
    {
        fseek($this->volumes[$volumeId]['resource'], $seek);
        return fread($this->volumes[$volumeId]['resource'], $size);
    }

    public function export()
    {
        $export = [];
        foreach ($this->volumes as $id => $volume) {
            $export[$id] = $volume['size'];
        }
        return $export;
    }
}