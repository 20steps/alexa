<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\View;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Controller\AbstractBricksController;
use Bricks\Infrastructure\CoreBrick\CoreBundle\Annotations as BS;

use Bricks\Basic\PagesBrick\PagesBundle\Modules\ContentModule;

class ContentController extends AbstractBricksController {
	
	/**
	 * @var ContentModule $contentModule
	 * @BS\Inject(brickAlias="pages")
	 */
	private $contentModule;
	
	/**
	 * @param Request $request
	 */
	protected function setupController(Request $request) {
		parent::setupController($request);
		// force html format as some spiders send strange requests ...
		$request->setRequestFormat('html');
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @param string $slug
	 * @return array
	 */
	public function pageAction(Request $request, string $slug) {
		$node =  $this->contentModule->findNode('slug:'.$slug,$request->getLocale(),'page',true);
		return [
			'node' => $this->contentModule->serializeOne($node,$request->getLocale(),true,true), // let assigned pageslet do its magic
			'body_class' => 'page',
			'title' => $node->getTitle($request->getLocale())
		];
	}
	
	/**
	 * @View
	 * @param Request $request
	 * @param string $slug
	 * @return array
	 */
	public function postAction(Request $request, string $slug) {
		$node =  $this->contentModule->findNode('slug:'.$slug,$request->getLocale(),'page',true);
		return [
			'node' => $this->contentModule->serializeOne($node,$request->getLocale(),true,true), // let assigned pageslet do its magic
			'body_class' => 'post',
			'title' => $node->getTitle($request->getLocale())
		];
	}
	
	
}