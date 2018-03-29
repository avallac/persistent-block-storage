<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Exception\BadConfigException;
use AVAllAC\PersistentBlockStorage\Service\HeaderSQLStorage;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HeaderStorageProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['headerStorage'] = function () use ($pimple) {
            $storageType = $pimple['config']['manager']['headerStorage'] ?? null;
            if ($storageType === 'sql') {
                return new HeaderSQLStorage($pimple['db']);
            } else {
                throw new BadConfigException();
            }
        };
    }
}
