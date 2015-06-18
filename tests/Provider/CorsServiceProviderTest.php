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

use Euskadi31\Silex\Provider\CorsServiceProvider;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class CorsProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testRegister()
    {
        $app = new Application;

        $app->register(new CorsServiceProvider);

        $app->get('/me', function() {
            return 'Hi!';
        });

        $response = $app->handle(Request::create('/me'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertFalse($response->headers->has('Access-Control-Allow-Origin'));

        $this->assertInstanceOf('Euskadi31\Silex\Provider\Cors\CorsListener', $app['cors.listener']);

        $this->assertEquals([
            '/me' => [
                'methods'       => ['GET'],
                'requirements'  => []
            ]
        ], $app['cors.allowed_methods']);

        $request = Request::create('/me', 'OPTIONS');
        $request->headers->set('Origin', 'localhost');

        $response = $app->handle($request);

        $this->assertEquals(204, $response->getStatusCode());
        $this->assertTrue($response->headers->has('Allow'));
        $this->assertEquals('GET', $response->headers->get('Allow'));
        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
    }
}
