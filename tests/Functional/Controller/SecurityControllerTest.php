<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityControllerTest extends WebTestCase
{
    public function testLoginPageIsAccessible(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    public function testRedirectToLoginWhenNotAuthenticated(): void
    {
        $client = static::createClient();
        $client->request('GET', '/admin/panel');

        $this->assertResponseRedirects('/login');
    }
}
