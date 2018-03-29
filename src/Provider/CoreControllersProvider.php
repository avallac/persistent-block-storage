<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Controller\DashboardController;
use AVAllAC\PersistentBlockStorage\Controller\CoreUploadController;
use AVAllAC\PersistentBlockStorage\Controller\FileController;
use AVAllAC\PersistentBlockStorage\Controller\VolumeController;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreControllersProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['fileController'] = function () use ($pimple) {
            return new FileController(
                $pimple['serverAPI'],
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

        $pimple['dashboardController'] = function () use ($pimple) {
            return new DashboardController(
                $pimple['coreSummary'],
                $pimple['twig']
            );
        };

        $pimple['coreUploadController'] = function () use ($pimple) {
            return new CoreUploadController(
                $pimple['serverAPI'],
                $pimple['headerStorage'],
                $pimple['loop']
            );
        };
    }
}
