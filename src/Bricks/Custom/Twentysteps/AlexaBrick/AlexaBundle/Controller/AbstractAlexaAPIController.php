<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Alexa\Request\Request as AlexaRequest;
use Alexa\Response\Response as AlexaResponse;

abstract class AbstractAlexaAPIController extends AbstractAPIController {
	
	/**
	 * @param Request $request
	 * @param string|null $appId
	 * @return AlexaRequest
	 */
    protected  function getAlexaRequest(Request $request, string $appId = null) {
    	if (!$appId) {
    		$appId = $this->getParameter('bricks_custom_twentysteps_alexa_application_id');
	    }
		$alexaRequest = new AlexaRequest($request->getContent(), $appId);
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
    
}