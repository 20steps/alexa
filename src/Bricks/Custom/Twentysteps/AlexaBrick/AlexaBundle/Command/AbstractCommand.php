<?php


namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Command;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Command\AbstractProjectCommand;
use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations\Command;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\AlexaModule;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\UptimeRobotModule;

/**
 *
 * @Command(
 *     defaultProjectCode="alexa"
 * )
 *
 */
abstract class AbstractCommand extends AbstractProjectCommand {

	protected function getCommandPrefix() {
		return 'bricks:custom:twentysteps:alexa:';
	}
	
	/**
	 * @return AlexaModule
	 */
	protected final function getAlexaModule() {
		return $this->container->get('twentysteps_alexa')->getAlexaModule();
	}
	
	/**
	 * @return UptimeRobotModule
	 */
	protected final function getUptimeRobotModule() {
		return $this->container->get('twentysteps_alexa')->getUptimeRobotModule();
	}
	
}
