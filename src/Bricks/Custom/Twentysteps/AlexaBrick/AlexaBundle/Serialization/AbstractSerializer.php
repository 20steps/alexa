<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Serialization;

use Bricks\AbstractCustomBundle\Serialization\AbstractSerializer as BaseAbstractSerializer;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;

use libphonenumber\PhoneNumberUtil;
use Stringy\StaticStringy as S;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

abstract class AbstractSerializer extends BaseAbstractSerializer
{
	
	// overrides
	
	protected function getSubscribedClass() {
		$className=get_class($this);
		$className=substr($className, strrpos($className, '\\')+1);
		return 'Bricks\\Custom\\Twentysteps\\AlexaBrick\\AlexaBundle\\Entity\\'.S::removeRight($className,'Serializer');
	}
	
	protected function getSubscribedMethods() {
		return [Request::METHOD_GET,Request::METHOD_POST, Request::METHOD_PUT];
	}
	
	// helpers
	
	/** @return AlexaShell */
	protected function getShell() {
		return $this->container->get('twentysteps_alexa');
	}
	
	/** @return PhoneNumberUtil */
	protected function getPhoneNumberUtil() {
		return $this->container->get('libphonenumber.phone_number_util');
	}
	
	/** @return User|null */
	protected function getUser() {
		/** @var TokenStorageInterface $tokenStorage */
		$tokenStorage = $this->container->get('security.token_storage');
		$token = $tokenStorage->getToken();
		if ($token) {
			$user = $token->getUser();
			if ($user instanceof User) {
				return $user;
			}
		}
		return null;
	}
}