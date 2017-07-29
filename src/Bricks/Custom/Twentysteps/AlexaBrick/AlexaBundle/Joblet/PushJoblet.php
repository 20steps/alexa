<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Joblet;
	
	use JMS\DiExtraBundle\Annotation as DI;
	
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Joblet\AbstractJoblet;
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Joblet\Joblet as JobletInterface;
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations\Joblet;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;
	
	/**
	 * @DI\Service("bricks_custom_twentysteps_alexa_joblet_push")
	 * @DI\Tag("monolog.logger", attributes = {"channel" = "bricks.custom.twentysteps_alexas.joblet.push"})
	 * @DI\Tag("twentysteps.bricks.joblet", attributes = {"alias" = "push"})
	 *
	 * @Joblet(
	 *		scope="project",
	 *      actor="alexa",
	 *      pods={"job"},
	 *		queue="bricks_custom_twentysteps_alexa_push",
	 *		priority="normal",
	 *		interval="PT1M"
	 * )
	 **/
	class PushJoblet extends AbstractJoblet implements JobletInterface {
		
		/**
		 * @DI\InjectParams({
		 *     "core" = @Di\Inject("core"),
		 *     "brickShell" = @Di\Inject("twentysteps_alexa"),
		 *     "logger" = @Di\Inject("logger"),
		 *     "stopwatch" = @Di\Inject("debug.stopwatch", required=false)
		 * })
		 */
		public function __construct($core, $brickShell, $logger, $stopwatch=null) {
			parent::__construct($core, $brickShell, $logger, $stopwatch);
		}
		
		public function run($jobId) {
			/** @var AlexaShell $shell */
			$shell = $this->getBrickShell();
			return array('status' => 'success','result' => $shell->getAlexaModule()->processPush());
		}
		
	}