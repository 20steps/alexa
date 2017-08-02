<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\DTO;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

use Bricks\AbstractCustomBundle\DTO\AbstractRegistration;

/**
 * Model for registration form
 */
class Registration extends AbstractRegistration {

    private $source;

    public function setFromRequest(Request $request) {
        parent::setFromRequest($request);
    }
	
	/**
	 * @return mixed
	 */
	public function getSource() {
		return $this->source;
	}
	
	/**
	 * @param mixed $source
	 * @return Registration
	 */
	public function setSource($source) {
		$this->source = $source;
		
		return $this;
	}
	
}