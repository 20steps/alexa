<?php
	
namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\EventSubscriber;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Psr\Log\LoggerInterface;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;

abstract class AbstractKernelEventSubscriber implements EventSubscriberInterface
{
	
	/** @var ContainerInterface  */
	private $sc;
	
	/** @var  LoggerInterface */
	private $logger;

	public function __construct(ContainerInterface $sc, LoggerInterface $logger)
	{
		$this->sc = $sc;
		$this->logger = $logger;
	}
	
	// helpers
	
	
	/** @return AlexaShell */
	protected function getShell() {
		return $this->sc->get('twentysteps_alexa');
	}
	
}