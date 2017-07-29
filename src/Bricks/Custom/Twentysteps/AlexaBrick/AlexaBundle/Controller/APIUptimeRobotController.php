<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations as BS;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\UptimeRobotModule;
use Symfony\Component\HttpFoundation\Response;

class APIUptimeRobotController extends AbstractAPIController {

	/**
	 * @var UptimeRobotModule $uptimeRobotModule;
	 * @BS\Inject()
	 */
	private $uptimeRobotModule;

	public function pingAction(Request $request) {
		$response = new Response($this->serializeJSON($this->uptimeRobotModule->ping()));
		$response->headers->set('Content-Type', 'application/json');
		return $response;
	}
	
}