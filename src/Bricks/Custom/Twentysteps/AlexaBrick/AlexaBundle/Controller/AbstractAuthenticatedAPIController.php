<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;


use Symfony\Component\HttpFoundation\Request;

use JMS\Serializer\Serializer;

use twentysteps\Commons\EnsureBundle\Ensure;

use Bricks\AbstractCustomBundle\Controller\AbstractAuthenticatedAPIController as AbstractAbstractAuthenticatedAPIController;
use Bricks\AbstractCustomBundle\Entity\AbstractUser;


abstract class AbstractAuthenticatedAPIController extends AbstractAbstractAuthenticatedAPIController {

    /** @var Serializer $serializer */
    protected $serializer;
 
    // overrides

    protected function prepareAction()
    {
        parent::prepareAction();
    }
	
	/**
	 * @return array
	 */
    protected function getSerializationGroups()
    {
        return ['public','self'];
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
        return 'bricks.custom.saarow_app.serialization.serialization_exclusion';
    }
	
	/**
	 * @param AbstractUser $user
	 * @return array|mixed|null|object
	 */
    protected function serializeUser(AbstractUser $user) {
        return json_decode($this->serializeJSON($this->user,["self","linked","public"],['requestingUser' => $this->user]),true);
    }

	// avatar
	
	/**
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function uploadAvatarPictureAction(Request $request) {
        $this->flash=$this->userModule->uploadAvatarPicture($request);
        return $this->infoAction($request);
    }
	
	/**
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function uploadAvatarPictureDesktopAction(Request $request) {
        $files = $request->files->get('files');
        $this->userModule->uploadAvatarPictureDesktop($files);
        return $this->infoAction($request);
    }
	
	/**
	 * @param Request $request
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function deleteAvatarPictureAction(Request $request) {
        $this->flash=$this->userModule->deleteAvatarPicture();
        return $this->infoAction($request);
    }


}