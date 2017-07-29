<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;

use Gedmo\Mapping\Annotation as Gedmo;

use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation as JMS;

use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * Abstract base class for all entities.
 *
 */
abstract class AbstractEntity {

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @JMS\Groups({"public","public_list"})
     * @Groups({"all"})
     * @ApiProperty(
     *     attributes={
     *       "ngAdmin"={
     *          "views" = { "list", "show", "edit" },
     *          "label" = "Id",
     *          "editable" = false,
     *          "position" = 1
     *       }
     *     }
     * )
     */
    protected $id;

    /**
     * @Type("DateTime<'U'>")
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @JMS\Groups({"public","public_list"})
     * @Groups({"all"})
     * @ApiProperty(
     *     attributes={
     *       "ngAdmin"={
     *          "views" = { "show", "edit" },
     *          "editable" = false,
     *          "label" = "erzeugt am",
     *          "position" = 901,
     *       }
     *     }
     * )
     */
    protected $createdAt;

    /**
     * @Type("DateTime<'U'>")
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="updated_at", type="datetime")
     * @JMS\Groups({"public","public_list"})
     * @Groups({"all"})
     * @ApiProperty(
     *     attributes={
     *       "ngAdmin"={
     *          "views" = { "list", "show", "edit" },
     *          "editable" = false,
     *          "label" = "letzte Änderung",
     *          "position" = 902
     *       }
     *     }
     * )
     */
    protected $updatedAt;
    
    
    // holder for user data
	
	/**
	 * @var User|null
	 * @Groups({"all"})
	 */
	protected $userData;
	
	
	// toString
	
	public function __toString() {
		return (string)$this->getId();
	}
	
	
    /**
     * Get id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Get createdAt
     * @return \DateTime
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return AbstractEntity
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return AbstractEntity
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }
	
	
	// holder for user data
	
	/**
	 * @return User
	 */
	public function getUserData() {
		return $this->userData;
	}
	
	/**
	 * @param User $userData
	 * @return AbstractEntity
	 */
	public function setUserData($userData) {
		$this->userData = $userData;
		
		return $this;
	}
    
	
}
