<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\OAuth;

use Symfony\Component\Security\Core\User\UserInterface;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;


// Roughly based on https://gist.github.com/danvbe/4476697
	
class UserProvider extends FOSUBUserProvider {
	
	public function connect(UserInterface $user, UserResponseInterface $response) {
		
		/**
		 * @var User $user
		 */
		$property = $this->getProperty($response);
		
		$username = $response->getUsername();
		
		// On connect, retrieve the access token and the user id
		$setterId = 'set' . ucfirst($this->getProperty($response));
		$setterAccessToken = substr($setterId,0,-2) . 'AccessToken';
		
		// Disconnect previously connected users
		if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
			$previousUser->$setterId(null);
			$previousUser->$setterAccessToken(null);
			$this->userManager->updateUser($previousUser);
		}
		
		// Connect using the current user
		$user->$setterId($username);
		$user->$setterAccessToken($response->getAccessToken());
		$this->userManager->updateUser($user);
	}
	
	public function loadUserByOAuthUserResponse(UserResponseInterface $response) {
		
		$setterId = 'set' . ucfirst($this->getProperty($response));
		$setterAccessToken = substr($setterId,0,-2) . 'AccessToken';
		
		$username = $response->getUsername();
		$email = $response->getEmail() ? $response->getEmail() : $username;
		$firstName = $response->getFirstName();
		
		/**
		 * @var User $user
		 */
		$user = $this->userManager->findUserBy(array($this->getProperty($response) => $username));
		
		if ($user) {
			// user is already connected, set the access token
			$user->$setterAccessToken($response->getAccessToken());

			// enhance profile
			if (! $user->getFirstName() || $user->getFirstName()=='') {
				$user->setFirstName($firstName);
			}

			$this->userManager->updateUser($user);

			return $user;
		}
		
		// user not connected, try to find by email
		$user = $this->userManager->findUserByEmail($email);
		if ($user) {
			// user found, connect and enable it
			$user->$setterId($username);
			$user->$setterAccessToken($response->getAccessToken());
			
			if (!$user->isEnabled()) {
				// change password for security reason if user is not enabled
				// i.e. has not confirmed the form based registration
				$user->setPassword($this->generateRandomPassword());
				$user->setEnabled(true);
			}
			
			// enhance profile
			if (! $user->getFirstName() || $user->getFirstName()=='') {
				$user->setFirstName($firstName);
			}
			
			$this->userManager->updateUser($user);

			return $user;
		}
		
		// user is new, create and connect it
		$user = $this->userManager->createUser();
		$user->setUsername($this->generateRandomUsername($username, $response->getResourceOwner()->getName()));
		$user->setEmail($email);
		$user->setPassword($this->generateRandomPassword());
		$user->setEnabled(true);
		
		$user->setRegistrationType('oauth:'.$response->getResourceOwner()->getName());
		$user->$setterId($username);
		$user->$setterAccessToken($response->getAccessToken());
		
		$user->setFirstName($response->getFirstName());
		
		$this->userManager->updateUser($user);
		return $user;
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