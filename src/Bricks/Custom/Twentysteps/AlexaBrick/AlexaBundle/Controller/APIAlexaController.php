<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Alexa\Request\Request as AlexaRequest;
use Alexa\Response\Response as AlexaResponse;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations as BS;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\AlexaModule;

class APIAlexaController extends AbstractAPIController {

	/**
	 * @var AlexaModule $alexaModule;
	 * @BS\Inject()
	 */
	private $alexaModule;
	
	/**
	 * @param Request $request
	 * @return Response
	 */
	public function processAction(Request $request) {
		
		if ($request->getMethod()==Request::METHOD_POST) {
			// prepare/process request from Amazon Alexa
			return $this->successAlexa(
				$this->alexaModule->processAlexaRequest($this->getAlexaRequest($request))
			);
		}
		
		// for monitoring / health-check,
		// cp. bricks_infrastructure_core_core_monitoring_monitor_definitions_custom
		return $this->success(['ping' => 'ok']);
	
	}
	
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
		$this->getAlexaLogger()->debug('alexa request',json_decode($content,true));
		$alexaRequest = new AlexaRequest($content, $appId);
		return $alexaRequest->fromData();
	}
	
	/**
	 * @param AlexaResponse $alexaResponse
	 * @return Response
	 */
	protected function successAlexa(AlexaResponse $alexaResponse) {
		$renderedResponse = $alexaResponse->render();
		$this->getAlexaLogger()->debug('response',$alexaResponse->render());
		$response = new Response($this->serializeJSON($renderedResponse));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
	/**
	 * @return LoggerInterface
	 */
	protected function getAlexaLogger() {
		return $this->get('monolog.logger.alexa');
	}
}