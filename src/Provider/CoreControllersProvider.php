<?php

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Controller\CoreStatusController;
use AVAllAC\PersistentBlockStorage\Controller\FileController;
use AVAllAC\PersistentBlockStorage\Controller\VolumeController;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreControllersProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['fileController'] = function () use ($pimple) {
            return new FileController(
                $pimple['headerStorage'],
                $pimple['loop'],
                $pimple['coreStorageManager'],
                $pimple['imagick']
            );
        };

        $pimple['volumeController'] = function () use ($pimple) {
            return new VolumeController(
                $pimple['headerStorage']
            );
        };

        $pimple['statusController'] = function () use ($pimple) {
            return new CoreStatusController(
                $pimple['coreSummary'],
                $pimple['twig']
            );
        };
    }
}
