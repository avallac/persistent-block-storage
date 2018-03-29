<?php

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;

interface HeaderStorage
{
    public function search(string $hash) : ?StoragePosition;
    public function export(int $volume) : string;
    public function insert(string $md5, int $size) : StoragePosition;
    public function checkExists(string $hash) : bool;
    public function beginTransaction();
    public function commit();
}
