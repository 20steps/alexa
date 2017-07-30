<?php

class AppKernel extends \Bricks\Platform\BricksKernel {
	
	/**
	 * {@inheritdoc}
	 */
	public function registerBundles() {
		$appBundles = [
			
			// 20steps/angularjs-bundle
			new twentysteps\Bundle\AngularJsBundle\twentystepsAngularJsBundle(),
			
			// Custom dependencies
			new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
			new Braincrafted\Bundle\BootstrapBundle\BraincraftedBootstrapBundle(),

			// Custom Bricks
			new Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle\BricksCustomTwentystepsAlexaBundle()

		];
		
		// return unison with bundles registered by bricks-core
		return array_merge($appBundles, parent::registerBundles());
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function getRootDir()
	{
		return __DIR__;
	}
	
}
