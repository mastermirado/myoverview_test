<?php

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class AuthTest extends PantherTestCase
{
    public function testLoginPageLoads(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/login');

        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
        $this->assertSelectorExists('button[type="submit"]');
    }

    public function testRegisterPageLoads(): void
    {
        $client = static::createPantherClient();
        $client->request('GET', '/register');

        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[type="email"]');
        $this->assertSelectorExists('input[type="password"]');
    }

    public function testLoginWithInvalidCredentialsShowsError(): void
    {
        $client = static::createPantherClient();
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'invalid@example.com',
            '_password' => 'wrongpassword',
        ]);
        $client->submit($form);

        $this->assertSelectorExists('.alert-danger');
    }
}
