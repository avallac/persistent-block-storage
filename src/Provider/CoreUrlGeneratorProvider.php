<?php

namespace AVAllAC\PersistentBlockStorage\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;

class CoreUrlGeneratorProvider implements ServiceProviderInterface
{
    public function register(Container $pimple) : void
    {
        $pimple['urlGenerator'] = function () use ($pimple) {
            $context = new RequestContext($pimple['config']['core']);
            return new UrlGenerator($pimple['routes'], $context);
        };
    }
}
