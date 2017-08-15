<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use APIAI\Request\Request as APIAIRequest;
use APIAI\Response\Response as APIAIResponse;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations as BS;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\APIAIModule;

class APIAPIAIController extends AbstractAPIController {

	/**
	 * @var APIAIModule $apiAIModule;
	 * @BS\Inject()
	 */
	private $apiAIModule;
	
	/**
	 * @param Request $request
	 * @return Response
	 */
	public function processAction(Request $request) {
		
		if ($request->getMethod()==Request::METHOD_POST) {
			// prepare/process request from Amazon Alexa
			return $this->successAPIAI($this->apiAIModule->processAPIAIRequest($this->getAPIAIRequest($request)));
		}
		
		// for monitoring / health-check,
		// cp. bricks_infrastructure_core_core_monitoring_monitor_definitions_custom
		return $this->success(['ping' => 'ok']);
	
	}
	
	/**
	 * @param Request $request
	 * @param string|null $appId
	 * @return APIAIRequest
	 */
	protected  function getAPIAIRequest(Request $request) {
		$content = $request->getContent();
		$this->getLogger()->debug('apiai request',json_decode($content,true));
		$alexaRequest = new APIAIRequest($content);
		return $alexaRequest->fromData();
	}
	
	/**
	 * @param APIAIResponse $alexaResponse
	 * @return Response
	 */
	protected function successAPIAI(APIAIResponse $apiaiResponse) {
		$renderedResponse = $apiaiResponse->render();
		$this->getLogger()->debug('apiai response',$renderedResponse);
		$response = new Response($this->serializeJSON($renderedResponse));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	/**
	 * @return LoggerInterface
	 */
	protected function getLogger() {
		return $this->get('monolog.logger.bricks.custom.twentysteps_alexa.controller.api_ai');
	}
}