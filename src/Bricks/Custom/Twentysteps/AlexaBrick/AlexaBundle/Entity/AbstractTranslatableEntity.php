<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\MappedSuperclass;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation\Type;
use Gedmo\Translatable\Translatable;

/**
 * Abstract base class for all translatable entities.
 *
 */
abstract class AbstractTranslatableEntity extends AbstractEntity implements Translatable {

    

}
