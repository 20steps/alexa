<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Serialization;

use Bricks\AbstractCustomBundle\Serialization\AbstractContextBuilder as BaseAbstractContextBuilder;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;

use Stringy\StaticStringy as S;

abstract class AbstractContextBuilder extends BaseAbstractContextBuilder
{
	
	protected function getSubscribedClass() {
		$className=get_class($this);
		$className=substr($className, strrpos($className, '\\')+1);
		return 'Bricks\\Custom\\Twentysteps\\AlexaBrick\\AlexaBundle\\Entity\\'.S::removeRight($className,'ContextBuilder');
	}
	
	// helpers
	
	/** @return AlexaShell */
	protected function getShell() {
		return $this->sc->get('twentysteps_alexa');
	}
	
	
}