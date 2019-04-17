<?php

namespace Tests\AppBundle\Security;

use AppBundle\Entity\User;
use PHPUnit\Framework\TestCase;
use AppBundle\Security\GithubUserProvider;

class GithubUserProviderTest extends TestCase
{
    private $client;

    private $response;

    private $streamedResponse;

    private $serializer;

    public function setUp()
    {
        $this->client = $this
            ->getMockBuilder('GuzzleHttp\Client')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->response = $this
            ->getMockBuilder('Psr\Http\Message\ResponseInterface')
            ->getMock();

        $this->streamedResponse = $this
            ->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();

        $this->serializer = $this
            ->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->setMethods(['deserialize'])
            ->getMock();
    }

    public function tearDown()
    {
        $this->client = null;
        $this->response = null;
        $this->streamedResponse = null;
        $this->serializer = null;
    }

    public function testLoadUserByUsernameReturningAUser()
    {
        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($this->streamedResponse);

        $this->client
            ->expects($this->once()) // Ajout expectations afin que la method get ne soit appelé qu'une seule fois.
            ->method('get')
            ->willReturn($this->response);

        $userData = array(
            'login' => "test",
            'name' => "test",
            'email' => "test@mail.com",
            'avatar_url' => "http://",
            'html_url' => "http://"
        );

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn($userData);

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

        $user = $githubUserProvider->loadUserByUsername('almat');

        $expectedUser = new User(
            $userData['login'], $userData['name'], $userData['email'], $userData['avatar_url'], $userData['html_url']
        );

        $this->assertEquals($expectedUser, $user);
        $this->assertEquals('AppBundle\Entity\User', get_class($user));
    }

    public function testLoadUserByUsernameReturningNotUser()
    {
        $this->expectException('LogicException');

        $streamedResponse = $this
            ->getMockBuilder('Psr\Http\Message\StreamInterface')
            ->getMock();

        $this->response
            ->expects($this->once())
            ->method('getBody')
            ->willReturn($streamedResponse);

        $this->client
            ->expects($this->once()) // Ajout expectations afin que la method get ne soit appelé qu'une seule fois.
            ->method('get')
            ->willReturn($this->response);

        $userData = false;

        $this->serializer
            ->expects($this->once())
            ->method('deserialize')
            ->willReturn($userData);

        $githubUserProvider = new GithubUserProvider($this->client, $this->serializer);

        $githubUserProvider->loadUserByUsername('almat');
    }
}
