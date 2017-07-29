<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity;

use Doctrine\ORM\Event\PreFlushEventArgs;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Shells\CoreShell;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;


abstract class AbstractEntityListener {

    protected $container;
    protected $logger;

    /** @var  CoreShell $core */
    private $core;
    /** @var AlexaShell $shell */
    private $shell;


    public function __construct($container,$logger) {
        $this->container = $container;
        $this->logger=$logger;
    }

    /** @return CoreShell */
    protected function getCore() {
        if (!$this->core) {
            $this->core=$this->container->get('core');
        }
        return $this->core;
    }

    /** @return AlexaShell */
    protected function getShell() {
        if (!$this->shell) {
            $this->shell=$this->container->get('twentysteps_alexa');
        }
        return $this->shell;
    }
}