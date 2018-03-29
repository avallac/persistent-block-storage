<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class CoreRoutingProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['coreRoutes'] = function () use ($pimple) {
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
            $route = new Route('/summary', ['_controller' => [$pimple['dashboardController'], 'summary']]);
            $routes->add('summary', $route);
            $route = new Route('/upload', ['_controller' => [$pimple['coreUploadController'], 'upload']]);
            $routes->add('upload', $route);
            return $routes;
        };
        
        $pimple['coreUrlGenerator'] = function () use ($pimple) {
            $context = new RequestContext($pimple['config']['core']);
            return new UrlGenerator($pimple['coreRoutes'], $context);
        };

        $pimple['coreRouter'] = function () use ($pimple) {
            return new UrlMatcher($pimple['coreRoutes'], new RequestContext('/'));
        };
    }
}
