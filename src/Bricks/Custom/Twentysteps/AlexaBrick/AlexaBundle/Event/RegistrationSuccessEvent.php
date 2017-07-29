<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Event;

use Bricks\AbstractCustomBundle\Event\AbstractRegistrationSuccessEvent;

/**
 * Event for successful registrations.
 */
class RegistrationSuccessEvent extends AbstractRegistrationSuccessEvent {

    const NAME = 'BRICKS_CUSTOM_TWENTYSTEPS_ALEXA_REGISTRATION_SUCCESS_EVENT';
	
}