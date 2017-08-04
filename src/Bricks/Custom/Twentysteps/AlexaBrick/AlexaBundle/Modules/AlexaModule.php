<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use Alexa\Request\LaunchRequest;
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
					case 'UptimeRobotStatusIntent':
						return $this->getShell()->getUptimeRobotModule()->processAlexaIntent($alexaRequest,$user);
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
					$responseText = 'Dieser Skill informiert Dich über den Systemstatus Deiner Webservices. Frage einfach Alexa, frage 20steps nach wie ist der Status.';
					$cardTitle = 'Willkommen';
				} else {
					$responseText = 'This skill informs you about the system status of your webservices. Simply ask: Alexa, ask 20steps to tell about the status.';
					$cardTitle = 'Welcome';
				}
				return $response->respond($responseText)->withCard($cardTitle,$responseText)->endSession();
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
	
	