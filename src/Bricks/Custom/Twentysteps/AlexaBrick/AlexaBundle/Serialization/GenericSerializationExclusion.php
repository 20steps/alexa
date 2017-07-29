<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Serialization;

use JMS\DiExtraBundle\Annotation as DI;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Serialization\SerializationExclusion as CoreSerializationExclusion;

/**
 * Generic exclusion strategy
 *
 * @DI\Service("bricks.custom.twentysteps_alexa.serialization.serialization_exclusion")
 */
class GenericSerializationExclusion extends CoreSerializationExclusion {
	
	/**
	 * @DI\InjectParams({
	 *     "logger" = @DI\Inject("logger")
	 * })
	 */
	public function __construct($logger) {
		parent::__construct($logger);
	}
	
}