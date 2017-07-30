<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity;

use FOS\OAuthServerBundle\Entity\AccessToken as BaseAccessToken;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="bricks_custom_twentysteps_alexa_access_token")
 * @ORM\Entity(repositoryClass="Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\AccessTokenRepository")
 */
class AccessToken extends BaseAccessToken
{
	/**
	 * @ORM\Id
	 * @ORM\Column(type="integer")
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Client")
	 * @ORM\JoinColumn(nullable=false)
	 */
	protected $client;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User", cascade={"persist","merge"})
	 */
	protected $user;
}