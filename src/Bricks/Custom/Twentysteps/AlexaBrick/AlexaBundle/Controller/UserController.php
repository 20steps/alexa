<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;

use FOS\RestBundle\Controller\Annotations\View;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Controller\AbstractBricksController;


class UserController extends AbstractBricksController {
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function loginAction(Request $request) {
		$session = $request->getSession();
		
		// get the login error if there is one
		if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
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
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function loginAlexaAction(Request $request) {
		$session = $request->getSession();
		$session->set('alexa_state',$request->query->get('state',rand(0,99)));
		$session->set('alexa_client_id',$request->query->get('client_id',$this->getParameter('bricks_custom_twentysteps_alexa_alexa_oauth2_client_id')));
		$session->set('alexa_response_type',$request->query->get('response_type','code'));
		$session->set('alexa_scope',$request->query->get('scope','_TWENTYSTEPS_ALEXA_USER'));
		$session->set('alexa_redirect_uri',$request->query->get('redirect_uri','https://20steps.de'));
		
		// get the login error if there is one
		if ($request->attributes->has(Security::AUTHENTICATION_ERROR)) {
			$error = $request->attributes->get(Security::AUTHENTICATION_ERROR);
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
	 * @param Request $request
	 * @return RedirectResponse
	 */
	public function preAuthorizeAlexaAction(Request $request) {
		$session = $request->getSession();
		return $this->redirectToRoute('fos_oauth_server_authorize',[
			'state' => $session->get('alexa_state','na'),
			'client_id' => $session->get('alexa_client_id','na'),
			'response_type' => $session->get('alexa_response_type','na'),
			'scope' => $session->get('alexa_scope','na'),
			'redirect_uri' => $session->get('alexa_redirect_uri','na')
		]);
		
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @return array
	 */
	public function homeAlexaAction(Request $request) {
		$context = ['message' => 'Hallo Alexa'];
		return $context;
	}
	
}