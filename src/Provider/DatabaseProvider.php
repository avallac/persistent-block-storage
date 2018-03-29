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
        $pimple['db'] = function () use ($pimple) {
            $pdo = $pimple['config']['database']['dsn'] ?? null;
            $username = $pimple['config']['database']['username'] ?? null;
            $password = $pimple['config']['database']['password'] ?? null;
            return new \PDO($pdo, $username, $password);
        };
    }
}
