<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use Alexa\Request\IntentRequest;
	use Monolog\Logger;

	use twentysteps\Commons\EnsureBundle\Ensure;
	
	use Alexa\Request\Request as AlexaRequest;
	use Alexa\Response\Response as AlexaResponse;
	
	/**
	 * Module for processing alexa requests.
	 */
	class AlexaModule extends AbstractModule {
		
		/**
		 * UptimeRobotModule constructor.
		 *
		 * @param Logger $logger
		 */
		public function __construct(Logger $logger) {
			parent::__construct($logger);
		}
		
		/**
		 * @param AlexaRequest $alexaRequest
		 * @return AlexaResponse
		 */
		public function processAlexaRequest(AlexaRequest $alexaRequest) {
			$response = new \Alexa\Response\Response;

			if ($alexaRequest instanceof IntentRequest) {
				$responseText = null;
				/**
				 * @var IntentRequest $alexaRequest
				 */
				switch ($alexaRequest->intentName) {
					case 'SandraLoveIntent':
						$responseText = 'Sandra ich liebe Dich!';
						break;
					case 'UptimeRobotStatusIntent':
						$responseText = $this->getShell()->getUptimeRobotModule()->getStatusResponseText();
						break;
					default:
						$responseText = 'Quatsch nicht!';
				}
				$response->respond($responseText);
			} else {
				$response->reprompt('Sags nochmal');
			}
			
			return $response;
			
		}
		
	}
	
	