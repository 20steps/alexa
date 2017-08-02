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
				if ($intentRequest->locale=='de_DE') {
					$responseText='Bitte registriere Dich bei alexa.20steps.de und verknüpfe in das Konto in der Allexa App auf Deinem Smartphone.';
					$cardTitle = 'Account verknüpfen';
				} else {
					$responseText='Please register at alexa.20steps.de and connect the account in the Alexa App on your Smartphone.';
					$cardTitle = 'Connect account';
				}
				return $response
					->respond($responseText)
					->withCard($cardTitle,$responseText)
					->endSession();
			}
			
			if (!$user->hasSetting('uptime_robot_api_key') || $user->getSetting('uptime_robot_api_key')=='') {
				if ($intentRequest->locale=='de_DE') {
					$responseText='Bitte melde Dich bei alexa.20steps.de an und speichere Deinen UptimeRobot API Schlüssel';
					$cardTitle = 'UptimeRobot API Schlüssel';
				} else {
					$responseText='Please login at alexa.20steps.de and enter your API key at UptimeRobot.';
					$cardTitle = 'UptimeRobot API Key';
				}
				return $response
					->respond($responseText)
					->withCard($cardTitle,$responseText)
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
					if ($intentRequest->locale=='de_DE') {
						$responseText = $statistics['count'].' Monitore wurden geprüft: ';
					} else {
						$responseText = $statistics['count'].' have been checked: ';
					}
					
					if (($statistics['seems_down']['count'] + $statistics['down']['count']) == 0) {
						if ($intentRequest->locale=='de_DE') {
							$responseText.= 'Alle Systeme sind up. Trink einen Kaffee! ';
						} else {
							$responseText.= 'All systems up and running. Drink a coffee!! ';
						}
					} else {
						if ($statistics['down']['count'] > 0) {
							if ($intentRequest->locale=='de_DE') {
								$responseText.= 'Oh weh! '.$statistics['down']['count'].' Monitore sind down! Bitte prüfe das sofort oder hol Dir einen Schnaps! ';
							} else {
								$responseText.= 'Oh no! '.$statistics['down']['count'].' monitors are down! Please check your systems immediately or drink a whiskey! ';
							}
						}
						if ($statistics['seems_down']['count'] > 0) {
							if ($intentRequest->locale=='de_DE') {
								$responseText.= 'Hm, '.$statistics['seems_down']['count'].' Monitore sind möglicherweise down! Bitte prüfe das bei Gelegenheit und vergiss nicht genug Wasser zu trinken.';
							} else {
								$responseText.= 'Hm, '.$statistics['seems_down']['count'].' monitors are possibly down! Please check this at your pleasure and don\'t forget to drink enough water.';
							}

						}
					}
					
					if ($statistics['paused']['count'] + $statistics['not_checked_yet']['count']  + $statistics['unknown']['count'] > 0) {
						if ($intentRequest->locale=='de_DE') {
							$responseText .= 'Ach und übrigens, ein Hinweis noch: ';
						} else {
							$responseText .= 'Oh and by the way, one additional hint: ';
						}
						if ($statistics['paused']['count'] > 0) {
							if ($intentRequest->locale=='de_DE') {
								$responseText .= $statistics['paused']['count'] . ' Monitore sind pausiert.';
							} else {
								$responseText .= $statistics['paused']['count'] . ' monitors are paused.';
							}
						}
						if ($statistics['not_checked_yet']['count'] > 0) {
							if ($intentRequest->locale=='de_DE') {
								$responseText .= $statistics['paused']['count'] . ' Monitore wurden noch nicht geprüft.';
							} else {
								$responseText .= $statistics['paused']['count'] . ' monitors have not been checked yet.';
							}
						}
						if ($statistics['unknown']['count'] > 0) {
							if ($intentRequest->locale=='de_DE') {
								$responseText .= 'Bei ' . $statistics['paused']['count'] . ' Monitoren bin ich mir nicht sicher..';
							} else {
								$responseText .= 'With ' . $statistics['paused']['count'] . ' monitors i am not quite sure ..';
							}
						}
					}
					
					$this->logger->debug('success',['statistics' => $statistics,'responseText' => $responseText]);

					return $response->respond($responseText)->withCard('UptimeRobot Check',$responseText)->endSession();
				}
				
				$error = $monitorsResponse->getError();
				
				if ($intentRequest->locale=='de_DE') {
					$responseText = 'Leider konnte ich den Status nicht ermitteln - bitte prüfe Deinen UptimeRobot API Schlüssel : '.$error->getMessage();
					$responseText .= ' Melde Dich bei alexa.20steps.de an um Deinen API Schlüssel zu konfigurieren.';
					$cardTitle = 'UptimeRobot Prüfung';
				} else {
					$responseText = 'I could not get the status via UptimeRobot. Please check that you configured the correct API key for UptimeRobot: '.($reasonPhrase?$reasonPhrase:$monitorsResponse->getStatusCode());
					$responseText .= ' Login at alexa.20steps.de to configure the kay.';
					$cardTitle = 'UptimeRobot Check';
				}
				
				$this->logger->error('error',['error' => ['type' => $error->getType(), 'message' => $error->getMessage()],'responseText' => $responseText]);

				return $response
					->respond($responseText)
					->withCard($cardTitle,$responseText)
					->endSession();
			}
			
			/**
			 * @var $monitorsResponse ResponseInterface
			 */
			$reasonPhrase = $monitorsResponse->getReasonPhrase();

			if ($intentRequest->locale=='de_DE') {
				$responseText = 'Leider ist ein technischer Fehler aufgetreten - bitte prüfe Deinen UptimeRobot API Schlüssel: '.($reasonPhrase?$reasonPhrase:$monitorsResponse->getStatusCode());
				$responseText .= ' Melde Dich bei alexa.20steps.de an um Deinen API Schlüssel zu konfigurieren.';
				$cardTitle = 'UptimeRobot Prüfung';
			} else {
				$responseText = 'There is a technical problem. Please check that you configured the correct API key for UptimeRobot: '.($reasonPhrase?$reasonPhrase:$monitorsResponse->getStatusCode());
				$responseText .= ' Login at alexa.20steps.de to configure the kay.';
				$cardTitle = 'UptimeRobot Check';
			}

			$this->logger->error('error_technical',['error' => ['reasonPhrase' => $reasonPhrase, 'statusCode' => $monitorsResponse->getStatusCode()],'responseText' => $responseText]);

			return $response->respond($responseText)->withCard($cardTitle,$responseText)->endSession();
		}
		
	}
	
	