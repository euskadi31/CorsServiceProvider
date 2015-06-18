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

use Euskadi31\Silex\Provider\Cors\CorsListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Silex\Application;

class CorsListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testSubscribedEvents()
    {
        $this->assertEquals([
            KernelEvents::RESPONSE => [['onKernelResponse', Application::LATE_EVENT]]
        ], CorsListener::getSubscribedEvents());
    }

    public function testKernelResponseWithoutMasterRequest()
    {
        $appMock = $this->getMock('Silex\Application');
        $filterResponseEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
                     ->disableOriginalConstructor()
                     ->getMock();
        $filterResponseEventMock->expects($this->once())
            ->method('isMasterRequest')
            ->will($this->returnValue(false));

        $listener = new CorsListener($appMock);

        $listener->onKernelResponse($filterResponseEventMock);
    }

    public function testKernelResponseWithoutCorsRequest()
    {
        $appMock = $this->getMock('Silex\Application');

        $headersMock = $this->getMock('Symfony\Component\HttpFoundation\HeaderBag');
        $headersMock->method('has')
            ->with($this->equalTo('Origin'))
            ->will($this->returnValue(false));

        $requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $requestMock->headers = $headersMock;

        $responseMock = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $filterResponseEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
                     ->disableOriginalConstructor()
                     ->getMock();
        $filterResponseEventMock->expects($this->once())
            ->method('isMasterRequest')
            ->will($this->returnValue(true));
        $filterResponseEventMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($requestMock));
        $filterResponseEventMock->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($responseMock));

        $listener = new CorsListener($appMock);

        $listener->onKernelResponse($filterResponseEventMock);
    }

    public function testKernelResponseWithMethodNotAllowed()
    {
        $app = new Application;
        $app['cors.options'] = [
            'expose_headers'    => null,
            'max_age'           => null,
            'allow_credentials' => false,
            'allow_methods'     => []
        ];

        $app['cors.allowed_methods'] = function($app) {
            return ['GET', 'POST'];
        };

        $headersMock = $this->getMock('Symfony\Component\HttpFoundation\HeaderBag');
        $headersMock->method('has')
            ->will($this->returnCallback(function($name) {
                switch ($name) {
                    case 'Origin':
                        return true;
                    case 'Access-Control-Request-Method':
                        return true;
                    default:
                        return false;
                }
            }));
        $headersMock->method('get')
            ->will($this->returnCallback(function($name) {
                switch ($name) {
                    case 'Access-Control-Request-Method':
                        return 'DELETE';
                }
            }));

        $requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $requestMock->headers = $headersMock;
        $requestMock->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('OPTIONS'));

        $responseMock = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $filterResponseEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
                     ->disableOriginalConstructor()
                     ->getMock();
        $filterResponseEventMock->expects($this->once())
            ->method('isMasterRequest')
            ->will($this->returnValue(true));
        $filterResponseEventMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($requestMock));
        $filterResponseEventMock->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($responseMock));

        $listener = new CorsListener($app);

        $listener->onKernelResponse($filterResponseEventMock);
    }

    public function testKernelResponse()
    {
        $self = $this;

        $app = new Application;
        $app['cors.options'] = [
            'expose_headers'    => null,
            'max_age'           => 3600,
            'allow_credentials' => true,
            'allow_methods'     => []
        ];

        $app['cors.allowed_methods'] = function($app) {
            return ['GET', 'POST'];
        };

        $headersMock = $this->getMock('Symfony\Component\HttpFoundation\HeaderBag');
        $headersMock->method('has')
            ->will($this->returnCallback(function($name) {
                switch ($name) {
                    case 'Origin':
                        return true;
                    case 'Access-Control-Request-Method':
                        return true;
                    default:
                        return false;
                }
            }));
        $headersMock->method('get')
            ->will($this->returnCallback(function($name) {
                switch ($name) {
                    case 'Access-Control-Request-Method':
                        return 'GET';
                    case 'Access-Control-Request-Headers':
                        return 'X-Version';
                }
            }));
        $headersMock->method('set')
            ->will($this->returnCallback(function($name, $value) use ($self) {
                switch ($name) {
                    case 'Access-Control-Allow-Headers':
                        $self->assertEquals('X-Version', $value);
                        break;
                    case 'Access-Control-Allow-Methods':
                        $self->assertEquals('GET,POST', $value);
                        break;
                    case 'Access-Control-Max-Age':
                        $self->assertEquals('3600', $value);
                        break;
                    case 'Access-Control-Allow-Credentials':
                        $self->assertEquals('true', $value);
                        break;
                    case 'Access-Control-Allow-Origin':
                        $self->assertEquals('*', $value);
                        break;
                }
            }));

        $requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $requestMock->headers = $headersMock;
        $requestMock->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('OPTIONS'));

        $responseMock = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $filterResponseEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
                     ->disableOriginalConstructor()
                     ->getMock();
        $filterResponseEventMock->expects($this->once())
            ->method('isMasterRequest')
            ->will($this->returnValue(true));
        $filterResponseEventMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($requestMock));
        $filterResponseEventMock->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($responseMock));

        $listener = new CorsListener($app);

        $listener->onKernelResponse($filterResponseEventMock);
    }

    public function testKernelResponseWithAllowedMethodsOption()
    {
        $self = $this;

        $app = new Application;
        $app['cors.options'] = [
            'expose_headers'    => null,
            'max_age'           => 3600,
            'allow_credentials' => true,
            'allow_methods'     => ['DELETE', 'PUT']
        ];

        $app['cors.allowed_methods'] = function($app) {
            return ['GET', 'POST'];
        };

        $headersMock = $this->getMock('Symfony\Component\HttpFoundation\HeaderBag');
        $headersMock->method('has')
            ->will($this->returnCallback(function($name) {
                switch ($name) {
                    case 'Origin':
                        return true;
                    case 'Access-Control-Request-Method':
                        return true;
                    default:
                        return false;
                }
            }));
        $headersMock->method('get')
            ->will($this->returnCallback(function($name) {
                switch ($name) {
                    case 'Access-Control-Request-Method':
                        return 'GET';
                    case 'Access-Control-Request-Headers':
                        return 'X-Version';
                }
            }));
        $headersMock->method('set')
            ->will($this->returnCallback(function($name, $value) use ($self) {
                switch ($name) {
                    case 'Access-Control-Allow-Headers':
                        $self->assertEquals('X-Version', $value);
                        break;
                    case 'Access-Control-Allow-Methods':
                        $self->assertEquals('GET,POST', $value);
                        break;
                    case 'Access-Control-Max-Age':
                        $self->assertEquals('3600', $value);
                        break;
                    case 'Access-Control-Allow-Credentials':
                        $self->assertEquals('true', $value);
                        break;
                    case 'Access-Control-Allow-Origin':
                        $self->assertEquals('*', $value);
                        break;
                }
            }));

        $requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $requestMock->headers = $headersMock;
        $requestMock->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('OPTIONS'));

        $responseMock = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $filterResponseEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
                     ->disableOriginalConstructor()
                     ->getMock();
        $filterResponseEventMock->expects($this->once())
            ->method('isMasterRequest')
            ->will($this->returnValue(true));
        $filterResponseEventMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($requestMock));
        $filterResponseEventMock->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($responseMock));

        $listener = new CorsListener($app);

        $listener->onKernelResponse($filterResponseEventMock);
    }

    public function testKernelResponseWithoutPreflightRequest()
    {
        $self = $this;

        $app = new Application;
        $app['cors.options'] = [
            'expose_headers'    => 'X-Version',
            'max_age'           => 3600,
            'allow_credentials' => true,
            'allow_methods'     => []
        ];

        $app['cors.allowed_methods'] = function($app) {
            return ['GET', 'POST'];
        };

        $headersMock = $this->getMock('Symfony\Component\HttpFoundation\HeaderBag');
        $headersMock->method('has')
            ->will($this->returnCallback(function($name) {
                switch ($name) {
                    case 'Origin':
                        return true;
                    case 'Access-Control-Request-Method':
                        return true;
                    default:
                        return false;
                }
            }));
        $headersMock->method('get')
            ->will($this->returnCallback(function($name) {
                switch ($name) {
                    case 'Access-Control-Request-Method':
                        return 'GET';
                    case 'Access-Control-Request-Headers':
                        return 'X-Version';
                }
            }));
        $headersMock->method('set')
            ->will($this->returnCallback(function($name, $value) use ($self) {
                switch ($name) {
                    case 'Access-Control-Expose-Headers':
                        $self->assertEquals('X-Version', $value);
                        break;
                    case 'Access-Control-Allow-Origin':
                        $self->assertEquals('*', $value);
                        break;
                    case 'Access-Control-Allow-Credentials':
                        $self->assertEquals('true', $value);
                        break;
                }
            }));

        $requestMock = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $requestMock->headers = $headersMock;
        $requestMock->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $responseMock = $this->getMock('Symfony\Component\HttpFoundation\Response');

        $filterResponseEventMock = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterResponseEvent')
                     ->disableOriginalConstructor()
                     ->getMock();
        $filterResponseEventMock->expects($this->once())
            ->method('isMasterRequest')
            ->will($this->returnValue(true));
        $filterResponseEventMock->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($requestMock));
        $filterResponseEventMock->expects($this->once())
            ->method('getResponse')
            ->will($this->returnValue($responseMock));

        $listener = new CorsListener($app);

        $listener->onKernelResponse($filterResponseEventMock);
    }
}
