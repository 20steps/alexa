<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Serialization;

use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;

use JMS\DiExtraBundle\Annotation as DI;
use JMS\Serializer\Exclusion\ExclusionStrategyInterface;
use JMS\Serializer\SerializationContext;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\AbstractEntity;

/**
 * Manipulate entity before final serialization
 *
 * @DI\Service("bricks.custom.twentysteps_alexa.serialization.user_data_serializer")
 * @DI\Tag("kernel.event_subscriber");
 */
class UserDataSerializer extends AbstractSerializer
{
	/**
	 * @DI\InjectParams({
	 *     "serviceContainer" = @DI\Inject("service_container")
	 * })
	 */
	public function __construct($serviceContainer) {
		parent::__construct($serviceContainer);
	}
	
	/**
	 * @inheritdoc
	 */
	public function onSerializeResource($resource, $event) {
	}
	
	/**
	 * @param GetResponseForControllerResultEvent $event
	 */
	public function onSerialize(GetResponseForControllerResultEvent $event)
	{
		// if it was a modifying and authenticated resource request attach user data to ride piggy back
		// so ng-api can update $rootScope.user
		$method = $event->getRequest()->getMethod();
		if (in_array($method,['POST','PUT','DELETE'])) {
			$resource = $event->getControllerResult();
			if ($resource && is_object($resource) && $resource instanceof AbstractEntity) {
				/** @var AbstractEntity $resource */
				$user = $this->getUser();
				if ($user) {
					$userData = json_decode($this->serializeJSON($user,["self","linked","public"],['requestingUser' => $user]),true);
					$resource->setUserData($userData);
				}
			}
		}
	}
	
	// the following must be synchronized with AbstractAuthenticatedAPIController in this bundle
	
	/** @return string */
	protected function serializeJSON($data,$serializationGroups = null, $enableMaxDepthChecks = true, $attributes = []) {
		$serializationContext = $this->getSerializationContext($serializationGroups,$enableMaxDepthChecks);
		foreach ($attributes as $key => $value) {
			$serializationContext->setAttribute($key,$value);
		}
		return $this->container->get('jms_serializer')->serialize($data,'json',$serializationContext);
	}
	
	/** @return SerializationContext */
	protected function getSerializationContext($serializationGroups = null, $enableMaxDepthChecks = true) {
		if (!$serializationGroups) {
			$serializationGroups = $this->getSerializationGroups();
		}
		$serializationContext = SerializationContext::create();
		if ($enableMaxDepthChecks) {
			$serializationContext->enableMaxDepthChecks();
		}
		if ($serializationGroups) {
			$serializationContext->setGroups($serializationGroups);
		}
		$customSerializationExlusionStrategy = $this->getCustomSerializationExclusionStrategy();
		if ($customSerializationExlusionStrategy) {
			/** @var ExclusionStrategyInterface $customSerializationExlusionStrategyService */
			$customSerializationExlusionStrategyService = $this->container->get($customSerializationExlusionStrategy);
			if ($customSerializationExlusionStrategyService) {
				$serializationContext->addExclusionStrategy($customSerializationExlusionStrategyService);
			}
		}
		return $serializationContext;
	}
	
	/** @return array */
	protected function getSerializationGroups() {
		return ["self","linked","public"];
	}
	
	/** @return string */
	protected function getCustomSerializationExclusionStrategy() {
		return 'bricks.custom.twentysteps_alexa.serialization.serialization_exclusion';
	}
	
	
	
}