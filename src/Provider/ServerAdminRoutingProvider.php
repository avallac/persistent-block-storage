<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use AVAllAC\PersistentBlockStorage\Controller\BaseController;
use AVAllAC\PersistentBlockStorage\Controller\ServerStatusController;
use AVAllAC\PersistentBlockStorage\Controller\ServerUploadController;

class ServerAdminRoutingProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     * @param string $name
     * @return BaseController|null
     */
    protected function getController(Container $pimple, string $name) : ?BaseController
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
        $pimple['ServerAdminRoutes'] = function () use ($pimple) {
            $routes = new RouteCollection();

            # status
            $route = new Route(
                '/status',
                [
                    '_controller' =>
                        [
                            $this->getController($pimple, 'ServerStatusController'),
                            ServerStatusController::STATUS
                        ]
                ]
            );
            $routes->add(ServerStatusController::STATUS, $route);

            # upload
            $route = new Route(
                '/upload/{md5}/{volume}/{seek}/{size}',
                [
                    '_controller' =>
                        [
                            $this->getController($pimple, 'ServerUploadController'),
                            ServerUploadController::UPLOAD
                        ]
                ]
            );
            $routes->add(ServerUploadController::UPLOAD, $route);


            return $routes;
        };

        $pimple['ServerAdminUrlGenerator'] = function () use ($pimple) {
            return new UrlGenerator($pimple['ServerAdminRoutes'], new RequestContext());
        };

        $pimple['ServerAdminRouter'] = function () use ($pimple) {
            return new UrlMatcher($pimple['ServerAdminRoutes'], new RequestContext('/'));
        };
    }
}