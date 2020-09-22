<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Exception\BadConfigException;
use AVAllAC\PersistentBlockStorage\Service\HeaderSQLStorage;
use AVAllAC\PersistentBlockStorage\Service\HeaderMemoryStorage;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HeaderStorageProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['HeaderStorage'] = function () use ($pimple) {
            $storageType = $pimple['config']['core']['headerStorage'] ?? null;
            if ($storageType === 'sql') {
                return new HeaderSQLStorage($pimple['Db'], $pimple['CoreStorageManager']);
            } elseif ($storageType === 'memory') {
                return new HeaderMemoryStorage();
            } else {
                throw new BadConfigException();
            }
        };
    }
}
