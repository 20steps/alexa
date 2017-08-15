<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\OAuth;
	
	use Symfony\Component\DependencyInjection\Container;
	use Monolog\Logger;
	
	use OAuth2\Model\IOAuth2Client;
	use OAuth2\OAuth2ServerException;
	use OAuth2\OAuth2;

	use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;

	use twentysteps\Commons\EnsureBundle\Ensure;
	
	/**
	 * Class SocialGrantExtension
	 * @package AppBundle\OAuth
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
			
			switch($intent) {
				case 'get':
					break;
				case 'create':
					break;
				default:
					Ensure::fail('intent not supported');
			}
			
			throw new OAuth2ServerException(OAuth2::HTTP_FORBIDDEN, 'bla', "The redirect URI is missing or do not match");
			return null;
		}
		
	}