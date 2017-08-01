<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Pageslet;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Model\Page;
use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Model\Post;
use JMS\DiExtraBundle\Annotation as DI;
use Kayue\WordpressBundle\Entity\Post as PostEntity;
use Mcfedr\AwsPushBundle\Message\Message as PushMessage;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use twentysteps\Commons\EnsureBundle\Ensure;

use Bricks\Basic\PagesBrick\PagesBundle\Annotations as Pages;
use Bricks\Basic\PagesBrick\PagesBundle\Pageslet\AbstractPageslet;
use Bricks\Basic\PagesBrick\PagesBundle\Model\SitemapItem;
use Bricks\Basic\PagesBrick\PagesBundle\Event\PagesNodeEvent;
use Bricks\Basic\MobileBrick\MobileBundle\DTO\Message;
use Bricks\Basic\FoundBrick\FoundBundle\Shell\FoundShell;
use Bricks\Basic\PagesBrick\PagesBundle\Model\Node;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell;


/**
 *
 * @DI\Service("bricks.custom.twentysteps_alexa.pageslet.alexa")
 * @DI\Tag("monolog.logger", attributes = {"channel" = "bricks.custom.twentysteps_alexa.pageslet.alexa"})
 * @DI\Tag("twentysteps.bricks.pageslet", attributes = {"alias" = "alexa"})
 *
 * @Pages\Pageslet(
 *      title="Twentysteps / AlexaBrick / Alexa",
 *      description="Some custom node types.",
 *      nodeTypes={"page","post","attachment"}
 * )
 */
class Pageslet extends AbstractPageslet {
	
	/**
	 * @var FoundShell
	 */
    private $found;
	
	/**
	 * @var Router
	 */
    private $router;
    
    /**
     * @DI\InjectParams({
     *     "brickShell" = @Di\Inject("twentysteps_alexa"),
     *     "core" = @Di\Inject("core"),
     *     "pages" = @Di\Inject("pages"),
     *     "found" = @Di\Inject("found"),
     *     "eventDispatcher" = @Di\Inject("event_dispatcher"),
     *     "router" = @Di\Inject("router"),
     *     "logger" = @Di\Inject("logger"),
     *     "stopwatch" = @Di\Inject("stopwatch", required = false),
     * })
     */
    public function __construct($brickShell,$core,$pages,$found,$eventDispatcher,$router,$logger,$stopwatch=null) {
        parent::__construct($brickShell,$core,$pages,$eventDispatcher,$logger,$stopwatch);
        $this->found = $found;
        $this->router = $router;
    }


    public function getLocales() {
        return array('de');
    }

    public function createNodeFromEntity($entity) {
	    /**
	     * @var PostEntity $entity
	     */
        switch ($entity->getType()) {
            case 'page':
                $page = new Page($entity);
	            $page->setURLPrefix($this->core->getContainer()->getParameter('bricks_custom_twentysteps_alexa_protocol').'://'.$this->core->getContainer()->getParameter('bricks_custom_twentysteps_alexa_host').'/');
                $page->setNativeModule($this->pages->getNativeModule());
                $page->setContentModule($this->pages->getContentModule());
                $page->setInjectionModule($this->core->injection());
                $page->setCDNModule($this->core->cdn());
                return $page;
            case 'post':
                $post = new Post($entity);
	            $post->setURLPrefix($this->core->getContainer()->getParameter('bricks_custom_twentysteps_alexa_protocol').'://'.$this->core->getContainer()->getParameter('bricks_custom_twentysteps_alexa_host').'/');
                $post->setNativeModule($this->pages->getNativeModule());
                $post->setContentModule($this->pages->getContentModule());
                $post->setInjectionModule($this->core->injection());
                $post->setCDNModule($this->core->cdn());
                return $post;
            default:
	            Ensure::fail("Unknown type [".$entity->getType()."]");
                return $entity;

        }
    }

