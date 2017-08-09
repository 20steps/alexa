<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use Alexa\Request\LaunchRequest;
	use Alexa\Request\SessionEndedRequest;
	use Doctrine\ORM\EntityManager;
	use Monolog\Logger;

	use twentysteps\Commons\EnsureBundle\Ensure;
	
	use Alexa\Request\Request as AlexaRequest;
	use Alexa\Response\Response as AlexaResponse;
	use Alexa\Request\IntentRequest;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\AccessToken;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\AccessTokenRepository;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\Client;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
	
	/**
	 * Module for processing alexa requests.
	 */
	class AlexaModule extends AbstractModule {
		
		/**
		 * @var EntityManager
		 */
		private $em;
		
		/**
		 * UptimeRobotModule constructor.
		 *
		 * @param Logger $logger
		 */
		public function __construct(EntityManager $em, Logger $logger) {
			parent::__construct($logger);
			$this->em = $em;
		}
		
		/**
		 * @param AlexaRequest $alexaRequest
		 * @return AlexaResponse
		 */
		public function processAlexaRequest(AlexaRequest $alexaRequest) {

			$user = $this->getUserFromRequest($alexaRequest);

			if ($alexaRequest instanceof IntentRequest) {
				/**
				 * @var IntentRequest $alexaRequest
				 */
				switch ($alexaRequest->intentName) {
					case 'AMAZON.HelpIntent':
						$response = new AlexaResponse();
						if ($alexaRequest->locale=='de-DE') {
							$responseText = 'Informiere Dich über den Status Deiner Webservices - sage dazu einfach:"Wie ist der Status? Oder imponiere Deiner Liebsten - sage einfach: "Frage nach Liebe.". Einstellungen kannst Du auf alexa.20steps.de vornehmen. Wie kann ich Dir nun helfen?';
							$cardTitle = 'Hilfe';
						} else {
							$responseText = 'You want to know the status of your webservices? Simply say: "How is the status? Or impress your loved one - simply say: "Ask for love." You can change settings at alexa.20steps.de. How can I help you now?';
							$cardTitle = 'Help';
						}
						return $response->respond($responseText)->withCard($cardTitle,$responseText);
					case 'AMAZON.StopIntent':
						$response = new AlexaResponse();
						return $response->endSession();
					case 'AMAZON.CancelIntent':
						$response = new AlexaResponse();
						return $response->endSession();
					case 'UptimeRobotStatusIntent':
						return $this->getShell()->getUptimeRobotModule()->processAlexaIntent($alexaRequest,$user);
					case 'SandraLoveIntent':
						$response = new AlexaResponse();
						if ($user && $user->hasSetting('love_name') && $user->getSetting('love_name')!='') {
							if ($alexaRequest->locale=='de-DE') {
								$responseText = sprintf('%s, %s liebt Dich!', $user->getSetting('love_name'), $user->getDisplayName());
								$cardTitle = 'Liebe';
							} else {
								$responseText = sprintf('%s, %s loves you!', $user->getSetting('love_name'), $user->getDisplayName());
								$cardTitle = 'Love';
							}
						} else {
							if ($alexaRequest->locale=='de-DE') {
								$responseText = 'Sandra, Helmut liebt Dich!';
								$cardTitle = 'Liebe';
							} else {
								$responseText = 'Sandra, Helmut loves you!';
								$cardTitle = 'Love';
							}
						}
						return $response->respond($responseText)->withCard($cardTitle,$responseText)->endSession();
					default:
						$response = new AlexaResponse();
						if ($alexaRequest->locale=='de-DE') {
							return $response->respond('Quatsch nicht!')->endSession();
						} else {
							return $response->respond('Don\'t talk rubbish!')->endSession();
						}
				}
			} else if ($alexaRequest instanceof LaunchRequest) {
				$response = new AlexaResponse();
				if ($alexaRequest->locale=='de-DE') {
					$responseText = 'Der twenty steps skill informiert Dich über den Systemstatus Deiner Webservices. Sage zum Beispiel: "Wie ist der Status?';
					$cardTitle = 'Willkommen';
				} else {
					$responseText = 'The twenty steps skill informs you about the system status of your webservices. For example say: "How is the status?';
					$cardTitle = 'Welcome';
				}
				return $response->respond($responseText)->withCard($cardTitle,$responseText);
			} else if ($alexaRequest instanceof SessionEndedRequest) {
				$response = new AlexaResponse();
				$response->endSession();
				return $response;
			}
			
			$response = new AlexaResponse();
			if ($alexaRequest->locale=='de_DE') {
				return $response->reprompt('Versuch\'s nochmal.')->endSession();
			} else {
				return $response->reprompt('Try again.')->endSession();
			}
		}
		
		public function processPush() {
			$count = 0;
			// push to Echo devices (will be implemented as soon as Alexa SDK is enhanced)
			return ['count' => $count];
		}
		
		/**
		 * @return Client
		 */
		public function createClient() {
			$clientManager = $this->getCore()->getContainer()->get('fos_oauth_server.client_manager.default');
			/**
			 * @var Client $client
			 */
			$client = $clientManager->createClient();
			$client->setRedirectUris([]); // ignored as alexa uses dynamic urls
			$client->setAllowedGrantTypes(['authorization_code']);
			$clientManager->updateClient($client);
			return $client;
		}
		
		// helpers
		
		/**
		 * @param AlexaRequest $alexaRequest
		 * @return null|User
		 */
		public function getUserFromRequest(AlexaRequest $alexaRequest) {
			Ensure::isNotNull($alexaRequest,'request must not be null');
			if ($alexaRequest->session && $alexaRequest->session->user && $alexaRequest->session->user->accessToken) {
				$accessToken = $alexaRequest->session->user->accessToken;
				$token = $this->getAccessTokenRepository()->findOneBy(['token' => $accessToken]);
				if ($token) {
					/**
					 * @var AccessToken $token
					 */
					return $token->getUser();
				}
			}
			return null;
		}
		
		/** @return AccessTokenRepository */
		protected function getAccessTokenRepository() {
			return $this->em->getRepository('BricksCustomTwentystepsAlexaBundle:AccessToken');
		}
		
		
		
	}
	
	