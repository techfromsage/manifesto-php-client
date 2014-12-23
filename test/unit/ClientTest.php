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

    public function testSuccessfulRequestArchive()
    {
        /** @var \Manifesto\Client|PHPUnit_Framework_MockObject_MockObject $mockClient */
        $mockClient = $this->getMock(
            '\Manifesto\Client',
            array('getHeaders', 'getHTTPClient'),
            array('https://example.com/manifesto')
        );

        $mockClient->expects($this->once())
            ->method('getHeaders')
            ->will(
                $this->returnValue(array(
                    array(
                        'Content-Type'=>'application/json',
                        'Authorization'=>'Bearer FooToken'
                    )
                ))
            );

        $client = new \Guzzle\Http\Client('https://example.com/manifesto');
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(
            new \Guzzle\Http\Message\Response(
                202,
                null,
                json_encode(array('id'=>"12345", "status"=>"Accepted"))
                ));
        $client->addSubscriber($plugin);

        $mockClient->expects($this->once())
            ->method('getHTTPClient')
            ->will($this->returnValue($client));

        // Set this manually since it won't work from the constructor since we're mocking
        $mockClient->setManifestoBaseUrl('https://example.com/manifesto');

        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $mockClient->setPersonaConnectValues($personaOpts);

        $m = new \Manifesto\Manifest();
        $m->setFormat(FORMAT_ZIP);
        $files = array();
        $file1 = array('file'=>'/path/to/file1.txt');
        $files[] = $file1;
        $m->addFile($file1);

        $file2 = array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>'/path/to/file2.txt', 'destinationPath'=>'foobar.txt');
        $files[] = $file2;
        $m->addFile($file2);

        $file3 = array('type'=>FILE_TYPE_CF, 'file'=>'/path/to/file3.txt', 'destinationPath'=>'/another/path/foobar.txt');
        $files[] = $file3;
        $m->addFile($file3);

        /** @var \Manifesto\Archive $response */
        $response = $mockClient->requestArchive($m, 'token', 'secret');

        $this->assertInstanceOf('\Manifesto\Archive', $response);
        $this->assertEquals('12345', $response->getId());
        $this->assertEquals('Accepted', $response->getStatus());
        $this->assertEmpty($response->getLocation());
    }

    public function testGenerateUrlNotAuthorisedResponse()
    {
        $mockClient = $this->getMock(
            '\Manifesto\Client',
            array('getHeaders', 'getHTTPClient'),
            array('https://example.com/manifesto')
        );

        $mockClient->expects($this->once())
            ->method('getHeaders')
            ->will(
                $this->returnValue(array(
                    array(
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer FooToken'
                    )
                ))
            );

        $client = new \Guzzle\Http\Client('https://example.com/manifesto');
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(
            new \Guzzle\Http\Message\Response(
                401,
                null,
                json_encode(array('code'=>'Unauthorised request', 'message'=>'Client is not authorised for request'))
            ));

        $client->addSubscriber($plugin);

        $mockClient->expects($this->once())
            ->method('getHTTPClient')
            ->will($this->returnValue($client));

        // Set this manually since it won't work from the constructor since we're mocking
        $mockClient->setManifestoBaseUrl('https://example.com/manifesto');

        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $mockClient->setPersonaConnectValues($personaOpts);

        $this->setExpectedException('\Manifesto\Exceptions\UnauthorisedAccessException', 'Client is not authorised for request');
        $response = $mockClient->generateUrl('123', 'token', 'secret');
    }

    public function testGenerateUrlReturns404()
    {
        /** @var \Manifesto\Client|PHPUnit_Framework_MockObject_MockObject $mockClient */
        $mockClient = $this->getMock(
            '\Manifesto\Client',
            array('getHeaders', 'getHTTPClient'),
            array('https://example.com/manifesto')
        );

        $mockClient->expects($this->once())
            ->method('getHeaders')
            ->will(
                $this->returnValue(array(
                    array(
                        'Content-Type'=>'application/json',
                        'Authorization'=>'Bearer FooToken'
                    )
                ))
            );

        $client = new \Guzzle\Http\Client('https://example.com/manifesto');
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(
            new \Guzzle\Http\Message\Response(
                404,
                null,
                "File not found"
            ));
        $client->addSubscriber($plugin);

        $mockClient->expects($this->once())
            ->method('getHTTPClient')
            ->will($this->returnValue($client));

        // Set this manually since it won't work from the constructor since we're mocking
        $mockClient->setManifestoBaseUrl('https://example.com/manifesto');

        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $mockClient->setPersonaConnectValues($personaOpts);

        $this->setExpectedException('\Manifesto\Exceptions\GenerateUrlException', 'Missing archive');
        $response = $mockClient->generateUrl('1234', 'token', 'secret');
    }

    public function testGenerateUrlJobReadyForDownload()
    {
        /** @var \Manifesto\Client|PHPUnit_Framework_MockObject_MockObject $mockClient */
        $mockClient = $this->getMock(
            '\Manifesto\Client',
            array('getHeaders', 'getHTTPClient'),
            array('https://example.com/manifesto')
        );

        $mockClient->expects($this->once())
            ->method('getHeaders')
            ->will(
                $this->returnValue(array(
                    array(
                        'Content-Type'=>'application/json',
                        'Authorization'=>'Bearer FooToken'
                    )
                ))
            );

        $client = new \Guzzle\Http\Client('https://example.com/manifesto');
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(
            new \Guzzle\Http\Message\Response(
                200,
                null,
                json_encode(array('url'=>'https://path.to.s3/export.zip'))
            ));
        $client->addSubscriber($plugin);

        $mockClient->expects($this->once())
            ->method('getHTTPClient')
            ->will($this->returnValue($client));

        // Set this manually since it won't work from the constructor since we're mocking
        $mockClient->setManifestoBaseUrl('https://example.com/manifesto');

        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $mockClient->setPersonaConnectValues($personaOpts);

        $this->assertEquals('https://path.to.s3/export.zip', $mockClient->generateUrl('1234', 'token', 'secret'));
    }

    public function testRequestArchiveNotAuthorisedResponse()
    {
        /** @var \Manifesto\Client|PHPUnit_Framework_MockObject_MockObject $mockClient */
        $mockClient = $this->getMock(
            '\Manifesto\Client',
            array('getHeaders', 'getHTTPClient'),
            array('https://example.com/manifesto')
        );

        $mockClient->expects($this->once())
            ->method('getHeaders')
            ->will(
                $this->returnValue(array(
                    array(
                        'Content-Type'=>'application/json',
                        'Authorization'=>'Bearer FooToken'
                    )
                ))
            );

        $client = new \Guzzle\Http\Client('https://example.com/manifesto');
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(
            new \Guzzle\Http\Message\Response(
                401,
                null,
                json_encode(array('code'=>'Unauthorised request', 'message'=>'Client is not authorised for request'))
            ));
        $client->addSubscriber($plugin);

        $mockClient->expects($this->once())
            ->method('getHTTPClient')
            ->will($this->returnValue($client));

        // Set this manually since it won't work from the constructor since we're mocking
        $mockClient->setManifestoBaseUrl('https://example.com/manifesto');

        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $mockClient->setPersonaConnectValues($personaOpts);

        $m = new \Manifesto\Manifest();
        $m->setFormat(FORMAT_ZIP);
        $files = array();
        $file1 = array('file'=>'/path/to/file1.txt');
        $files[] = $file1;
        $m->addFile($file1);

        $file2 = array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>'/path/to/file2.txt', 'destinationPath'=>'foobar.txt');
        $files[] = $file2;
        $m->addFile($file2);

        $file3 = array('type'=>FILE_TYPE_CF, 'file'=>'/path/to/file3.txt', 'destinationPath'=>'/another/path/foobar.txt');
        $files[] = $file3;
        $m->addFile($file3);

        $this->setExpectedException('\Manifesto\Exceptions\UnauthorisedAccessException', 'Client is not authorised for request');
        $response = $mockClient->requestArchive($m, 'token', 'secret');
    }

    public function testRequestArchiveInvalidManifestResponse()
    {
        /** @var \Manifesto\Client|PHPUnit_Framework_MockObject_MockObject $mockClient */
        $mockClient = $this->getMock(
            '\Manifesto\Client',
            array('getHeaders', 'getHTTPClient'),
            array('https://example.com/manifesto')
        );

        $mockClient->expects($this->once())
            ->method('getHeaders')
            ->will(
                $this->returnValue(array(
                    array(
                        'Content-Type'=>'application/json',
                        'Authorization'=>'Bearer FooToken'
                    )
                ))
            );

        $client = new \Guzzle\Http\Client('https://example.com/manifesto');
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(
            new \Guzzle\Http\Message\Response(
                400,
                null,
                json_encode(array('code'=>'Invalid Manifest', 'message'=>'The Manifest is incomplete or contains invalid properties'))
            ));
        $client->addSubscriber($plugin);

        $mockClient->expects($this->once())
            ->method('getHTTPClient')
            ->will($this->returnValue($client));

        // Set this manually since it won't work from the constructor since we're mocking
        $mockClient->setManifestoBaseUrl('https://example.com/manifesto');

        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $mockClient->setPersonaConnectValues($personaOpts);

        $m = new \Manifesto\Manifest();
        $m->setFormat(FORMAT_ZIP);
        $files = array();
        $file1 = array('file'=>'/path/to/file1.txt');
        $files[] = $file1;
        $m->addFile($file1);

        $file2 = array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>'/path/to/file2.txt', 'destinationPath'=>'foobar.txt');
        $files[] = $file2;
        $m->addFile($file2);

        $file3 = array('type'=>FILE_TYPE_CF, 'file'=>'/path/to/file3.txt', 'destinationPath'=>'/another/path/foobar.txt');
        $files[] = $file3;
        $m->addFile($file3);

        $this->setExpectedException('\Manifesto\Exceptions\ManifestValidationException', 'The Manifest is incomplete or contains invalid properties');
        $response = $mockClient->requestArchive($m, 'token', 'secret');
    }

    public function testRequestArchiveReturns404()
    {
        /** @var \Manifesto\Client|PHPUnit_Framework_MockObject_MockObject $mockClient */
        $mockClient = $this->getMock(
            '\Manifesto\Client',
            array('getHeaders', 'getHTTPClient'),
            array('https://example.com/manifesto')
        );

        $mockClient->expects($this->once())
            ->method('getHeaders')
            ->will(
                $this->returnValue(array(
                    array(
                        'Content-Type'=>'application/json',
                        'Authorization'=>'Bearer FooToken'
                    )
                ))
            );

        $client = new \Guzzle\Http\Client('https://example.com/manifesto');
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(
            new \Guzzle\Http\Message\Response(
                404,
                null,
                "File not found"
            ));
        $client->addSubscriber($plugin);

        $mockClient->expects($this->once())
            ->method('getHTTPClient')
            ->will($this->returnValue($client));

        // Set this manually since it won't work from the constructor since we're mocking
        $mockClient->setManifestoBaseUrl('https://example.com/manifesto');

        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $mockClient->setPersonaConnectValues($personaOpts);

        $m = new \Manifesto\Manifest();
        $m->setFormat(FORMAT_ZIP);
        $files = array();
        $file1 = array('file'=>'/path/to/file1.txt');
        $files[] = $file1;
        $m->addFile($file1);

        $file2 = array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>'/path/to/file2.txt', 'destinationPath'=>'foobar.txt');
        $files[] = $file2;
        $m->addFile($file2);

        $file3 = array('type'=>FILE_TYPE_CF, 'file'=>'/path/to/file3.txt', 'destinationPath'=>'/another/path/foobar.txt');
        $files[] = $file3;
        $m->addFile($file3);

        $this->setExpectedException('\Manifesto\Exceptions\ArchiveException', 'Misconfigured Manifesto base url');
        $response = $mockClient->requestArchive($m, 'token', 'secret');
    }

    public function testRequestArchiveUnexpectedClientErrorResponse()
    {
        /** @var \Manifesto\Client|PHPUnit_Framework_MockObject_MockObject $mockClient */
        $mockClient = $this->getMock(
            '\Manifesto\Client',
            array('getHeaders', 'getHTTPClient'),
            array('https://example.com/manifesto')
        );

        $mockClient->expects($this->once())
            ->method('getHeaders')
            ->will(
                $this->returnValue(array(
                    array(
                        'Content-Type'=>'application/json',
                        'Authorization'=>'Bearer FooToken'
                    )
                ))
            );

        $client = new \Guzzle\Http\Client('https://example.com/manifesto');
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(
            new \Guzzle\Http\Message\Response(
                420,
                null,
                "Enhance Your Calm"
            ));
        $client->addSubscriber($plugin);

        $mockClient->expects($this->once())
            ->method('getHTTPClient')
            ->will($this->returnValue($client));

        // Set this manually since it won't work from the constructor since we're mocking
        $mockClient->setManifestoBaseUrl('https://example.com/manifesto');

        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $mockClient->setPersonaConnectValues($personaOpts);

        $m = new \Manifesto\Manifest();
        $m->setFormat(FORMAT_ZIP);
        $files = array();
        $file1 = array('file'=>'/path/to/file1.txt');
        $files[] = $file1;
        $m->addFile($file1);

        $file2 = array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>'/path/to/file2.txt', 'destinationPath'=>'foobar.txt');
        $files[] = $file2;
        $m->addFile($file2);

        $file3 = array('type'=>FILE_TYPE_CF, 'file'=>'/path/to/file3.txt', 'destinationPath'=>'/another/path/foobar.txt');
        $files[] = $file3;
        $m->addFile($file3);

        $this->setExpectedException('\Guzzle\Http\Exception\ClientErrorResponseException');
        $response = $mockClient->requestArchive($m, 'token', 'secret');
    }

    public function testRequestArchiveUnexpectedServerErrorResponse()
    {
        /** @var \Manifesto\Client|PHPUnit_Framework_MockObject_MockObject $mockClient */
        $mockClient = $this->getMock(
            '\Manifesto\Client',
            array('getHeaders', 'getHTTPClient'),
            array('https://example.com/manifesto')
        );

        $mockClient->expects($this->once())
            ->method('getHeaders')
            ->will(
                $this->returnValue(array(
                    array(
                        'Content-Type'=>'application/json',
                        'Authorization'=>'Bearer FooToken'
                    )
                ))
            );

        $client = new \Guzzle\Http\Client('https://example.com/manifesto');
        $plugin = new \Guzzle\Plugin\Mock\MockPlugin();
        $plugin->addResponse(
            new \Guzzle\Http\Message\Response(
                500,
                null,
                "Server error"
            ));
        $client->addSubscriber($plugin);

        $mockClient->expects($this->once())
            ->method('getHTTPClient')
            ->will($this->returnValue($client));

        // Set this manually since it won't work from the constructor since we're mocking
        $mockClient->setManifestoBaseUrl('https://example.com/manifesto');

        $personaOpts = array(
            'persona_host' => 'http://persona',
            'persona_oauth_route' => '/oauth/tokens',
            'tokencache_redis_host' => 'localhost',
            'tokencache_redis_port' => 6379,
            'tokencache_redis_db' => 2,
        );
        $mockClient->setPersonaConnectValues($personaOpts);

        $m = new \Manifesto\Manifest();
        $m->setFormat(FORMAT_ZIP);
        $files = array();
        $file1 = array('file'=>'/path/to/file1.txt');
        $files[] = $file1;
        $m->addFile($file1);

        $file2 = array('type'=>FILE_TYPE_S3, 'container'=>'myBucket', 'file'=>'/path/to/file2.txt', 'destinationPath'=>'foobar.txt');
        $files[] = $file2;
        $m->addFile($file2);

        $file3 = array('type'=>FILE_TYPE_CF, 'file'=>'/path/to/file3.txt', 'destinationPath'=>'/another/path/foobar.txt');
        $files[] = $file3;
        $m->addFile($file3);

        $this->setExpectedException('\Guzzle\Http\Exception\ServerErrorResponseException');
        $response = $mockClient->requestArchive($m, 'token', 'secret');
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