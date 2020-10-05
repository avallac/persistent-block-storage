<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;
use AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumeException;
use AVAllAC\PersistentBlockStorage\Model\VolumeInfo;

class HeaderSQLStorage implements HeaderStorage
{
    protected $db;
    protected $dbExists;
    protected $dbValid;
    protected $dbSearch;
    protected $dbExport;
    protected $dbExportAll;
    protected $dbInsert;
    protected $dbMarkBroken;
    protected $dbMarkOk;
    protected $dbStorageInfo;
    protected $dbStorageInit;
    protected $dbStorageUpdateCurrentVolume;
    protected $dbStorageUpdateNextVolume;
    protected $coreStorageManager;

    public function __construct(\PDO $db, CoreStorageManager $coreStorageManager)
    {
        $this->db = $db;
        $this->coreStorageManager = $coreStorageManager;
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $sql = 'SELECT id FROM storage WHERE md5 = :HASH';
        $this->dbExists = $this->db->prepare($sql);

        $sql = 'SELECT id FROM storage WHERE md5 = :HASH AND broken = 0';
        $this->dbValid =  $this->db->prepare($sql);

        $sql = 'SELECT volume, seek, size FROM storage WHERE md5 = :HASH';
        $this->dbSearch = $this->db->prepare($sql);

        $sql = 'SELECT md5, seek, size FROM storage WHERE volume = :VOLUME ORDER BY seek';
        $this->dbExport = $this->db->prepare($sql);

        $sql = 'SELECT md5,size FROM storage';
        $this->dbExportAll = $this->db->prepare($sql);

        $sql = 'INSERT INTO storage (volume,md5,seek,size) VALUES (:volume, :md5, :seek, :size)';
        $this->dbInsert = $this->db->prepare($sql);

        $sql = 'UPDATE storage SET broken = 1 WHERE md5 = :HASH';
        $this->dbMarkBroken = $this->db->prepare($sql);

        $sql = 'UPDATE storage SET broken = 0 WHERE md5 = :HASH';
        $this->dbMarkOk = $this->db->prepare($sql);

        $sql = 'SELECT volume, pos FROM storage_last ORDER BY volume desc limit 1';
        $this->dbStorageInfo = $this->db->prepare($sql);

        $sql = 'INSERT INTO storage_last (volume, pos) VALUES (0, :newPos)';
        $this->dbStorageInit = $this->db->prepare($sql);

        $sql = 'UPDATE storage_last SET pos = :newPos WHERE pos = :oldPos';
        $this->dbStorageUpdateCurrentVolume = $this->db->prepare($sql);

        $sql = 'UPDATE storage_last SET volume = volume + 1, pos = :newPos WHERE pos = :oldPos';
        $this->dbStorageUpdateNextVolume = $this->db->prepare($sql);
    }

    /**
     * @param string $hash
     * @return StoragePosition|null
     * @throws \AVAllAC\PersistentBlockStorage\Exception\IncorrectVolumePositionException
     */
    public function search(string $hash) : ?StoragePosition
    {
        $this->dbSearch->execute([':HASH' => $hash]);
        if ($data = $this->dbSearch->fetch()) {
            return new StoragePosition($data['volume'], $data['seek'], $data['size']);
        } else {
            return null;
        }
    }

    /**
     * @param int $volume
     * @return string
     */
    public function export(int $volume) : string
    {
        $output = '';
        $this->dbExport->execute([':VOLUME' => $volume]);
        while ($e = $this->dbExport->fetch(\PDO::FETCH_ASSOC)) {
            $output .= pack('a16J2', hex2bin($e['md5']), $e['seek'], $e['size']);
        }
        return $output;
    }

    /**
     * @return array
     */
    public function exportAll() : array
    {
        $result = [];
        $this->dbExportAll->execute();
        while ($e = $this->dbExportAll->fetch(\PDO::FETCH_ASSOC)) {
            $result[$e['md5']] = $e['size'];
        }

        return $result;
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
        $this->dbInsert->execute([
            'volume' => $storagePosition->getVolume(),
            'md5' => $md5,
            'seek' => $storagePosition->getSeek(),
            'size' => $size
        ]);
        return $storagePosition;
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function checkExists(string $hash) : bool
    {
        $this->dbExists->execute([':HASH' => $hash]);
        return $this->dbExists->fetchColumn() ? true : false;
    }

    /**
     * @param string $hash
     * @return bool
     */
    public function checkExistsValid(string $hash) : bool
    {
        $this->dbValid->execute([':HASH' => $hash]);
        return $this->dbValid->fetchColumn() ? true : false;
    }

    public function beginTransaction() : void
    {
        $this->db->exec('BEGIN TRANSACTION ISOLATION LEVEL SERIALIZABLE');
    }

    public function commit() : void
    {
        $this->db->exec('COMMIT');
    }

    public function rollBack() : void
    {
        $this->db->exec('ROLLBACK');
    }

    public function markBroken(string $hash) : void
    {
        $this->dbMarkBroken->execute([':HASH' => $hash]);
    }

    public function markOk(string $hash) : void
    {
        $this->dbMarkOk->execute([':HASH' => $hash]);
    }

    /**
     * @param int $size
     * @return StoragePosition
     * @throws \Exception
     */
    private function reservationPosition(int $size) : StoragePosition
    {
        $this->dbStorageInfo->execute();
        $e = $this->dbStorageInfo->fetch();
        if ($e !== false) {
            $blockSize = -1;
            if ($volume = $this->coreStorageManager->getVolumeInfo($e['volume'])) {
                $blockSize = $volume->getSize();
            }
            if (($e['pos'] + $size) < $blockSize) {
                $this->dbStorageUpdateCurrentVolume->execute([
                    'newPos' => $e['pos'] + $size,
                    'oldPos' => $e['pos']
                ]);
                return new StoragePosition($e['volume'], $e['pos'], $size);
            } else {
                if (empty($this->coreStorageManager->getVolumeInfo($e['volume'] + 1))) {
                    throw new IncorrectVolumeException();
                }
                $this->dbStorageUpdateNextVolume->execute([
                    'newPos' => $size + VolumeInfo::HEADER_SIZE,
                    'oldPos' => $e['pos']
                ]);
                return new StoragePosition($e['volume'] + 1, VolumeInfo::HEADER_SIZE, $size);
            }
        } else {
            $this->dbStorageInit->execute(['newPos' => $size + VolumeInfo::HEADER_SIZE]);
            return new StoragePosition(0, VolumeInfo::HEADER_SIZE, $size);
        }
    }
}

