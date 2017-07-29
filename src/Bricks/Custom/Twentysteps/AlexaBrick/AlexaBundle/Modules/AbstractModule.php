<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Base\AbstractBrickShellModule;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;
	
	abstract class AbstractModule extends AbstractBrickShellModule {
		
		
		/**
		 * @return User
		 */
		public function getCurrentUser() {
			return parent::getCurrentUser();
		}
		
		/**
		 * @return User|null
		 */
		public function getCurrentUserOrNull() {
			return parent::getCurrentUserOrNull();
		}
		
		/**
		 * @return AlexaShell
		 */
		public function getShell() {
			return parent::getShell();
		}
		
	}
