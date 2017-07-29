<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

use JMS\Serializer\Annotation as JMS;
use JMS\Serializer\Annotation\Type;

use Bricks\AbstractCustomBundle\Entity\AbstractLogin;

/**
 * Login
 *
 * @ORM\Table(name="bricks_custom_twentysteps_alexa_login")
 * @ORM\Entity(repositoryClass="Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\LoginRepository")
 */
class Login extends AbstractLogin
{

    // generic properties

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Type("DateTime<'U'>")
     */
    protected $createdAt;
    /**
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="logins")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @JMS\Exclude
     *
     **/
    protected $user;

}
