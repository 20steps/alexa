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
			return $this->successAlexa(
				$this->alexaModule->processAlexaRequest($this->getAlexaRequest($request)),$this->getUser()
			);
		}
		return $this->success(['ping' => 'pong']); // for monitoring
	}
	
}