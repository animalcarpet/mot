<?php

namespace ApplicationTest\Service;

use Application\Factory\ApplicationWideCacheFactory;
use Application\Service\CatalogService;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommonTest\Bootstrap;

/**
 * Class CatalogServiceTest.
 */
class CatalogServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var CatalogService */
    private $service;

    public function setUp()
    {
        $fixture = json_decode(
            file_get_contents(
                __DIR__.'/../../DvsaMotEnforcementTest/Controller/fixtures/catalog.json'
            ),
            true
        );

        $client = \DvsaCommonTest\TestUtils\XMock::of(Client::class);
        $client
            ->expects($this->any())
            ->method('get')
            ->willReturn($fixture);

        $appCacheFactory = new ApplicationWideCacheFactory();
        $appCache = $appCacheFactory->createService(Bootstrap::getServiceManager());
        $this->service = new CatalogService($appCache, $client);
    }

    public function testGetters()
    {
        $this->assertCount(9, $this->service->getScores());
        $this->assertCount(3, $this->service->getDecisions());
        $this->assertCount(2, $this->service->getDecisions('NT'));
        $this->assertCount(3, $this->service->getCaseOutcomeActions());
        $this->assertCount(4, $this->service->getCategories());
    }
}
