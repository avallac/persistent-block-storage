<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;

class HeaderSQLStorage implements HeaderStorage
{
    private $db;
    private $dbExists;
    private $dbSearch;
    private $dbExport;
    private $dbInsert;

    /**
     * HeaderSQLStorage constructor.
     * @param \PDO $db
     */
    public function __construct(\PDO $db)
    {
        $this->db = $db;
        $this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $sql = 'SELECT id FROM storage WHERE md5 = :HASH';
        $this->dbExists =  $this->db->prepare($sql);
        $sql = 'SELECT volume, seek, size FROM storage WHERE md5 = :HASH';
        $this->dbSearch =  $this->db->prepare($sql);
        $sql = 'SELECT md5, seek, size FROM storage WHERE volume = :VOLUME ORDER BY seek';
        $this->dbExport =  $this->db->prepare($sql);
        $sql = 'INSERT INTO storage (volume,md5,seek,size) VALUES (:volume, :md5, :seek, :size)';
        $this->dbInsert =  $this->db->prepare($sql);
    }

    /**
     * @param string $hash
     * @return array|null
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

    /**
     * @param int $size
     * @return StoragePosition
     * @throws \Exception
     */
    private function reservationPosition(int $size) : StoragePosition
    {
        $stmt = $this->db->prepare("SELECT volume,pos,max_size FROM storage_last");
        $stmt->execute();
        $e = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (($e['pos'] + $size) < $e['max_size']) {
            $stmt = $this->db->prepare("UPDATE storage_last SET pos = :newPos WHERE pos = :oldPos");
            $stmt->execute([
                'newPos' => $e['pos'] + $size,
                'oldPos' => $e['pos']
            ]);
            return new StoragePosition($e['volume'], $e['pos'], $size);
        } else {
            $sql = "UPDATE storage_last SET volume = volume + 1, pos = :newPos WHERE pos = :oldPos";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'newPos' => $size,
                'oldPos' => $e['pos']
            ]);
            return new StoragePosition($e['volume'] + 1, 0, $size);
        }
    }
}

