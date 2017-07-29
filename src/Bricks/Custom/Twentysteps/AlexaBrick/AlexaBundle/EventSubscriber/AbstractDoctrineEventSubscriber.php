<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;

use Psr\Log\LoggerInterface;

use Doctrine\Common\EventSubscriber;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Interfaces\CoreInjectionModule;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;

abstract class AbstractDoctrineEventSubscriber implements EventSubscriber
{
	
	/**
	 * @var ContainerInterface
	 */
    protected $sc;
	
    /** @var LoggerInterface */
	protected $logger;
	
	/** @var CoreInjectionModule */
	private $injectionModule;
	
	/**
	 * @var AlexaShell
	 */
    private $shell;
	
	/**
	 * AbstractDoctrineEventSubscriber constructor.
	 *
	 * @param ContainerInterface $sc
	 * @param LoggerInterface $logger
	 */
	public function __construct(ContainerInterface $sc, LoggerInterface $logger) {
        $this->sc=$sc;
        $this->logger=$logger;
    }


    // helpers

	/** @return CoreInjectionModule */
	protected function getInjectionModule() {
		if (!$this->injectionModule) {
			$this->injectionModule=$this->sc->get('core')->injection();
		}
		return $this->injectionModule;
	}
	
	/**
	 * @return AlexaShell
	 */
	protected function getShell() {
		if (!$this->shell) {
			$this->shell=$this->sc->get('twentysteps_alexa');
		}
		return $this->shell;
	}
	
	protected function enterProjectScope() {
		$this->getInjectionModule()->enterProjectScope('alexa');
	}
	
	protected function leaveProjectScope() {
		$this->getInjectionModule()->leaveScope();
	}
	
	/**
	 * Cleans up the text and adds separator.
	 *
	 * @param string $text
	 * @param string $separator
	 *
	 * @return string
	 */
	protected function urlize($text)
	{
		$separator = '-';
		if (function_exists('mb_strtolower')) {
			$text = mb_strtolower($text);
		} else {
			$text = strtolower($text);
		}
		
		// Remove all none word characters
		$text = preg_replace('/\W/', ' ', $text);
		
		// More stripping. Replace spaces with dashes
		$text = strtolower(preg_replace('/[^A-Za-z0-9\/]+/', $separator,
			preg_replace('/([a-z\d])([A-Z])/', '\1_\2',
				preg_replace('/([A-Z]+)([A-Z][a-z])/', '\1_\2',
					preg_replace('/::/', '/', $text)))));
		
		return trim($text, $separator);
	}
}