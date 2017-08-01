<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations as BS;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\AlexaModule;

class APIAlexaController extends AbstractAPIAlexaController {

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
	
}