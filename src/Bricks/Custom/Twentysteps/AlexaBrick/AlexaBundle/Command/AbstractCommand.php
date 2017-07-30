<?php


namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Command;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Command\AbstractProjectCommand;
use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations\Command;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\AlexaModule;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\UptimeRobotModule;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\UserModule;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\Mailmodule;

/**
 *
 * @Command(
 *     defaultProjectCode="alexa"
 * )
 *
 */
abstract class AbstractCommand extends AbstractProjectCommand {

	protected function getCommandPrefix() {
		return 'bricks:custom:alexa:';
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
	
	/**
	 * @return UserModule
	 */
	protected final function getUserModule() {
		return $this->container->get('twentysteps_alexa')->getUserModule();
	}
	
	/**
	 * @return Mailmodule
	 */
	protected final function getMailModule() {
		return $this->container->get('twentysteps_alexa')->getMailModule();
	}
	
}
