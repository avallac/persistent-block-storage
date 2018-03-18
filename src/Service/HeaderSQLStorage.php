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
}
