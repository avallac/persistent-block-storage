<?php

namespace AVAllAC\PersistentBlockStorage\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DatabaseProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['Db'] = function () use ($pimple) {
            $pdo = $pimple['config']['core']['database']['dsn'] ?? null;
            $username = $pimple['config']['core']['database']['username'] ?? null;
            $password = $pimple['config']['core']['database']['password'] ?? null;
            return new \PDO($pdo, $username, $password);
        };
    }
}
