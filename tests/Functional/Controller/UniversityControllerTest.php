<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UniversityControllerTest extends WebTestCase
{
    public function testUniversityController(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/universities/Far');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.university', 'Far');
    }
}
