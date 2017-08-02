<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Model;

use Stringy\StaticStringy as S;

use Bricks\Basic\PagesBrick\PagesBundle\Model\Node;
use Bricks\Basic\PagesBrick\PagesBundle\Modules\ContentModule;
use Bricks\Basic\PagesBrick\PagesBundle\Modules\OptionModule;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Interfaces\CoreCDNModule;
use Bricks\Infrastructure\CoreBrick\CoreBundle\Interfaces\CoreInjectionModule;
use Kayue\WordpressBundle\Model\Post as PostEntity;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Shell\AlexaShell;


class AbstractModel extends Node {

    /**
     * @var ContentModule $contentModule
     */
    protected $contentModule;

    /**
     * @var OptionModule $optionModule
     */
    protected $optionModule;

    /**
     * @var AlexaShell
     */
    protected $brickShell;

    /**
     * @var CoreInjectionModule
     */
    protected $injectionModule;

    /**
     * @var CoreCDNModule
     */
    protected $cdnModule;

    /** @var string */
    protected $urlPrefix;


    public function __construct(PostEntity $entity)
    {
        parent::__construct($entity);
    }
	
	public function getObjectClass() {
		return \Doctrine\Common\Util\ClassUtils::getRealClass(get_class($this));
	}
	
	
	public function setContentModule($contentModule) {
        $this->contentModule=$contentModule;
        return $this;
    }

    public function setOptionModule($optionModule) {
        $this->optionModule=$optionModule;
        return $this;
    }

    public function setInjectionModule($injectionModule) {
        $this->injectionModule=$injectionModule;
    }

    public function setCDNModule($cdnModule) {
        $this->cdnModule=$cdnModule;
    }

    public function setBrickShell(AlexaShell $brickShell) {
        $this->brickShell=$brickShell;
        return $this;
    }

    public function getBrickShell() {
        return $this->brickShell;
    }

    public function getPrimaryCategory($locale=false) {
        $taxonomyEntries = $this->entity->getTaxonomyEntriesByName('category',$locale,true);
        if (is_array($taxonomyEntries) && count($taxonomyEntries)>0) {
            return $taxonomyEntries[0];
        }
        return null;
    }

    public function getPrimaryCategoryName() {
        $primaryCategory = $this->getPrimaryCategory();
        if ($primaryCategory) {
            return $primaryCategory['name'];
        }
        return null;
    }

    public function getFeaturedImageUrl($size='full') {
        $url=$this->cdnModule->mapUrl(parent::getFeaturedImageUrl($size));
        if (substr($url,0,4)!='http') {
            return 'http:'.$url;
        }
        return $url;
    }

    public function getFullFeaturedImageUrl() {
        $url=$this->cdnModule->mapUrl(parent::getFeaturedImageUrl('large'));
        if (substr($url,0,4)!='http') {
            return 'http:'.$url;
        }
        return $url;
    }

    public function getMediumFeaturedImageUrl() {
        $url=$this->cdnModule->mapUrl(parent::getFeaturedImageUrl('medium'));
        if (substr($url,0,4)!='http') {
            return 'http:'.$url;
        }
        return $url;
    }
	
	/**
	 * @return string
	 */
	public function getUrlPrefix() {
		return $this->urlPrefix;
	}
	
	/**
	 * @param string $urlPrefix
	 * @return self
	 */
	public function setUrlPrefix($urlPrefix) {
		$this->urlPrefix = $urlPrefix;
		
		return $this;
	}
	
	
	
	public function getShortDescription() {
		$shortDescription = $this->getMeta('short_description', null);
		if($shortDescription) {
			return $shortDescription;
		}
		$content = strip_tags($this->getContent('de',true));
		return (string)(S::safeTruncate($content,162, ' ...'));
	}
	
	public function getPath($locale = 'de')
	{
		return '/'.$locale.'/c/'.$this->getSlug($locale);
	}
	
}