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

        $response = $controller();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertTrue($response->headers->has('Allow'));
        $this->assertEquals('GET,POST', $response->headers->get('Allow'));
        $this->assertEquals(204, $response->getStatusCode());
    }
}
