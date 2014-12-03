<?php

require_once 'TestBase.php';

class ClientTest extends TestBase
{
    public function testSetGetManifestoBaseUrl()
    {
        $baseUrl = 'http://example.com/manifesto';
        $client = new \Manifesto\Client($baseUrl);
        $this->assertEquals($baseUrl, $client->getManifestoBaseUrl());
        $client->setManifestoBaseUrl('https://example.org/foobar');
        $this->assertEquals('https://example.org/foobar', $client->getManifestoBaseUrl());
    }

    public function testSetGetPersonaConnectValues()
    {
        $client = new TestManifestoClient('http://example.com/');
        $this->assertEmpty($client->getPersonaConnectValues());
        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $client->setPersonaConnectValues($personaOpts);

        $this->assertEquals($personaOpts, $client->getPersonaConnectValues());

        // Test that passing opts in constructor also sets property
        $client = new TestManifestoClient('https://example.org/', $personaOpts);
        // Make sure that we're actually looking at the right thing, since assertions are cheap
        $this->assertEquals('https://example.org/', $client->getManifestoBaseUrl());
        $this->assertEquals($personaOpts, $client->getPersonaConnectValues());
    }

    public function testSetPersonaClient()
    {
        $client = new TestManifestoClient('http://example.com/');

        $persona = new \personaclient\PersonaClient(
            array(
                'persona_host' => 'http://persona',
                'persona_oauth_route' => '/oauth/tokens',
                'tokencache_redis_host' => 'localhost',
                'tokencache_redis_port' => 6379,
                'tokencache_redis_db' => 2,
            )
        );

        $client->setPersonaClient($persona);

        $this->assertEquals($persona, $client->getPersonaClient());
    }
}

class TestManifestoClient extends \Manifesto\Client
{
    public function getPersonaConnectValues()
    {
        return $this->personaConnectValues;
    }

    public function getPersonaClient()
    {
        return parent::getPersonaClient();
    }
}