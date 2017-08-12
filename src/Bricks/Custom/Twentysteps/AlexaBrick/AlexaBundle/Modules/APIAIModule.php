<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules;
	
	use APIAI\Request\GoogleRequest;
	use Doctrine\ORM\EntityManager;
	use Monolog\Logger;

	use twentysteps\Commons\EnsureBundle\Ensure;
	
	use APIAI\Request\Request as APIAIRequest;
	use APIAI\Response\Response as APIAIResponse;
	use APIAI\Request\IntentRequest;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\AccessToken;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\AccessTokenRepository;
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
	
	/**
	 * Module for processing alexa requests.
	 */
	class APIAIModule extends AbstractModule {
		
		/**
		 * @var EntityManager
		 */
		private $em;
		
		/**
		 * APIAIModule constructor.
		 *
		 * @param EntityManager $em
		 * @param Logger $logger
		 */
		public function __construct(EntityManager $em, Logger $logger) {
			parent::__construct($logger);
			$this->em = $em;
		}
		
		/**
		 * @param APIAIRequest $apiaiRequest
		 * @return APIAIResponse
		 */
		public function processAPIAIRequest(APIAIRequest $apiaiRequest) {

			$user = $this->getUserFromRequest($apiaiRequest);
			$userAgent = '20steps/assistant';
			
			if ($apiaiRequest instanceof IntentRequest) {
				/**
				 * @var IntentRequest $apiaiRequest
				 */
				switch ($apiaiRequest->getIntentName()) {
					case 'SandraLoveIntent':
						$response = new APIAIResponse($userAgent);
						if ($user && $user->hasSetting('love_name') && $user->getSetting('love_name')!='') {
							if ($apiaiRequest->getLang()=='de') {
								$speech = sprintf('%s, %s liebt Dich!', $user->getSetting('love_name'), $user->getDisplayName());
								$displayText = 'Liebe';
							} else {
								$speech = sprintf('%s, %s loves you!', $user->getSetting('love_name'), $user->getDisplayName());
								$displayText = 'Love';
							}
						} else {
							if ($apiaiRequest->getLang()=='de') {
								$speech = 'Sandra, Helmut liebt Dich!';
								$displayText = 'Liebe';
							} else {
								$speech = 'Sandra, Helmut loves you!';
								$displayText = 'Love';
							}
						}
						return $response->respond($speech)->withDisplayText($displayText);
					case 'UptimeRobotStatusIntent':
						return $this->getShell()->getUptimeRobotModule()->processAPIAIIntent($apiaiRequest,$user);
					default:
						$response = new APIAIResponse($userAgent);
						if ($apiaiRequest->getLang()=='de') {
							return $response->respond('Entschuldigung, aber ich verstehe Dich leider nicht.');
						} else {
							return $response->respond('Sad to say but I don\'t understand you.');
						}
				}
			}
			
		}
		
		public function processPush() {
			$count = 0;
			// push to Echo devices (will be implemented as soon as Google Home SDK is enhanced)
			return ['count' => $count];
		}
		
		/**
		 * @param APIAIRequest $apiaiRequest
		 * @return null|User
		 */
		public function getUserFromRequest(APIAIRequest $apiaiRequest) {
			Ensure::isNotNull($apiaiRequest,'request must not be null');
			$originalRequest = $apiaiRequest->getOriginalRequest();
			if ($originalRequest && $originalRequest instanceof GoogleRequest) {
				/**
				 * @var GoogleRequest $originalRequest
				 */
				$user = $originalRequest->getUser();
				if ($user) {
					$accessToken = $user->getAccessToken();
					if ($accessToken) {
						$token = $this->getAccessTokenRepository()->findOneBy(['token' => $accessToken]);
						if ($token) {
							/**
							 * @var AccessToken $token
							 */
							return $token->getUser();
						}
					}
				}
			}
			return null;
		}
		
		/** @return AccessTokenRepository */
		protected function getAccessTokenRepository() {
			return $this->em->getRepository('BricksCustomTwentystepsAlexaBundle:AccessToken');
		}
		
		
	}
	
	