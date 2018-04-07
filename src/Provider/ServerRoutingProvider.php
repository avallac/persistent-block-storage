<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ServerRoutingProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['serverRoutes'] = function () use ($pimple) {
            $routes = new RouteCollection();
            $route = new Route('/status', ['_controller' => [$pimple['statusController'], 'status']]);
            $routes->add('status', $route);
            $pattern = '/upload/{md5}/{volume}/{seek}/{size}';
            $route = new Route($pattern, ['_controller' => [$pimple['serverUploadController'], 'upload']]);
            $routes->add('upload', $route);
            return $routes;
        };

        $pimple['serverUrlGenerator'] = function () use ($pimple) {
            return new UrlGenerator($pimple['serverRoutes'], new RequestContext());
        };

        $pimple['serverRouter'] = function () use ($pimple) {
            return new UrlMatcher($pimple['serverRoutes'], new RequestContext('/'));
        };
    }
}