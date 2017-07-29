<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Command;
	
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations\Command;
	
	/**
	 *
	 * @Command(
	 *     defaultProjectCode="alexa",
	 *     defaultBundleAlias="twentysteps_alexa"
	 * )
	 *
	 */
	class UptimeRobotPingCommand extends AbstractCommand {
		
		/**
		 * @see Command
		 */
		protected function configure() {
			$commandName = $this->getCommandPrefix().'uptime-robot:ping';
			$this
				->setHelp('By calling <info>'.$commandName.'</info> you can ping')
				->setDescription('Ping.')->setName($commandName);
			parent::configure();
		}
		
		
		/**
		 * @see AbstractCommand
		 */
		protected function executeCommand() {
			var_dump($this->getUptimeRobotModule()->ping());
		}
	}
