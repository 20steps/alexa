<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use Monolog\Logger;

	use twentysteps\Commons\EnsureBundle\Ensure;
	
	/**
	 * Module for uptime robot skill.
	 */
	class UptimeRobotModule extends AbstractModule {
		
		/**
		 * UptimeRobotModule constructor.
		 *
		 * @param Logger $logger
		 */
		public function __construct(Logger $logger) {
			parent::__construct($logger);
		}
		
		/**
		 * @return array
		 */
		public function ping() {
			return ['ping' => 'pong'];
		}
		
		public function process() {
			return ['hello' => 'world'];
		}
		
	}
	
	