<?php declare(strict_types=1);

namespace AVAllAC\PersistentBlockStorage\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TwigProvider implements ServiceProviderInterface
{
    /**
     * @param Container $pimple
     */
    public function register(Container $pimple) : void
    {
        $pimple['twig'] = function () use ($pimple) {
            $loader = new \Twig_Loader_Filesystem(__DIR__ . '/../../templates/');
            $twig = new \Twig_Environment($loader, ['debug' => true]);
            $twig->addExtension(new \Twig_Extension_Debug());
            return $twig;
        };
    }
}