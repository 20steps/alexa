<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Controller\AbstractBricksController;

class SignInController extends AbstractBricksController {
	
	public function loginAction(Request $request) {
		$response = new Response();
		$response->setContent('<html><body>Hallo Welt</body></html>');
		$response->headers->set('Content-Type', 'text/html');
		return $response;
	}
    
}