<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\OAuth;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
	use Symfony\Component\DependencyInjection\Container;
	use Monolog\Logger;
	
	use OAuth2\Model\IOAuth2Client;
	use OAuth2\OAuth2ServerException;
	use OAuth2\OAuth2;

	use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
	use FOS\UserBundle\Doctrine\UserManager;
	
	use twentysteps\Commons\EnsureBundle\Ensure;
	
	/**
	 * Class JWTBearerExtension
	 * Cp. https://developers.google.com/actions/identity/oauth2-assertion-flow
	 */
	class JWTBearerExtension implements GrantExtensionInterface {
		
		/**
		 * @var Container
		 */
		private $sc;
		
		/**
		 * @var Logger
		 */
		private $logger;
		
		/**
		 * @param Container $userRepository
		 */
		public function __construct(Container $sc, Logger $logger)
		{
			$this->sc = $sc;
			$this->logger = $logger;
		}
		
		/*
		 * {@inheritdoc}
		 */
		public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders)
		{
			$intent = Ensure::isNotNull(array_key_exists('intent',$inputData)?$inputData['intent']:null,'no intent set');
			$jwtString = Ensure::isNotNull(array_key_exists('assertion',$inputData)?$inputData['assertion']:null,'no assertion set');
			
			$this->logger->debug(var_export($inputData,true));

			$jwt = \JOSE_JWT::decode($jwtString);
			
			$this->logger->debug(var_export($jwt,true));
			
			// alg: RS256, kid: ca04df587b5a7cead80abee9ea8dcf7586a78e01
			
			$userGoogleId = $jwt->claims['sub'];
			$userEmail = $jwt->claims['email'];
			$userEmailVerified = $jwt->claims['email_verified'];
			$userFirstName = $jwt->claims['given_name'];
			$userLastName = $jwt->claims['family_name'];
			$userLocale = $jwt->claims['locale'];
			
			
			$userManager = $this->getUserManager();
			switch($intent) {
				case 'get':
					/**
					 * @var User $user
					 */
					$user = $userManager->findUserBy(array('googleId' => $userGoogleId));
					if (!$user) {
						$user = $userManager->findUserByEmail($userEmail);
					}
					if (!$user) {
						$this->logger->debug('get intent: user not found');
						throw new OAuth2ServerException(OAuth2::HTTP_UNAUTHORIZED, 'user_not_found', "User was not found");
					}

					// possibly set user's Google Id and enable user
					$user->setGoogleId($userGoogleId);
					if (!$user->isEnabled()) {
						// change password for security reason if user is not enabled
						// i.e. has not confirmed the form based registration
						$user->setPassword($this->generateRandomPassword());
						$user->setEnabled(true);
					}

					// enhance profile
					if (!$user->getFirstName() || $user->getFirstName()=='') {
						$user->setFirstName($userFirstName);
					}
					
					$userManager->updateUser($user);
					
					$this->logger->info('get intent: user updated ['.$userGoogleId.','.$userEmail.','.$user->getId().']');
					
					// return user reference so token is assigned to user
					return ['data' => $user];
					
				case 'create':
					/**
					 * @var User $user
					 */
					$user = $userManager->findUserBy(array('googleId' => $userGoogleId));
					if (!$user) {
						$user = $userManager->findUserByEmail($userEmail);
					}
					if ($user) {
						$this->logger->warn('create intent: user found, returning linking_error ['.$userGoogleId.','.$userEmail.','.$user->getId().']');
						throw new OAuth2ServerException(OAuth2::HTTP_UNAUTHORIZED, 'linking_error', "User already exists",$user->getEmailCanonical());
					}
					
					// user is new, create and connect it
					$user = $userManager->createUser();
					$user->setUsername($this->generateRandomUsername($userGoogleId, 'google'));
					$user->setEmail($userEmail);
					$user->setPassword($this->generateRandomPassword());
					$user->setEnabled(true);
					
					$user->setRegistrationType('oauth:google');
					$user->setGoogleId($userId);
					
					$user->setFirstName($userFirstName);
					
					$userManager->updateUser($user);

					$this->logger->info('create intent: user created ['.$userGoogleId.','.$userEmail.','.$user->getId().']');

					// return user reference so token is assigned to user
					return ['data' => $user];

				default:
					$this->logger->critical('unsupported intent ['.$intent.']');
					Ensure::fail('intent not supported');
			}
			
			Ensure::fail('coding error ');
		}
		
		// helpers
		
		/**
		 * @return UserManager
		 */
		protected function getUserManager() {
			return $this->sc->get('bricks.custom.twentysteps_alexa.user_manager');
		}
		
		/**
		 * Generates a random username with the given
		 * e.g 12345_github, 12345_facebook
		 *
		 * @param string $username
		 * @param string $serviceName
		 * @return string
		 */
		private function generateRandomUsername($username, $serviceName){
			if(!$username){
				$username = "user". uniqid(rand(), true) . $serviceName;
			}
			
			return $username. "_" . $serviceName;
		}
		
		private function generateRandomPassword() {
			return uniqid(rand(), true);
		}
		
	}