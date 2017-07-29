<?php

namespace Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\Foundlet;

use Symfony\Bridge\Monolog\Logger;

use twentysteps\Commons\EnsureBundle\Ensure;

use Bricks\Infrastructure\CoreBrick\CoreBundle\Base\AbstractBrickShell;

use Bricks\Basic\FoundBrick\FoundBundle\Foundlet\AbstractFoundlet as AbstractBaseFoundlet;
use Bricks\Basic\FoundBrick\FoundBundle\Search\SearchOptions;

abstract class AbstractFoundlet extends AbstractBaseFoundlet {


    /** @var  AbstractBrickShell */
    protected $brickShell;
    /** @var Logger */
    protected $logger;

    protected $query;

    public function __construct(AbstractBrickShell $brickShell, Logger $logger) {
        $this->setBrickShell($brickShell);
        $this->logger=$logger;
    }
	
	public function defineFields() {
		parent::defineFields();
		$this
			->addFieldDefiniton([
				'name'     => 'id',
				'type'     => 'long',
				'required' => true,
				'indexed'  => true,
				'stored'   => true,
				'value'    => function ($entity) {
					return $entity->getId();
				}
			]);
	}
	
	protected function getUserValueFromSearchOptions(SearchOptions $searchOptions, $key, $default=null) {
		$userData = $searchOptions->getUserData();
		if ($userData !== null) {
			Ensure::isTrue(is_array($userData), "UserData must be an array for property foundlet.");
			if (isset($userData[$key])) {
				return $userData[$key];
			}
		}
		return $default;
	}
	
}