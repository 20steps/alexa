<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
	use Monolog\Logger;
	
	use Psr\Http\Message\ResponseInterface;
	
	use Alexa\Request\IntentRequest;
	use Alexa\Response\Response as AlexaResponse;
	
	use twentysteps\Commons\EnsureBundle\Ensure;
	use twentysteps\Commons\UptimeRobotBundle\UptimeRobotAPI;
	use twentysteps\Commons\UptimeRobotBundle\Model\GetMonitorsResponse;
	
	/**
	 * Module for uptime robot skill.
	 */
	class UptimeRobotModule extends AbstractModule {
		
		/**
		 * @var UptimeRobotAPI
		 */
		private $uptimeRobotAPI;
		
		/**
		 * UptimeRobotModule constructor.
		 *
		 * @param Logger $logger
		 */
		public function __construct(UptimeRobotAPI $uptimeRobotAPI, Logger $logger) {
			parent::__construct($logger);
			$this->uptimeRobotAPI = $uptimeRobotAPI;
		}
		
		/**
		 * @param IntentRequest $intentRequest
		 * @return AlexaResponse
		 */
		public function processAlexaIntent(IntentRequest $intentRequest, User $user) {
			Ensure::isTrue($intentRequest->intentName == 'UptimeRobotStatusIntent',
				sprintf('Wrong intent [%s]',$intentRequest->intentName));

			$response = new AlexaResponse();

			if (!$user) {
				$responseText='Bitte registriere Dich bei alexa.20steps.de und verknüpfe in der Alexa App Deinen Account.';
				return $response
					->respond($responseText)
					->withCard('Account verknüpfen',$responseText)
					->endSession();
			}
			
			if (!$user->hasSetting('uptime_robot_api_key') || $user->getSetting('uptime_robot_api_key')=='') {
				$responseText='Bitte melde Dich bei alexa.20steps.de an und speichere Deinen UptimeRobot API Key';
				return $response
					->respond($responseText)
					->withCard('UptimeRobot API Key',$responseText)
					->endSession();
				
			}

			$this->uptimeRobotAPI->setApiKey($user->getSetting('uptime_robot_api_key'));
			
			// get info about all monitors connected to configured account
			$monitorsResponse = $this->uptimeRobotAPI->monitor()->all();
			if ($monitorsResponse instanceof GetMonitorsResponse) {
				/**
				 * @var GetMonitorsResponse $monitorsResponse
				 */
				if ($monitorsResponse->getStat()=='ok') {
					// gather statistics
					$statistics = [
						'paused' => [
							'count' => 0
						],
						'not_checked_yet' => [
							'count' => 0
						],
						'up' => [
							'count' => 0
						],
						'seems_down' => [
							'count' => 0
						],
						'down' => [
							'count' => 0
						],
						'unknown' => [
							'count' => 0
						],
						'count' => 0
					];
					
					$statusIdToName = [
						0 => 'paused',
						1 => 'not_checked_yet',
						2 => 'up',
						8 => 'seems_down',
						9 => 'down'
					];
					
					foreach ($monitorsResponse->getMonitors() as $monitor) {
						$status = $monitor->getStatus();
						if (array_key_exists($status,$statusIdToName)) {
							$statistics[$statusIdToName[$status]]['count']++;
						} else {
							$statistics['unknown']['count']++;
						}
						$statistics['count']++;
					}
					
					
					// prepare response text
					$responseText = $statistics['count'].' Monitore wurden geprüft: ';
					
					if (($statistics['seems_down']['count'] + $statistics['down']['count']) == 0) {
						$responseText.= 'Alle Systeme sind up. Trink einen Kaffee! ';
					} else {
						if ($statistics['down']['count'] > 0) {
							$responseText.= 'Oh weh! '.$statistics['down']['count'].' Monitore sind down! Bitte prüfe das sofort oder hol Dir einen Schnaps! ';
						}
						if ($statistics['seems_down']['count'] > 0) {
							$responseText.= 'Hm, '.$statistics['seems_down']['count'].' Monitore sind möglicherweise down! Bitte prüfe das bei Gelegenheit, trinke aber vorher ein Wasser ';
						}
					}
					
					if ($statistics['paused']['count'] + $statistics['not_checked_yet']['count']  + $statistics['unknown']['count'] > 0) {
						$responseText .= 'Ach und übrigens, ein Hinweis noch: ';
						if ($statistics['paused']['count'] > 0) {
							$responseText .= $statistics['paused']['count'] . ' Monitore sind pausiert.';
						}
						if ($statistics['not_checked_yet']['count'] > 0) {
							$responseText .= $statistics['paused']['count'] . ' Monitore wurden noch nicht geprüft.';
						}
						if ($statistics['unknown']['count'] > 0) {
							$responseText .= 'Bei ' . $statistics['paused']['count'] . ' Monitoren bin ich mir nicht sicher..';
						}
					}
					
					if ($user) {
						$responseText = 'Hallo '.$user->getDisplayName().'. '.$responseText;
					}
					$this->logger->debug('success',['statistics' => $statistics,'responseText' => $responseText]);

					return $response->respond($responseText)->withCard('UptimeRobot Check',$responseText)->endSession();
				}
				
				$error = $monitorsResponse->getError();
				$responseText = 'Leider konnte ich den Status nicht ermitteln - bitte prüfe Deinen UptimeRobot API Key : '.$error->getMessage();
				$responseText .= ' Melde Dich bei alexa.20steps.de an um Deinen API Key zu konfigurieren.';

				$this->logger->error('error',['error' => ['type' => $error->getType(), 'message' => $error->getMessage()],'responseText' => $responseText]);

				return $response
					->respond($responseText)
					->withCard('UptimeRobot Check',$responseText)
					->endSession();
			}
			
			/**
			 * @var $monitorsResponse ResponseInterface
			 */
			$reasonPhrase = $monitorsResponse->getReasonPhrase();
			$responseText = 'Leider ist ein technischer Fehler aufgetreten - bitte prüfe Deinen UptimeRobot API Key: '.($reasonPhrase?$reasonPhrase:$monitorsResponse->getStatusCode());
			$responseText .= ' Melde Dich bei alexa.20steps.de an um Deinen API Key zu konfigurieren.';

			$this->logger->error('error_technical',['error' => ['reasonPhrase' => $reasonPhrase, 'statusCode' => $monitorsResponse->getStatusCode()],'responseText' => $responseText]);

			return $response->respond($responseText)->withCard('UptimeRobot Check',$responseText)->endSession();
		}
		
	}
	
	