<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\AccessToken;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\AccessTokenRepository;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\Client;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
	use Doctrine\ORM\EntityManager;
	use Monolog\Logger;

	use twentysteps\Commons\EnsureBundle\Ensure;
	
	use Alexa\Request\Request as AlexaRequest;
	use Alexa\Response\Response as AlexaResponse;
	use Alexa\Request\IntentRequest;
	
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
						$responseText = 'Sandra, Helmut liebt Dich!';
						if ($user) {
							$responseText.=' Ach übrigens: der aktuelle Nutzer ist '.$user->getUsername();
						}
						return $response->respond($responseText)->withCard('Für Sandra',$responseText)->endSession();
					case 'UptimeRobotStatusIntent':
						return $this->getShell()->getUptimeRobotModule()->processAlexaIntent($alexaRequest,$user);
					default:
						$response = new AlexaResponse();
						return $response->respond('Quatsch nicht!')->endSession();
				}
			}
			
			$response = new AlexaResponse();
			return $response->reprompt('Versuch\'s nochmal.')->endSession();
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
			$client->setRedirectUris(array('https://www.amazon.de'));
			$client->setAllowedGrantTypes(array('authorization_code'));
			$clientManager->updateClient($client);
			return $client;
		}
		
		// helpers
		
		/**
		 * @param AlexaRequest $alexaRequest
		 * @return null|User
		 */
		public function getUserFromRequest(AlexaRequest $alexaRequest) {
			$user = $alexaRequest->session->user;
			if ($user && $user->accessToken) {
				$accessToken = $user->accessToken;
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
	
	