    /**
     * @return array
     */
    public function transformNodeData($nodeData, $node, $locale, $details=false, $render=false) {
        $scope=$this->core->injection()->getScope();
        Ensure::isEqual($scope, "project","transformNodeData(...) must only be called in project scope.");

        $project=$this->core->injection()->getActor();
        Ensure::isNotNull($project, "Project must not be null when calling transformNodeData(...).");

        Ensure::isEqual($project->getCode(),"alexa","Pageslet should only be executed for project with code alexa");
	
	    /**
	     * @var Node $node
	     */
        if ($details) {
            // augment node types custom for alexa
            switch ($node->getType()) {
                case 'page':
                    /** @var Page $page */
                    $page = $node;
                    $nodeData['page'] = array(
                        'fullFeaturedImageUrl' => $page->getFullFeaturedImageUrl(),
                        'mediumFeaturedImageUrl' => $page->getMediumFeaturedImageUrl(),
                        'shortDescription' => $page->getShortDescription(),
                        'paths' => array(
                            'de' => $page->getPath('de'),
                            'en' => $page->getPath('en')
                        ),
                    );
                    break;
                case 'post':
                    /** @var Post $post */
                    $post = $node;
                    $primaryCategory = $post->getPrimaryCategory();
                    $nodeData['post'] = array(
                        'paths' => array(
                            'de' => $post->getPath('de'),
                            'en' => $post->getPath('en')
                        ),
                        'fullFeaturedImageUrl' => $post->getFullFeaturedImageUrl(),
                        'mediumFeaturedImageUrl' => $post->getMediumFeaturedImageUrl(),
                        'shortDescription' => $post->getShortDescription(),
                        'categories' => array(
                            'primary' => $primaryCategory?array(
                                'id' => $primaryCategory['id'],
                                'name' => $primaryCategory['name'],
                                'slug' => $primaryCategory['slug']
                            ):null
                        )
                    );
                    break;
            }
        } else {
            // no details
            switch ($node->getType()) {

            }
            // nothing to do for non-detailed node view yet
        }

        //remove generic meta data (to reduce transfer volume)
        unset($nodeData['metas']);
        return $nodeData;
    }


    public function getSitemapItems() {
    	
        /** @var SitemapItem[] $sitemapItems */
        $sitemapItems=array();

        // start urls
        $sitemapItems[]=new SitemapItem('/');

        // generate sitemap entries for all locales and node types
        foreach ($this->getLocales() as $locale) {

            // homepage
            $sitemapItems[]=new SitemapItem('/'.$locale);

            // pages
            array_map(function(Page $page) use (&$sitemapItems,$locale) {
                $sitemapItems[]=new SitemapItem($page->getPath($locale));
            },$this->pages->getContentModule()->findAllNodes(true,array('page')));

            // posts
            $sitemapItems[]=new SitemapItem('/#!/'.$locale.'/news');
            array_map(function(Post $post) use (&$sitemapItems,$locale) {
                $sitemapItems[]=new SitemapItem($post->getPath($locale));
            },$this->pages->getContentModule()->findAllNodes(true,array('post')));

			// functional pages in SPA
	        $functionalRoutes = [
	        	'bricks.custom.twentysteps_alexa.user.login',
		        'bricks.custom.twentysteps_alexa.user.register',
	        	'bricks.custom.twentysteps_alexa.user.reset_password',
	        	'bricks.custom.twentysteps_alexa.user.alexa.login',
		        'bricks.custom.twentysteps_alexa.user.alexa.register'
	        ];
	        array_map(function($functionalRoute) use (&$sitemapItems,$locale) {
	        	$url = $this->router->getGenerator()->generate($functionalRoute,['_locale' => $locale,'project' => 'alexa'],UrlGeneratorInterface::ABSOLUTE_URL);
		        $path = parse_url($url,PHP_URL_PATH);
		        $sitemapItems[]=new SitemapItem($path);
	        },$functionalRoutes);
        }
	    
        return $sitemapItems;

    }

