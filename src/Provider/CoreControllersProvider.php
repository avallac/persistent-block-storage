<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use AVAllAC\PersistentBlockStorage\Controller\CoreDashboardController;
use AVAllAC\PersistentBlockStorage\Controller\CoreUploadController;
use AVAllAC\PersistentBlockStorage\Controller\CoreDeliveryController;
use AVAllAC\PersistentBlockStorage\Controller\CoreReportController;
use AVAllAC\PersistentBlockStorage\Controller\CoreVolumeExportController;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class CoreControllersProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['CoreDeliveryController'] = function () use ($pimple) {
            return new CoreDeliveryController(
                $pimple['ServerAPI'],
                $pimple['HeaderStorage'],
                $pimple['Loop'],
                $pimple['CoreStorageManager'],
                $pimple['Imagick']
            );
        };

        $pimple['CoreVolumeExportController'] = function () use ($pimple) {
            return new CoreVolumeExportController(
                $pimple['HeaderStorage']
            );
        };

        $pimple['CoreDashboardController'] = function () use ($pimple) {
            return new CoreDashboardController(
                $pimple['CoreVolumesSummary'],
                $pimple['Twig']
            );
        };

        $pimple['CoreReportController'] = function () use ($pimple) {
            return new CoreReportController(
                $pimple['HeaderStorage']
            );
        };

        $pimple['CoreUploadController'] = function () use ($pimple) {
            return new CoreUploadController(
                $pimple['ServerAPI'],
                $pimple['HeaderStorage'],
                $pimple['Loop']
            );
        };
    }
}
