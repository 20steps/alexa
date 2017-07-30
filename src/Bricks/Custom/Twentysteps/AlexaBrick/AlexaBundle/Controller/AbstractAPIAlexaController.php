<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Alexa\Request\Request as AlexaRequest;
use Alexa\Response\Response as AlexaResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

abstract class AbstractAPIAlexaController extends AbstractAPIController {
	
	/**
	 * @param Request $request
	 * @param string|null $appId
	 * @return AlexaRequest
	 */
    protected  function getAlexaRequest(Request $request, string $appId = null) {
    	if (!$appId) {
    		$appId = $this->getParameter('bricks_custom_twentysteps_alexa_application_id');
	    }
	    $content = $request->getContent();
	    $this->logger->error('alexa request',json_decode($content,true));
		$alexaRequest = new AlexaRequest($content, $appId);
		return $alexaRequest->fromData();
    }
	
	/**
	 * @param AlexaResponse $alexaResponse
	 * @return Response
	 */
    protected function successAlexa(AlexaResponse $alexaResponse) {
	    $response = new Response($this->serializeJSON($alexaResponse->render()));
	    $response->headers->set('Content-Type', 'application/json');
	    return $response;
    }
	
	/**
	 * @return null|User
	 */
    protected function getUser() {
	    $token = $this->getTokenStorage()->getToken();
	    if ($token) {
		    $tokenUser = $token->getUser();
		    $user = $this->em->find(get_class($tokenUser),$tokenUser->getId());
	    } else {
	    	$user = null;
	    }
	    /**
	     * @var User $user
	     */
	    $this->logger->error('alexa user',['user' => $user?$user->getUsername():'no user']);
	    return null;
    }
	/**
	 * @return TokenStorage
	 */
	protected function getTokenStorage() {
		return $this->get('security.token_storage');
	}
    
}