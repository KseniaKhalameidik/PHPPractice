<?php

namespace App\Tests\Integration\Service;

use App\Service\UniversityService;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Cache\CacheItemInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class UniversityServiceTest extends KernelTestCase
{
    private ArrayAdapter $realCache;
    private HttpClientInterface|MockObject $universityClient;
    private HttpClientInterface|MockObject $api;

    #[Override]
    protected function setUp(): void
    {
        // $routerService = static::getContainer()->get('router');
        // $myCustomService = static::getContainer()->get(CustomService::class);
        //$realCache = static::getContainer()->get(CacheInterface::class);
        parent::setUp();
        $kernel = self::bootKernel();

        $this->assertSame('test', $kernel->getEnvironment());

        $this->realCache = new ArrayAdapter();
        $this->api = $this->createMock(HttpClientInterface::class);
        $this->universityClient = $this->createMock(HttpClientInterface::class);
        $this->api->expects($this->once())
            ->method('withOptions')
            ->with(['base_uri' => 'http://universities.hipolabs.com/'])
            ->willReturn($this->universityClient);
        
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->realCache->clear();
    }

    #[DataProvider('provideJson')]
    public function testGetUniversitiesWithoutCache(string $rawJson, int $magicNumber): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())
                ->method('getContent')
                ->willReturn($rawJson)
        ;
        
        $this->universityClient->expects($this->once())
            ->method('request')
            ->with(
                'GET'
            )
            ->willReturn($response)
        ;

        $universityService = new UniversityService($this->api, $this->realCache);
        $universityService->getUniversitiesByName('Far');
    }

    #[DataProvider('provideJson')]
    public function testGetUniversitiesWithCache(string $rawJson, int $magicNumber): void
    {
        $this->realCache->get('universities.Far', function (CacheItemInterface $item) use ($rawJson) {
            $item->expiresAfter(5);
            $universities = json_decode($rawJson, true);
            $item->set($universities);

            return $universities;
        });

        $this->universityClient
            ->expects($this->never())
            ->method('request')
        ;

        $universityService = new UniversityService($this->api, $this->realCache);
        $actualResult = $universityService->getUniversitiesByName('Far');
        $this->assertEquals(
            json_decode($rawJson, true),
            $actualResult
        );
    }

    public static function provideJson(): array
    {
        $rawJson = '[{"domains": ["dvgups.ru"], "state-province": null, "country": "Russian Federation", "name": "Far East State Transport University", "web_pages": ["http://www.dvgups.ru/"], "alpha_two_code": "RU"}, {"domains": ["khspu.ru"], "state-province": null, "country": "Russian Federation", "name": "Far Easten State University of Humanities", "web_pages": ["http://www.khspu.ru/"], "alpha_two_code": "RU"}, {"domains": ["dvgu.ru"], "state-province": null, "country": "Russian Federation", "name": "Far Eastern State University", "web_pages": ["http://www.dvgu.ru/"], "alpha_two_code": "RU"}, {"domains": ["festu.ru"], "state-province": null, "country": "Russian Federation", "name": "Far Eastern State Technical University", "web_pages": ["http://www.festu.ru/"], "alpha_two_code": "RU"}]';

        return [
            'firstJson' => [ $rawJson, 42 ],
            
        ];
    }
}