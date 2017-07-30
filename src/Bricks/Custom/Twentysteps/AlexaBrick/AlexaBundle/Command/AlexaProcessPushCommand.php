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
	class AlexaProcessPushCommand extends AbstractCommand {
		
		/**
		 * @see Command
		 */
		protected function configure() {
			$commandName = $this->getCommandPrefix().'alexa:process-push';
			$this
				->setHelp('By calling <info>'.$commandName.'</info> you can process push')
				->setDescription('Process push.')->setName($commandName);
			parent::configure();
		}
		
		
		/**
		 * @see AbstractCommand
		 */
		protected function executeCommand() {
			var_dump($this->getAlexaModule()->processPush());
		}
	}
