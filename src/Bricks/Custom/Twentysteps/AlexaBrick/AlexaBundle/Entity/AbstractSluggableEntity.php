<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiProperty;

/**
 * Abstract base class for all entities.
 *
 */
abstract class AbstractSluggableEntity extends AbstractEntity {
	
	/**
	 * @var string The public name of this entity.
	 *
	 * @ORM\Column
	 * @Assert\NotBlank
	 * @JMS\Groups({"public","public_list"})
	 * @Groups({"all","write_all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "position" = 10,
	 *          "views" = { "list", "show", "create", "edit"},
	 *          "required" = true,
	 *          "label" = "Name",
	 *          "isDetailLink" = true
	 *       }
	 *     }
     * )
	 */
	protected $name;
	
	/**
	 * @var string The slug this entity.
	 * @Gedmo\Slug(fields={"name"}, updatable=true)
	 * @ORM\Column(length=128, unique=true)
	 * @JMS\Groups({"public","public_list"})
	 * @Groups({"all"})
	 * @ApiProperty(
	 *     attributes={
	 *       "ngAdmin"={
	 *          "views" = { "show", "edit"},
	 *          "position" = 11,
	 *          "editable" = false,
	 *          "label" = "Slug"
	 *       }
	 *     }
	 * )
	 */
	protected $slug;
	
	/**
	 * @return mixed
	 */
	public function getSlug() {
		return $this->slug;
	}
	
	/**
	 * @param string $slug
	 * @return AbstractSluggableEntity
	 */
	public function setSlug($slug) {
		$this->slug = $slug;
		
		return $this;
	}
	
	/**
	 * @return string
	 */
	public function getName() {
		return $this->name;
	}
	
	/**
	 * @param string $name
	 * @return AbstractSluggableEntity
	 */
	public function setName($name) {
		$this->name = $name;
		
		return $this;
	}
	
}
