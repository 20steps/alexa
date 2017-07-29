<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Bricks\AbstractCustomBundle\Controller\AbstractSignInAPIController;
use Bricks\AbstractCustomBundle\DTO\AbstractRegistration;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\DTO\Registration;

class SignInAPIController extends AbstractSignInAPIController {

    /** @return AbstractRegistration */
    protected function registrationFactory() {
        return new Registration();
    }

}

