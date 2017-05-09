<?php

namespace DataCatalogApiTest\Controller;

use DataCatalogApi\Controller\DataCatalogController;
use DataCatalogApi\Service\DataCatalogService;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommonApiTest\Controller\AbstractRestfulControllerTestCase;

/**
 * Class DataCatalogControllerTest.
 */
class DataCatalogControllerTest extends AbstractRestfulControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new DataCatalogController();
        parent::setUp();
        $this->mockLogger();
    }

    public function testGetCanBeAccessed()
    {
        $this->mockValidAuthorization([SiteBusinessRoleCode::TESTER]);

        $mockService = $this->getMockServiceManagerClass(
            DataCatalogService::class, DataCatalogService::class
        );

        $mockedMethods = [
            'getEnforcementDecisionData',
            'getEnforcementDecisionCategoryData',
            'getEnforcementDecisionOutcomeData',
            'getEnforcementDecisionScoreData',
            'getSiteAssessmentVisitOutcomeData',
            'getReasonForSiteVisitData',
            'getBusinessRoles',
        ];
        foreach ($mockedMethods as $method) {
            $mockService->expects($this->once())
                ->method($method)
                ->will($this->returnValue(array()));
        }

        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
