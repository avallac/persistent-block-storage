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
use AVAllAC\PersistentBlockStorage\Controller\ServerDeliveryController;

class ServerDeliveryRoutingProvider implements ServiceProviderInterface
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
        $pimple['ServerDeliveryRoutes'] = function () use ($pimple) {
            $routes = new RouteCollection();

            # get
            $route = new Route(
                '/{md5}/{volume}/{seek}/{size}',
                [
                    '_controller' =>
                        [
                            $this->getController($pimple, 'ServerDeliveryController'),
                            'get'
                        ]
                ]
            );
            $routes->add(ServerDeliveryController::GET, $route);

            return $routes;
        };

        $pimple['ServerDeliveryUrlGenerator'] = function () use ($pimple) {
            return new UrlGenerator($pimple['ServerDeliveryRoutes'], new RequestContext());
        };

        $pimple['ServerDeliveryRouter'] = function () use ($pimple) {
            return new UrlMatcher($pimple['ServerDeliveryRoutes'], new RequestContext('/'));
        };
    }
}