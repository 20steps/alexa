<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;

use FOS\RestBundle\Controller\Annotations\View;


class UserController extends AbstractAuthenticatedController {
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function loginAction(Request $request) {
		$session = $request->getSession();
		
		// get the login error if there is one
		if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(
				Security::AUTHENTICATION_ERROR
			);
		} else {
			$error = $session->get(Security::AUTHENTICATION_ERROR);
			$session->remove(Security::AUTHENTICATION_ERROR);
		}
		
		return array(
			// last username entered by the user
			'last_username' => $session->get(Security::LAST_USERNAME),
			'error'         => $error,
			'body_class' => 'login'
		);
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function homeAction(Request $request) {
		$context = ['message' => 'Hallo Welt'];
		return $context;
	}
    
}