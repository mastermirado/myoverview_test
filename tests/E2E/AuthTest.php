<?php

namespace App\Tests\E2E;

use Symfony\Component\Panther\PantherTestCase;

class AuthTest extends PantherTestCase
{
    private static function selenium(): array
    {
        return [
            'browser' => PantherTestCase::SELENIUM,
        ];
    }

    private static function seleniumOptions(): array
    {
        return [
            'host' => $_SERVER['PANTHER_SELENIUM_HOST'] ?? 'http://selenium:4444/wd/hub',
        ];
    }

    public function testLoginPageLoads(): void
    {
        $client = static::createPantherClient(self::selenium(), [], self::seleniumOptions());
        $client->request('GET', '/login');

        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
        $this->assertSelectorExists('button[type="submit"]');
    }

    public function testRegisterPageLoads(): void
    {
        $client = static::createPantherClient(self::selenium(), [], self::seleniumOptions());
        $client->request('GET', '/register');

        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[type="email"]');
        $this->assertSelectorExists('input[type="password"]');
    }

    public function testLoginWithInvalidCredentialsShowsError(): void
    {
        $client = static::createPantherClient(self::selenium(), [], self::seleniumOptions());
        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'invalid@example.com',
            '_password' => 'wrongpassword',
        ]);
        $client->submit($form);

        $this->assertSelectorExists('.alert-danger');
    }
}
