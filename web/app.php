<?php

use Symfony\Component\HttpFoundation\Request;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/setup.html#checking-symfony-application-configuration-and-setup
// for more information
umask(0000);


/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
	
Request::setTrustedProxies(['127.0.0.1/32','192.168.0.1/16','136.243.144.68','136.243.144.74','136.243.144.75','136.243.148.18','138.201.122.28','138.201.137.4','144.76.121.145']);

$sfRequest = Request::createFromGlobals();

// Possibly do not lazy load worpdress as part of the Pages Brick ...
$lazyLoadWordpress = true;
if (isset($_SERVER['LAZY_LOAD_WORDPRESS']) && ($_SERVER['LAZY_LOAD_WORDPRESS'] === '0' || $_SERVER['LAZY_LOAD_WORDPRESS']=='false')) {
	$lazyLoadWordpress = false;
}

if ( !$lazyLoadWordpress
	&& strpos($_SERVER['REQUEST_URI'],'/bricks')!==0
	&& strpos($_SERVER['REQUEST_URI'],'/auttables')!==0
	&& strpos($_SERVER['REQUEST_URI'],'/de/bricks')!==0
	&& strpos($_SERVER['REQUEST_URI'],'/en/bricks')!==0
	&& strpos($_SERVER['REQUEST_URI'],'/_wdt')!==0
	&& strpos($_SERVER['REQUEST_URI'],'/_profiler')!==0
	&& strpos($_SERVER['REQUEST_URI'],'/_configurator')!==0
	&& strpos($_SERVER['REQUEST_URI'],'/_trans')!==0
) {
	define('WP_USE_THEMES', true);
	require_once __DIR__.'/wp-load.php';
}

$sfResponse = $kernel->handle($sfRequest);

// fully send response before kernel terminates for JSON API
if ($sfResponse->headers->get('Content-Type')=='application/json') {
	if (function_exists('apache_setenv')) {
		apache_setenv('no-gzip','1');	// will be gzip'ed by nginx
	}
	$sfResponse->headers->set('Content-Length',strlen($sfResponse->getContent()));
	$sfResponse->headers->set('X-Accel-Buffering','no');
	$sfResponse->headers->set('Connection', 'close');
	$sfResponse->send();
	@ob_flush();
	flush();
} else {
	$sfResponse->send();
}

$kernel->terminate($sfRequest, $sfResponse);
