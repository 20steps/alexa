<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Controller\AbstractBricksController;

abstract class AbstractAPIController extends AbstractBricksController {
	
	/**
	 * @var string $locale
	 */
    protected $locale;

	/**
	 * @var string $role
	 */
    protected $role;
	
	/**
	 * @param Request $request
	 */
    protected function setupController(Request $request) {
        parent::setupController($request);
        $this->locale=$request->get('locale','de');
        $this->role=$request->get('role','user');
    }
	
	/**
	 * @return array
	 */
    protected function getSerializationGroups()
    {
        return ['public'];
    }
	
	/**
	 * @return array
	 */
    protected function getSerializationGroupsForLists()
    {
        return ['public_list'];
    }
	
	/**
	 * @return string
	 */
    protected function getCustomSerializationExclusionStrategy() {
        return 'bricks.custom.twentysteps_alexa.serialization.serialization_exclusion';
    }
}