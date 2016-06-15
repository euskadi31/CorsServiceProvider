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

use Euskadi31\Silex\Provider\Cors\OptionsController;

class OptionsControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testController()
    {
        $controller = new OptionsController([
            'GET', 'POST'
        ]);

        $headers = $this->getMockBuilder('Symfony\Component\HttpFoundation\HeaderBag')
            ->disableOriginalConstructor()
            ->getMock();
        $headers->method('get')
            ->will($this->returnCallback(function($name) {
                switch ($name) {
                    case 'Access-Control-Request-Headers':
                        return 'Content-Type';
                }
            }));

        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $request->headers = $headers;

        $response = $controller($request);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertTrue($response->headers->has('Allow'));
        $this->assertEquals('GET,POST', $response->headers->get('Allow'));
        $this->assertEquals('*', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertEquals('GET,POST', $response->headers->get('Access-Control-Allow-Methods'));
        $this->assertEquals('Content-Type', $response->headers->get('Access-Control-Allow-Headers'));
        $this->assertEquals(204, $response->getStatusCode());
    }
}
