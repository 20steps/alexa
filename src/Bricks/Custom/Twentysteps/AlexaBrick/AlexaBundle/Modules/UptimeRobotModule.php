<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use Monolog\Logger;
	
	use Psr\Http\Message\ResponseInterface;
	use twentysteps\Commons\EnsureBundle\Ensure;
	use twentysteps\Commons\UptimeRobotBundle\Model\GetMonitorsResponse;
	use twentysteps\Commons\UptimeRobotBundle\UptimeRobotAPI;
	
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
		
		public function getStatusResponseText() {
			$monitorsResponse = $this->uptimeRobotAPI->monitor()->all();
			if ($monitorsResponse instanceof GetMonitorsResponse) {
				/**
				 * @var GetMonitorsResponse $monitorsResponse
				 */
				if ($monitorsResponse->getStat()=='ok') {
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
						]
					];
					$statusIdToName = [
						0 => 'paused',
						1 => 'not_checked_yet',
						2 => 'up',
						8 => 'seems_down',
						9 => 'down'
					];
					$count = 0;
					foreach ($monitorsResponse->getMonitors() as $monitor) {
						$status = $monitor->getStatus();
						if (array_key_exists($status,$statusIdToName)) {
							$statistics[$statusIdToName[$status]]['count']++;
						} else {
							$statistics['unknown'[$status]]['count']++;
						}
						$count++;
					}
					$responseText = $count.' Monitore wurden geprüft: ';
					
					if ($statistics['seems_down']['count'] + $statistics['down']['count'] == 0) {
						$responseText.= 'Alle Systeme up. Trink einen Kaffee! ';
					} else {
						if ($statistics['down']['count'] > 0) {
							$responseText.= 'Oh weh! '.$statistics['seems_down']['count'].' Monitore sind down! Bitte prüfe das sofort! ';
						}
						if ($statistics['seems_down']['count'] > 0) {
							$responseText.= 'Hm, '.$statistics['seems_down']['count'].' Monitore sind möglicherweise down! Bitte prüfe das bei Gelegenheit! ';
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
					return $responseText;
				}
				return 'Leider konnte ich den Status nicht ermitteln: '.$monitorsResponse->getError()->getMessage();
			}
			
			/**
			 * @var $monitorsResponse ResponseInterface
			 */
			$reasonPhrase = $monitorsResponse->getReasonPhrase();
			return 'Leider konnte ich den Status nicht ermitteln: '.$reasonPhrase?$reasonPhrase:$monitorsResponse->getStatusCode();
		}
		
		public function processJob() {
			// no background job processing needed yet
			return ['hello' => 'world'];
		}
		
	}
	
	