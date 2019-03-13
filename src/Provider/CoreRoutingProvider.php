<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use AVAllAC\PersistentBlockStorage\Controller\CoreDashboardController;

class CoreRoutingProvider implements ServiceProviderInterface
{
    protected function getController(Container $pimple, string $name)
    {
        if ($pimple->offsetExists($name)) {
            return $pimple[$name];
        }

        return null;
    }

    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        // TODO Константы
        $pimple['CoreRoutes'] = function () use ($pimple) {
            $routes = new RouteCollection();

            # storage
            $route = new Route(
                '/storage/original/{file}.{type}',
                [
                    '_controller' =>
                        [
                            $this->getController($pimple, 'CoreDeliveryController'),
                            'getOriginal'
                        ]
                ]
            );
            $routes->add('storage', $route);

            # volumeHeaders
            $route = new Route(
                '/volume/export/{volume}',
                [
                    '_controller' =>
                        [
                            $this->getController($pimple, 'VolumeController'),
                            'serialize'
                        ]
                ]
            );
            $routes->add('volumeHeaders', $route);

            # volumes
            $route = new Route(
                '/volumes',
                ['_controller' =>
                    [
                        $this->getController($pimple, 'CoreDashboardController'),
                        'volumes'
                    ]
                ]
            );
            $routes->add(CoreDashboardController::VOLUMES, $route);


            # servers
            $route = new Route(
                '/servers',
                ['_controller' =>
                    [
                        $this->getController($pimple, 'CoreDashboardController'),
                        'servers'
                    ]
                ]
            );
            $routes->add(CoreDashboardController::SERVERS, $route);

            # upload
            $route = new Route(
                '/upload',
                [
                    '_controller' =>
                        [
                            $this->getController($pimple, 'CoreUploadController'),
                            'upload'
                        ]
                ]
            );
            $routes->add('upload', $route);

            # report
            $route = new Route(
                '/report/{hash}',
                [
                    '_controller' =>
                        [
                            $this->getController($pimple, 'CoreReportController'),
                            'report'
                        ]
                ]
            );
            $routes->add('report', $route);

            return $routes;
        };
        
        $pimple['CoreUrlGenerator'] = function () use ($pimple) {
            $context = new RequestContext($pimple['config']['coreUrl']);
            return new UrlGenerator($pimple['CoreRoutes'], $context);
        };

        $pimple['CoreRouter'] = function () use ($pimple) {
            return new UrlMatcher($pimple['CoreRoutes'], new RequestContext('/'));
        };
    }
}
