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
        $pimple['routes'] = function () use ($pimple) {
            $routes = new RouteCollection();
            $pattern = '/storage/original/{file}.{type}';
            $route = new Route($pattern, ['_controller' => [$pimple['fileController'], 'getOriginal']]);
            $routes->add('storage', $route);
            $pattern = '/storage/resize/{format}/{file}.{type}';
            $route = new Route($pattern, ['_controller' => [$pimple['fileController'], 'getResized']]);
            $routes->add('resized', $route);
            $pattern = '/volume/export/{volume}';
            $route = new Route($pattern, ['_controller' => [$pimple['volumeController'], 'serialize']]);
            $routes->add('volumeHeaders', $route);
            return $routes;
        };

        $pimple['router'] = function () use ($pimple) {
            return new UrlMatcher($pimple['routes'], new RequestContext('/'));
        };
    }
}
