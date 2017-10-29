<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Command;
	
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations\Command;
		use Symfony\Component\Console\Input\InputInterface;
		use Symfony\Component\Console\Output\OutputInterface;
		
		/**
	 *
	 * @Command(
	 *     defaultProjectCode="alexa",
	 *     defaultBundleAlias="twentysteps_alexa"
	 * )
	 *
	 */
	class AlexaDebugCommand extends AbstractCommand {
		
		/**
		 * @see Command
		 */
		protected function configure() {
			$commandName = $this->getCommandPrefix().'alexa:debug';
			$this
				->setHelp('By calling <info>'.$commandName.'</info> you can debug stuff')
				->setDescription('Debug stuff.')->setName($commandName);
			parent::configure();
		}
		
		/**
		 * @see AbstractCommand
		 */
		protected function executeCommand() {
			throw new \Exception("testing sentry");
		}
	}
