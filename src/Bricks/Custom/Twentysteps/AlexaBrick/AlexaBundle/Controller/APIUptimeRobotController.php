<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations as BS;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\UptimeRobotModule;

class APIUptimeRobotController extends AbstractAPIController {

	/**
	 * @var UptimeRobotModule $uptimeRobotModule;
	 * @BS\Inject()
	 */
	private $uptimeRobotModule;

	public function pingAction(Request $request) {
		
		// let the module do the ping/pong
		return $this->success($this->uptimeRobotModule->ping());
	}
	
}