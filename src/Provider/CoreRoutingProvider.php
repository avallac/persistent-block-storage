<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CoreRoutingProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['router'] = function () use ($pimple) {
            $routes = new RouteCollection();
            $route = new Route('/storage/{file}.{type}', ['_controller' => [$pimple['fileController'], 'get']]);
            $routes->add('storage', $route);
            return new UrlMatcher($routes, new RequestContext('/'));
        };
    }
}
