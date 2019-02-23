<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;

class HeaderMemoryStorage implements HeaderStorage
{
    private $db;
    private $backup;
    private $transaction;
    private $volume;
    private $position;


    public function __construct()
    {
        $this->db = [];
        $this->volume = 0;
        $this->position = 0;
    }

    /**
     * @param string $hash
     * @return StoragePosition|null
     * @throws \AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException
     */
    public function search(string $hash) : ?StoragePosition
    {
        if (isset($this->db[$hash])) {
            new StoragePosition($this->db[$hash]['volume'], $this->db[$hash]['seek'], $this->db[$hash]['size']);
        }
        return null;
    }

    /**
     * @param int $volume
     * @return string
     */
    public function export(int $volume) : string
    {
        $output = '';
        foreach ($this->db as $item) {
            if ($item['volume'] === $volume) {
                $output .= pack('a16J2', hex2bin($item['md5']), $item['seek'], $item['size']);
            }

        }
        return $output;
    }

    /**
     * @param string $md5
     * @param int $size
     * @return StoragePosition
     * @throws \Exception
     */
    public function insert(string $md5, int $size) : StoragePosition
    {
        $storagePosition = $this->reservationPosition($size);
        $this->db[$md5] = [
            'volume' => $storagePosition->getVolume(),
            'md5' => $md5,
            'seek' => $storagePosition->getSeek(),
            'size' => $size,
            'valid' => true
        ];
        return $storagePosition;
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function checkExists(string $hash) : bool
    {
        return isset($this->db[$hash]) ? true : false;
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function checkExistsValid(string $hash) : bool
    {
        return isset($this->db[$hash]) && $this->db[$hash]['valid'] ? true : false;
    }

    public function beginTransaction() : void
    {
        if ($this->transaction) {
            throw new \Exception('second transaction');
        }
        $this->transaction = true;
        $this->backup = $this->db;
    }

    public function commit() : void
    {
        $this->transaction = false;
    }

    public function rollBack() : void
    {
        $this->db = $this->backup;
        $this->transaction = false;
    }

    public function markBroken(string $hash) : void
    {
        $this->db[$hash]['valid'] = false;
    }

    public function markOk(string $hash) : void
    {
        $this->db[$hash]['valid'] = true;
    }

    /**
     * @param int $size
     * @return StoragePosition
     * @throws \Exception
     */
    private function reservationPosition(int $size) : StoragePosition
    {
        if (($this->position + $size) <  self::VOLUME_SIZE) {
            $pos = new StoragePosition($this->volume, $this->position, $size);
            $this->position += $size;
            return $pos;
        } else {
            $this->volume++;
            $this->position = 0;
        }
    }
}

