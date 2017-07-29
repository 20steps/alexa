<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Foundlet;

use Bricks\Basic\FoundBrick\FoundBundle\Foundlet\AbstractFoundlet as AbstractBaseFoundlet;
use Bricks\Basic\FoundBrick\FoundBundle\Foundlet\FieldDefinition;
use Bricks\Basic\FoundBrick\FoundBundle\Search\SearchOptions;
use Bricks\Basic\FoundBrick\FoundBundle\Foundlet\ViewType;
use Bricks\Basic\PagesBrick\PagesBundle\Shell\PagesShell;
use Bricks\Infrastructure\CoreBrick\CoreBundle\Base\AbstractBrickShell;
use Symfony\Bridge\Monolog\Logger;

abstract class AbstractPagesFoundlet extends AbstractFoundlet {


    protected $pages;

    private $languageCodes = null;

    public function __construct(AbstractBrickShell $brickShell, Logger $logger, PagesShell $pages) {
        parent::__construct($brickShell,$logger);
        $this->pages=$pages;
    }
    /**
     * Initializes the language code array.
     */
    protected function initLanguageCodes() {
    	return [
    		'de' => 'de'
	    ];

    	/*
        $this->languageCodes=array();
        $locales=$this->pages->getContentModule()->getAvailableLocales();
        foreach ($locales as $locale) {
            $this->languageCodes[$locale]=$locale;
        }*/
    }

    /**
     * Returns the language code array.
     * @return array
     */
    protected function getLanguageCodes() {
        if (!$this->languageCodes) {
            $this->initLanguageCodes();
        }
        return $this->languageCodes;
    }


}