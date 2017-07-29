<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell;

use ZendDiagnostics\Result\Skip;
use ZendDiagnostics\Result\Success;
use ZendDiagnostics\Result\Warning;
use ZendDiagnostics\Result\Failure;

use twentysteps\Commons\EnsureBundle\Ensure;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Base\AbstractCustomShell;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\AlexaModule;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Modules\UptimeRobotModule;

class AlexaShell extends AbstractCustomShell  {

	/**
	 * @var array
	 * for entity listeners on long transactions
	 */
	public $fixturesBuffer;

	// initialization
	
    public function __construct($container, $logger, $stopwatch=null) {
        parent::__construct($container, $logger, $stopwatch);

        // don't do that in modules, might be to late - must be done before doctrine tries to resolve for the first entity (e.g. before security context ops on user ..)
        $this->registerEntityListeners();
        
        $this->fixturesBuffer = [];
    }

    // prepare doctrine

    private function registerEntityListeners() {
        $em=$this->container->get('doctrine.orm.entity_manager');
    }
	
	// getters for module
	
	/**
	 * @return AlexaModule
	 */
	public function getAlexaModule() {
		return $this->useModuleByKey('AlexaModule');
	}
	
	/**
	 * @return UptimeRobotModule
	 */
	public function getUptimeRobotModule() {
		return $this->useModuleByKey('UptimeRobotModule');
	}
	
	// overrides
	
	public function getProjectCode() {
		return 'alexa';
	}
	
	/**
     * Returns the current health state of the Brick.
     */
    public function check() {
        return new Success('ok.');
    }
    
    // custom
	
	public function enterProjectScope() {
		$this->getCore()->injection()->enterProjectScope($this->getProjectCode());
	}
	
	public function leaveProjectScope() {
		$this->getCore()->injection()->leaveScope();
	}
	
	/**
	 * @return bool
	 */
	public function isLive() {
    	return $this->getCore()->getContainer()->getParameter('bricks_custom_twentysteps_alexa_is_live',false);
	}

}