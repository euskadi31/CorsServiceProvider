<?php
/*
 * This file is part of the CorsServiceProvider.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Silex\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\BootableProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\Application;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * CORS integration for Silex.
 *
 * @author Axel Etcheverry <axel@etcheverry.biz>
 */
class CorsServiceProvider implements ServiceProviderInterface, BootableProviderInterface, EventListenerProviderInterface
{
    /**
     * {@inheritDoc}
     */
    public function register(Container $app)
    {
        $app['cors.options'] = [
            'expose_headers'    => null,
            'max_age'           => null,
            'allow_credentials' => false,
            'allow_methods'     => []
        ];

        $app['cors.listener'] = function($app) {
            return new Cors\CorsListener($app);
        };

        $app['cors.allowed_methods'] = function($app) {
            $allow = [];

            foreach ($app['routes'] as $route) {
                $path = $route->getPath();

                if (!array_key_exists($path, $allow)) {
                    $allow[$path] = [
                        'methods'       => [],
                        'requirements'  => []
                    ];
                }

                $requirements = $route->getRequirements();

                unset($requirements['_method']);

                $allow[$path]['methods']        = array_merge(
                    $allow[$path]['methods'],
                    $route->getMethods()
                );

                $allow[$path]['requirements']   = array_merge(
                    $allow[$path]['requirements'],
                    $requirements
                );
            }

            return $allow;
        };
    }

    /**
     * {@inheritDoc}
     */
    public function boot(Application $app)
    {
        $app->flush();
        $this->createOptionsRoutes($app);
    }

    /**
     * {@inheritDoc}
     */
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app['cors.listener']);
    }

    /**
     * Create options route
     *
     * @param  Application $app
     * @return void
     */
    private function createOptionsRoutes(Application $app)
    {
        foreach ($app['cors.allowed_methods'] as $path => $route) {
            $app->match($path, new Cors\OptionsController($route['methods']))
                ->setRequirements($route['requirements'])
                ->method('OPTIONS');
        }
    }
}
