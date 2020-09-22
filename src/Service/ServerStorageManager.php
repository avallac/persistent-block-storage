<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Exception\CantOpenFileException;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException;
use AVAllAC\PersistentBlockStorage\Exception\ResourceNotFoundException;
use AVAllAC\PersistentBlockStorage\Exception\VolumeReadException;
use AVAllAC\PersistentBlockStorage\Exception\VolumeWriteException;
use AVAllAC\PersistentBlockStorage\Exception\VolumeBadHeader;
use AVAllAC\PersistentBlockStorage\Exception\VolumeUnknown;
use AVAllAC\PersistentBlockStorage\Model\StoragePosition;
use AVAllAC\PersistentBlockStorage\Model\VolumeServerInfo;

class ServerStorageManager
{
    const VERSION_STORAGE = 2;
    const FILE_HEAD_SIZE = 24;
    /** @var VolumeServerInfo[] */
    protected $volumes;
    protected $logger;

    /**
     * ServerStorageManager constructor.
     * @param array $volumes
     * @param array $coreVolumes
     * @throws CantOpenFileException
     * @throws IncorrectVolumeException
     * @throws IncorrectVolumePositionException
     * @throws ResourceNotFoundException
     * @throws VolumeBadHeader
     * @throws VolumeReadException
     * @throws VolumeUnknown
     */
    public function __construct(array $volumes, array $coreVolumes, Logger $logger)
    {
        $this->logger = $logger;
        $this->volumes = [];
        foreach ($volumes as $id => $path) {
            if (!file_exists($path)) {
                throw new ResourceNotFoundException();
            }
            if (empty($coreVolumes[$id])) {
                throw new VolumeUnknown();
            }
            $uid = $coreVolumes[$id]['uid'];
            $size = $coreVolumes[$id]['size'];
            $this->volumes[$id] = new VolumeServerInfo($id, $uid, $size, $path);
            $fRes = fopen($this->volumes[$id]->getPath(), 'c+b');
            if (empty($fRes)) {
                throw new CantOpenFileException();
            }
            $this->volumes[$id]->setResource($fRes);
            $this->checkHeader($id, $uid, $size);
        }
    }

    /**
     * @param int $id
     * @param string $uid
     * @param int $size
     * @throws IncorrectVolumeException
     * @throws IncorrectVolumePositionException
     * @throws VolumeBadHeader
     * @throws VolumeReadException
     */
    protected function checkHeader(int $id, string $uid, int $size)
    {
        $position = new StoragePosition($id, 0, 64);
        $data = $this->read($position);
        $header = unpack("Cversion/A16uid/Jid/Jsize/Jflags", $data);
        if ($header['version'] !== self::VERSION_STORAGE) {
            throw new VolumeBadHeader('Unsupported version for block #' . $id);
        }
        if ($header['id'] !== $id) {
            throw new VolumeBadHeader('Incorrect ID for block #' . $id . ' ' . $header['id'] . '[expect ' . $uid . ']');
        }
        if ($header['uid'] !== $uid) {
            throw new VolumeBadHeader('Incorrect UID for block #' . $id . ' ' . $header['uid'] . '[expect ' . $uid . ']');
        }
        if ($header['size'] !== $size) {
            throw new VolumeBadHeader('Incorrect size for block #' . $id . ' ' . $header['size'] . '[expect ' . $size . ']');
        }
    }

    /**
     * @param int $id
     * @return mixed
     * @throws IncorrectVolumeException
     */
    public function getVolumeResource(int $id)
    {
        if (!$this->volumeAvailable($id)) {
            throw new IncorrectVolumeException();
        }

        return $this->volumes[$id]->getResource();
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
        return $this->volumes[$id]->getPath();
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
        return $this->volumes[$id]->getSize();
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
     * @return string
     * @throws IncorrectVolumeException
     * @throws IncorrectVolumePositionException
     * @throws VolumeReadException
     */
    public function readBlock(StoragePosition $position) : string
    {
        if ($this->correctPosition($position)) {
            fseek($this->getVolumeResource($position->getVolume()), $position->getSeek());
            $headerBin = fread($this->getVolumeResource($position->getVolume()), self::FILE_HEAD_SIZE);
            $header = unpack('Jsize/a16md5', $headerBin);
            $result = fread($this->getVolumeResource($position->getVolume()), $position->getSize() - self::FILE_HEAD_SIZE);
            if ($result === false) {
                $this->logger->error(
                    'ServerStorageManager',
                    'read result false',
                    [
                        'position' => $position->toArray(),
                    ]
                );
                throw new VolumeReadException();
            }
            if ($header['size'] !== $position->getSize()) {
                $this->logger->error(
                    'ServerStorageManager',
                    'read size mismatch',
                    [
                        'inBlockSize' => $header['size'],
                        'readBlockSize' => $position->getSize(),
                        'position' => $position->toArray(),
                    ]
                );
                throw new VolumeReadException();
            }
            if (bin2hex($header['md5']) !== md5($result)) {
                $this->logger->error(
                    'ServerStorageManager',
                    'read md5 mismatch',
                    [
                        'realMd5' => md5($result),
                        'inBlockMd5' => bin2hex($header['md5']),
                        'position' => $position->toArray(),
                    ]
                );
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
     * @param string $md5
     * @return int
     * @throws IncorrectVolumeException
     * @throws IncorrectVolumePositionException
     * @throws VolumeWriteException
     */
    public function write(StoragePosition $position, string $data, string $md5) : int
    {
        if ($this->correctPosition($position)) {
            fseek($this->getVolumeResource($position->getVolume()), $position->getSeek());
            $pack = pack('Ja16', $position->getSize(), hex2bin($md5));
            $result = fwrite($this->getVolumeResource($position->getVolume()), $pack . $data);
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
                'path' => $volume->getPath()
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
        if (($pos->getSeek() + $pos->getSize()) >= $this->getVolumeSize($pos->getVolume())) {
            return false;
        }
        return true;
    }
}