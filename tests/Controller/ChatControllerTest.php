<?php

namespace App\Tests\Controller;

use Symfony\AI\Agent\MockAgent;
use Symfony\AI\Agent\MockResponse;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ChatControllerTest extends WebTestCase
{
    public function testIndexRendersPage(): void
    {
        $client = static::createClient();
        $client->request('GET', '/chat');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form#chatForm');
    }

    public function testApiReturnsBadRequestWhenNoMessages(): void
    {
        $client = static::createClient();
        $client->request('POST', '/chat/api', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['messages' => []]));

        $this->assertResponseStatusCodeSame(400);
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
    }

    public function testApiReturnsBadRequestWhenMissingMessagesKey(): void
    {
        $client = static::createClient();
        $client->request('POST', '/chat/api', [], [], ['CONTENT_TYPE' => 'application/json'], json_encode([]));

        $this->assertResponseStatusCodeSame(400);
    }

    public function testApiCallsAgentAndReturnsResponse(): void
    {
        $client = static::createClient();

        $mockAgent = new MockAgent(['Salut' => new MockResponse('Bonjour !')]);
        static::getContainer()->set('ai.agent.default', $mockAgent);

        $body = json_encode(['messages' => [['role' => 'user', 'content' => 'Salut']]]);
        $client->request('POST', '/chat/api', [], [], ['CONTENT_TYPE' => 'application/json'], $body);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Bonjour !', $data['response']);
        $mockAgent->assertCallCount(1);
    }

    public function testApiHandlesMultiTurnConversation(): void
    {
        $client = static::createClient();

        $mockAgent = new MockAgent(['Comment ça va ?' => new MockResponse('Ça va merci !')]);
        static::getContainer()->set('ai.agent.default', $mockAgent);

        $body = json_encode(['messages' => [
            ['role' => 'user', 'content' => 'Bonjour'],
            ['role' => 'assistant', 'content' => 'Bonjour, comment puis-je vous aider ?'],
            ['role' => 'user', 'content' => 'Comment ça va ?'],
        ]]);
        $client->request('POST', '/chat/api', [], [], ['CONTENT_TYPE' => 'application/json'], $body);

        $this->assertResponseIsSuccessful();
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('Ça va merci !', $data['response']);
        $mockAgent->assertCalledWith('Comment ça va ?');
    }

    public function testApiIgnoresUnknownRoles(): void
    {
        $client = static::createClient();

        $mockAgent = new MockAgent(['Bonjour' => new MockResponse('OK')]);
        static::getContainer()->set('ai.agent.default', $mockAgent);

        $body = json_encode(['messages' => [
            ['role' => 'user', 'content' => 'Bonjour'],
            ['role' => 'system', 'content' => 'ce rôle est ignoré'],
        ]]);
        $client->request('POST', '/chat/api', [], [], ['CONTENT_TYPE' => 'application/json'], $body);

        $this->assertResponseIsSuccessful();
        $mockAgent->assertCallCount(1);
    }
}
