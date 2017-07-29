<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Controller\AbstractBricksController;

abstract class AbstractAPIController extends AbstractBricksController {

    protected $locale;
    protected $editor;

    protected function setupController(Request $request) {
        parent::setupController($request);
        $this->locale=$request->get('locale','de');
        $this->editor=$request->get('role',false);
    }

    protected function getSerializationGroups()
    {
        return ['public'];
    }

    protected function getSerializationGroupsForLists()
    {
        return ['public_list'];
    }

    protected function getCustomSerializationExclusionStrategy() {
        return 'bricks.custom.twentysteps_alexa.serialization.serialization_exclusion';
    }
}