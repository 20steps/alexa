<?php

namespace Tests\Bricks\Custom\Twentysteps\AlexaBrick\AlexaBundle;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ApiEventControllerTest extends WebTestCase
{
	public function setUp()
	{
		$this->rootDir = realpath(__DIR__.'/../../../../../../../');
		$this->fs = new Filesystem();
	}
	
	public function testEvent()
    {
        $client = static::createClient();
	
	    $client->setServerParameter('HTTP_HOST', api.alexa.localhost.com);
	    
        $client->request('GET', 'api/v1.0/event/event.json');
        $response = $client->getResponse();
	    $json = json_decode($response->getContent(),true);
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContains('success', $json['status']);
    }
}
