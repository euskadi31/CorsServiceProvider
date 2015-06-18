<?php
/*
 * This file is part of the CorsServiceProvider.
 *
 * (c) Axel Etcheverry <axel@etcheverry.biz>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Euskadi31\Silex\Provider\Cors;

use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Silex\Application;

/**
 * Initializes the CORS.
 *
 * @author Axel Etcheverry <axel@etcheverry.biz>
 */
class CorsListener implements EventSubscriberInterface
{
    protected $app;

    /**
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     *
     * @param  FilterResponseEvent $event
     * @return void
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();

        if (!$this->isCorsRequest($request)) {
            return;
        }

        if ($this->isPreflightRequest($request)) {
            if (!empty($this->app['cors.options']['allow_methods'])) {
                $allowedMethods = $this->app['cors.options']['allow_methods'];
            } else {
                $allowedMethods = $this->app['cors.allowed_methods'];
            }

            if (!in_array(
                $request->headers->get('Access-Control-Request-Method'),
                $allowedMethods
            )) {
                return;
            }

            $response->headers->set(
                'Access-Control-Allow-Headers',
                $request->headers->get('Access-Control-Request-Headers')
            );

            $response->headers->set('Access-Control-Allow-Methods', $allowedMethods);

            if (!empty($this->app['cors.options']['max_age'])) {
                $response->headers->set(
                    'Access-Control-Max-Age',
                    $this->app['cors.options']['max_age']
                );
            }

        } elseif (!empty($this->app['cors.options']['expose_headers'])) {
            $response->headers->set(
                'Access-Control-Expose-Headers',
                $this->app['cors.options']['expose_headers']
            );
        }

        $response->headers->set('Access-Control-Allow-Origin', '*');

        if ($this->app['cors.options']['allow_credentials']) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }
    }

    /**
     * Check if cors request
     *
     * @param  Request $request
     * @return boolean
     */
    private function isCorsRequest(Request $request)
    {
        return $request->headers->has('Origin');
    }

    /**
     * Check if preflight request
     *
     * @param  Request $request
     * @return boolean
     */
    private function isPreflightRequest(Request $request)
    {
        return (
            $request->getMethod() === 'OPTIONS' &&
            $request->headers->has('Access-Control-Request-Method')
        );
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => [['onKernelResponse', Application::LATE_EVENT]]
        ];
    }
}
