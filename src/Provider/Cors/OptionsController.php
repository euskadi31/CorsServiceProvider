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

use Symfony\Component\HttpFoundation\Response;

/**
 * Options controller.
 *
 * @author Axel Etcheverry <axel@etcheverry.biz>
 */
class OptionsController
{
    /**
     * @var array
     */
    private $methods;

    /**
     *
     * @param array $methods
     */
    public function __construct(array $methods)
    {
        $this->methods = $methods;
    }

    /**
     * @return Response
     */
    public function __invoke()
    {
        return Response::create('', 204, [
            'Allow' => implode(',', $this->methods)
        ]);
    }
}
