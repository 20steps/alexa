<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Controller\Annotations\View;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Controller\AbstractBricksController;

class SignInController extends AbstractBricksController {
	
	/**
	 * @param Request $request
	 * @return Response
	 * @View
	 */
	public function loginAction(Request $request) {
		$context = ['message' => 'Hallo Welt'];
		return $context;
	}
    
}