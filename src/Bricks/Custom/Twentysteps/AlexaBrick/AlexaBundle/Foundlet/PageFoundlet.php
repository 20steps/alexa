<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Foundlet;

use Symfony\Bridge\Monolog\Logger;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Base\AbstractBrickShell;

use Bricks\Basic\FoundBrick\FoundBundle\Foundlet\FieldDefinition;
use Bricks\Basic\PagesBrick\PagesBundle\Shell\PagesShell;

use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Model\Page;


class PageFoundlet extends AbstractPagesFoundlet {

    public function __construct(AbstractBrickShell $brickShell, Logger $logger, PagesShell $pages) {
        parent::__construct($brickShell,$logger,$pages);
    }

    public function defineFields() {
        parent::defineFields();
        $this
            ->addFieldDefiniton(array(
                'name' => 'path',
                'type' => 'string',
                'required' => true,
                'indexed' => false,
                'stored' => true,
                'value' => function (Page $page, FieldDefinition $fieldDefinition) {
                    return $page->getPath($fieldDefinition->getLanguageCode());
                }
            ))
            ->addFieldDefiniton(array(
                'name' => 'title',
                'type' => 'text_general_highlight',
                'highlight' => 'title_highlighted',
                'required' => false,
                'indexed' => true,
                'stored' => true,
                'copyFieldNames' => array("text"),
                'value' => function (Page $page, FieldDefinition $fieldDefinition) {
                    return $page->getTitle($fieldDefinition->getLanguageCode());
                }
            ))
            ->addFieldDefiniton(array(
                'name' => 'content',
                'type' => 'text_general_highlight',
                'highlight' => 'content_highlighted',
                'required' => false,
                'indexed' => true,
                'stored' => true,
                'copyFieldNames' => array("text"),
                'value' => function (Page $page, FieldDefinition $fieldDefinition) {
                    return $page->getContent($fieldDefinition->getLanguageCode(),true);
                }
            ))
            ->addFieldDefiniton(array(
                'name' => 'metaDescription',
                'type' => 'text_general_highlight',
                'highlight' => 'meta_description_highlighted',
                'required' => false,
                'indexed' => true,
                'stored' => true,
                'copyFieldNames' => array("text"),
                'value' => function (Page $page, FieldDefinition $fieldDefinition) {
                    return $page->getMetaDescription($fieldDefinition->getLanguageCode());
                }
            ))
            ->addFieldDefiniton(array(
                'name' => 'fullFeaturedImageUrl',
                'type' => 'string',
                'required' => false,
                'indexed' => false,
                'stored' => true,
                'value' => function (Page $page, FieldDefinition $fieldDefinition) {
                    return $page->getFeaturedImageUrl('large');
                }
            ))
            ->addFieldDefiniton(array(
                'name' => 'mediumFeaturedImageUrl',
                'type' => 'string',
                'required' => false,
                'indexed' => false,
                'stored' => true,
                'value' => function (Page $page, FieldDefinition $fieldDefinition) {
                    return $page->getFeaturedImageUrl('medium');
                }
            ))
            ->addFieldDefiniton(array(
                'name' => 'stateName',
                'type' => 'string',
                'required' => false,
                'indexed' => false,
                'stored' => true,
                'value' => function (Page $page, FieldDefinition $fieldDefinition) {
                    return $page->getStateName();
                }
            ))
	        ->addFieldDefiniton(array(
		        'name' => 'metaKeywords',
		        'type' => 'text_general_highlight',
		        'highlight' => 'metaKeywordsHighlighted',
		        'required' => false,
		        'indexed' => true,
		        'stored' => true,
		        'copyFieldNames' => array("text"),
		        'value' => function (Page $post, FieldDefinition $fieldDefinition) {
			        return $post->getMetaKeywords();
		        }
	        ))
	        ->addFieldDefiniton(array(
		        'name' => 'tags',
		        'type' => 'text_general_highlight',
		        'highlight' => 'tagsHighlighted',
		        'required' => false,
		        'indexed' => true,
		        'stored' => true,
		        'copyFieldNames' => array("text"),
		        'value' => function (Page $entity, FieldDefinition $fieldDefinition) {
			        return $entity->getTags(true);
		        }
	        ));
    }

    /**
     * @see Foundlet
     * @return \Traversable
     */
    public function getAllEntities($projectId) {
        $this->pages->getNativeModule()->useNativeFunctions();
        return $this->pages->getContentModule()->findAllNodes(true,array('page'));
    }
}