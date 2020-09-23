<?php

namespace AVAllAC\PersistentBlockStorage\Service;

use AVAllAC\PersistentBlockStorage\Model\StoragePosition;

interface HeaderStorage
{
    const VOLUME_SIZE = 99999999999;

    public function search(string $hash) : ?StoragePosition;
    public function export(int $volume) : string;
    public function exportAll() : array;
    public function insert(string $md5, int $size) : StoragePosition;
    public function checkExists(string $hash) : bool;
    public function checkExistsValid(string $hash) : bool;
    public function beginTransaction() : void;
    public function commit() : void;
    public function rollBack() : void;
    public function markBroken(string $hash) : void;
    public function markOk(string $hash) : void;
}
