<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Serialization;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

use JMS\DiExtraBundle\Annotation as DI;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Entity\AbstractEntity;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;

/**
 * Dynamically add serialization groups e.g. given roles of user
 *
 * @DI\Service(
 *     "bricks.custom.twentysteps_alexa.serialization.generic_context_builder",
 *     decorates = "api_platform.serializer.context_builder",
 *     decoration_inner_name = "app.serializer.builder.generic_inner"
 * )
 */
class GenericContextBuilder extends AbstractContextBuilder
{
	
	/**
	 * @DI\InjectParams({
	 *     "decorated" = @DI\Inject("app.serializer.builder.generic_inner"),
	 *     "sc" = @DI\Inject("service_container")
	 * })
	 */
	public function __construct(SerializerContextBuilderInterface $decorated, ContainerInterface $sc)
	{
		parent::__construct($decorated,$sc);
	}
	
	/**
	 * @inheritdoc
	 */
	
	protected function updateGroups($resource, array $context, bool $normalization, Request $request, $extractedAttributes = null)
	{
		// add {entity_class}_admin group given role of current user
		
		/*if ($this->getTokenStorage()->getToken()) {
			if ($this->getAuthorizationChecker()->isGranted($this->getAdminRoleName()) && false === $normalization) {
				$context['groups'][] = $this->getGroupPrefix($resource).'admin';
				$context['groups'][] = 'all_admin';
			}
		}*/
		
		// TODO: remove after testing
		/*$context['groups'][] = 'all_public';
		$context['groups'][] = 'all_details_public';
		$context['groups'][] = $this->getGroupPrefix($resource).'public';
		$context['groups'][] = $this->getGroupPrefix($resource).'details_public';
		$context['groups'][] = $this->getGroupPrefix($resource).'admin';*/
		
		if ($request->query->get('single')) {
			$context['groups'][] = $this->getGroupPrefix($context['resource_class']).'details_public';
		}
		
		return $context;
	}
	
	/**
	 * @inheritdoc
	 */
	protected function isSubscribed($resource) {
		
		return $resource instanceof AbstractEntity || $resource instanceof User || $resource instanceof Paginator;
	}
	
	// helpers
	
	protected function getAdminRoleName() {
		return 'ROLE_TWENTYSTEPS_ALEXA_ADMIN';
	}
	
}