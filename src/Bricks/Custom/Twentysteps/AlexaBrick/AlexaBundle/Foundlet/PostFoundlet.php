<?php
	
	namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Foundlet;
	
	use Symfony\Bridge\Monolog\Logger;
	
	use Bricks\Infrastructure\CoreBrick\CoreBundle\Base\AbstractBrickShell;
	
	use Bricks\Basic\FoundBrick\FoundBundle\Foundlet\FieldDefinition;
	use Bricks\Basic\PagesBrick\PagesBundle\Shell\PagesShell;
	
	use Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Model\Post;
	
	class PostFoundlet extends AbstractPagesFoundlet {
		
		public function __construct(AbstractBrickShell $brickShell, Logger $logger, PagesShell $pages) {
			parent::__construct($brickShell, $logger, $pages);
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
					'value' => function (Post $post, FieldDefinition $fieldDefinition) {
						return $post->getPath($fieldDefinition->getLanguageCode());
					}
				))
				->addFieldDefiniton(array(
					'name' => 'date',
					'type' => 'long',
					'required' => true,
					'indexed' => false,
					'stored' => true,
					'value' => function (Post $post) {
						return $post->getDate()->getTimestamp();
					}
				))
				->addFieldDefiniton(array(
					'name' => 'title',
					'type' => 'text_general_highlight',
					'highlight' => 'titleHighlighted',
					'required' => false,
					'indexed' => true,
					'stored' => true,
					'copyFieldNames' => array("text"),
					'value' => function (Post $post, FieldDefinition $fieldDefinition) {
						return $post->getTitle($fieldDefinition->getLanguageCode());
					}
				))
				->addFieldDefiniton(array(
					'name' => 'content',
					'type' => 'text_general_highlight',
					'highlight' => 'contentHighlighted',
					'required' => false,
					'indexed' => true,
					'stored' => true,
					'copyFieldNames' => array("text"),
					'value' => function (Post $post, FieldDefinition $fieldDefinition) {
						return $post->getContent($fieldDefinition->getLanguageCode(),true);
					}
				))
                ->addFieldDefiniton(array(
                    'name' => 'slug',
                    'type' => 'string',
                    'required' => true,
                    'indexed' => false,
                    'stored' => true,
                    'value' => function (Post $post, FieldDefinition $fieldDefinition) {
                        return $post->getSlug();
                    }
                ))
                ->addFieldDefiniton(array(
                    'name' => 'stateName',
                    'type' => 'string',
                    'required' => true,
                    'indexed' => false,
                    'stored' => true,
                    'value' => function (Post $post, FieldDefinition $fieldDefinition) {
                        return $post->getStateName();
                    }
                ))
                ->addFieldDefiniton(array(
                    'name' => 'fullFeaturedImageUrl',
                    'type' => 'string',
                    'required' => false,
                    'indexed' => false,
                    'stored' => true,
                    'value' => function (Post $post, FieldDefinition $fieldDefinition) {
                        return $post->getFeaturedImageUrl('large');
                    }
                ))
                ->addFieldDefiniton(array(
                    'name' => 'mediumFeaturedImageUrl',
                    'type' => 'string',
                    'required' => false,
                    'indexed' => false,
                    'stored' => true,
                    'value' => function (Post $post, FieldDefinition $fieldDefinition) {
                        return $post->getFeaturedImageUrl('medium');
                    }
                ))
				->addFieldDefiniton(array(
					'name' => 'metaDescription',
					'type' => 'text_general_highlight',
					'highlight' => 'metaDescriptionHighlighted',
					'required' => false,
					'indexed' => true,
					'stored' => true,
					'copyFieldNames' => array("text"),
					'value' => function (Post $post, FieldDefinition $fieldDefinition) {
						return $post->getMetaDescription($fieldDefinition->getLanguageCode());
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
					'value' => function (Post $post, FieldDefinition $fieldDefinition) {
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
					'value' => function (Post $entity, FieldDefinition $fieldDefinition) {
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
			return $this->pages->getContentModule()->findAllNodes(true,array('post'));
		}
	}