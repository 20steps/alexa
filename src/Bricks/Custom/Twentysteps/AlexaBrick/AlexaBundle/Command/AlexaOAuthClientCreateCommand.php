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
	class AlexaOAuthClientCreateCommand extends AbstractCommand {
		
		/**
		 * @see Command
		 */
		protected function configure() {
			$commandName = $this->getCommandPrefix().'alexa:oauth:client:create';
			$this
				->setHelp('By calling <info>'.$commandName.'</info> you can create the alexa oauthv2 clients for Amazon and Google account linking')
				->setDescription('Create clients. Returns the public client ids to be entered in Amazon Alexa Skill and Google Actions console.')->setName($commandName);
			parent::configure();
		}
		
		
		/**
		 * @see AbstractCommand
		 */
		protected function executeCommand() {
			var_dump($this->getAlexaModule()->createOAuthClient()->getPublicId());
		}
	}
