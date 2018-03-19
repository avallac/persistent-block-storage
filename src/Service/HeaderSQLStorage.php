<?php

namespace AVAllAC\PersistentBlockStorage\Service;

class HeaderSQLStorage implements HeaderStorage
{
    protected $db;

    public function __construct(\PDO $db)
    {
        $this->db = $db;
    }

    public function search(string $hash) : ?array
    {
        $stmt = $this->db->prepare('SELECT volume, seek, size FROM storage WHERE md5 = :HASH');
        $stmt->execute([':HASH' => $hash]);
        return $stmt->fetch();
    }

    public function export(int $volume) : ?array
    {
        $sql = 'SELECT md5, seek, size FROM storage WHERE volume = :VOLUME ORDER BY seek';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':VOLUME' => $volume]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
