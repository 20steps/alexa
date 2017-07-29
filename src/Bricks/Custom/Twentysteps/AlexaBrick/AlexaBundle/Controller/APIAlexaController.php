<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations as BS;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\AlexaModule;

class APIAlexaIntentController extends AbstractAlexaAPIController {

	/**
	 * @var AlexaModule $alexaModule;
	 * @BS\Inject()
	 */
	private $alexaModule;
	
	/**
	 * @param Request $request
	 * @return Response
	 */
	public function pingAction(Request $request) {
		return $this->successAlexa($this->alexaModule->processAlexaRequest($this->getAlexaRequest($request)));
	}
	
}