    public function onNodeEvent(PagesNodeEvent $event) {
	
	    $node = $event->getNode();

        $this->logger->info('TwentystepsAlexa Pageslet received PagesNodeEvent: ['.$event->getAction().','.($node?$node->getId():'null').','.var_export($event->getData(),true).']');

        // trigger mobile preview in alexa
		$this->pushPreviewMessages($event);
		
		// synchronize CRUD from PushMessage CTP to PushMessage Entity
		$this->synchronizePushMesssages($event);
		
		// inform editors about activity in CMS
        $this->pushActivityToEditors($event);
        
        // hand over node to found for near-realtime
        // (re)indexing or deletion via Founds Pages-bridge
        $this->found->getPagesBridgeModule()->onNodeEvent($event,'twentysteps_alexa');

        // invalidate full website (html and json)
        // ... please make this more targeted in derived pageslets
        $this->core->varnish()->invalidateRegex('.*','html|json',
            array(
                $this->pages->getSiteModule()->getDomain()
            )
        );

        // invalidate all cached responses tagged with this node
        // ... be it in the Bricks or the  Custom API
        $this->invalidateCacheTagsForNodes(array($event->getId()));

        // invalidate cdn
        $this->core->cdn()->purgeZone();
    }
    
    // helpers
	
	protected function pushPreviewMessages(PagesNodeEvent $event) {
		if ($event->getAction()=='PREVIEW') {
			if (array_key_exists('username_app',$event->getData())) {
				$username = $event->getData()['username_app'];
				$user = $this->getBrickShell()->getUserModule()->findOneByUsername($username);
				$node = $event->getNode();
				
				if ($user) {
					$message = new PushMessage();
					$message->setText('PREVIEW: '.$event->getNode()->getTitle());
					$message->setCustom(array(
						'action' => 'node_preview',
						'user' => array(
							'username' => $username
						),
						'node' => array(
							'id' => $node->getId(),
							'slug' => $node->getSlug(),
							'type' => $node->getType(),
							'title' => $node->getTitle(),
							'index' => $node->getMeta('index', 0)
						)
					));
					$message->setContentAvailable(true);
					$message->setSound('');
					$this->logger->info('publishing message to user ['.$user->getId().'] ['.var_export($message,true));
					$this->getBrickShell()->getAWSModule()->publishToUser($message,$user->getId());
				}
			}
		}
	}
	
	protected function synchronizePushMesssages(PagesNodeEvent $event) {
    	$node = $event->getNode();

		if ($event->getAction()=='CREATED' && $node->getType()=='push_message') {
			$this->getBrickShell()->getPushMessageModule()->syncPushMessageFromPagesNode($node);
		}
		
		if ($event->getAction()=='UPDATED' && $node->getType()=='push_message') {
			$this->getBrickShell()->getPushMessageModule()->syncPushMessageFromPagesNode($node);
		}
		
		if ($event->getAction()=='DELETED') {
			// we do not have a node type here, so remove "on verdacht"
			$this->getBrickShell()->getPushMessageModule()->removePushMessageFromPagesNodeId($event->getId());
		}
	}
	
	protected function pushActivityToEditors(PagesNodeEvent $event) {
    	$node = $event->getNode();
		if ($event->getAction()=='CREATED') {
			switch ($node->getType()) {
				case 'post':
					/** @var Post $post */
					$this->logger->info('got PagesNodeEvent for node ['.$node->getId().']');
					$pushMessage = new PushMessage();
					$pushMessage->setText('CREATED: '.$node->getTitle());
					$pushMessage->setCustom(array(
						'action' => 'new_post',
						'node' => array(
							'slug' => $node->getSlug(),
							'id' => $node->getId(),
							'title' => $node->getTitle(),
							'type' => $node->getType(),
							'index' => $node->getMeta('index', 0),
						)
					));
					$this->getBrickShell()->getAWSModule()->publishToEditors($pushMessage);
					break;
			}
		}
		
		if ($event->getAction()=='UPDATED') {
			switch ($node->getType()) {
				case 'post':
					/** @var Post $post */
					$this->logger->info('got PagesNodeEvent for node ['.$node->getId().']');
					$pushMessage = new PushMessage();
					$pushMessage->setText('UPDATED: '.$node->getTitle());
					$pushMessage->setCustom(array(
						'action' => 'new_post',
						'node' => array(
							'slug' => $node->getSlug(),
							'id' => $node->getId(),
							'title' => $node->getTitle(),
							'type' => $node->getType(),
							'index' => $node->getMeta('index', 0),
						)
					));
					$this->getBrickShell()->getAWSModule()->publishToEditors($pushMessage);
					break;
			}
		}
	}
	
	/**
	 * @return Shell\AlexaShell
	 */
	public function getBrickShell() {
		return Ensure::isNotNull($this->brickShell, "missing brickShell in Pageslet");
	}
}