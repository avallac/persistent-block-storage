<?php

namespace AVAllAC\PersistentBlockStorage\Service;

interface HeaderStorage
{
    public function search(string $hash) : ?array;
}
