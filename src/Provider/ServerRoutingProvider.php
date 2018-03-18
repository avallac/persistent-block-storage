<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ServerRoutingProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['router'] = function () use ($pimple) {
            $routes = new RouteCollection();
            $route = new Route('/status', ['_controller' => [$pimple['statusController'], 'status']]);
            $routes->add('status', $route);
            return new UrlMatcher($routes, new RequestContext('/'));
        };
    }
}