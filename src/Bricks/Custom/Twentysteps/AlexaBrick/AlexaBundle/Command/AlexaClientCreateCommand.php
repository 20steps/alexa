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
	class AlexaClientCreateCommand extends AbstractCommand {
		
		/**
		 * @see Command
		 */
		protected function configure() {
			$commandName = $this->getCommandPrefix().'alexa:client:create';
			$this
				->setHelp('By calling <info>'.$commandName.'</info> you can create the alexa oauthv2 client')
				->setDescription('Create client. Returns the public client id to be entered in Amazon Alexa configuration as Client ID.')->setName($commandName);
			parent::configure();
		}
		
		
		/**
		 * @see AbstractCommand
		 */
		protected function executeCommand() {
			var_dump($this->getAlexaModule()->createClient()->getPublicId());
		}
	}
