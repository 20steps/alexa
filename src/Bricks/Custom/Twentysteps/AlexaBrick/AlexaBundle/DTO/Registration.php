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
    private $type;
    private $birthday;
    private $firstName;
    private $lastName;
    private $message;

    public function setFromRequest(Request $request) {
        parent::setFromRequest($request);
        $this->setType($request->request->get('type'));
        $this->setFirstName($request->request->get('first_name'));
        $this->setLastName($request->request->get('last_name'));
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
	
	/**
	 * @return mixed
	 */
	public function getMessage() {
		return $this->message;
	}
	
	/**
	 * @param mixed $message
	 * @return Registration
	 */
	public function setMessage($message) {
		$this->message = $message;
		
		return $this;
	}
    
    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type?:'user';
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

}