<?php

namespace DvsaMotApiTest\Controller;

use DvsaCommon\Constants\Role;
use DvsaCommon\Enum\EnfDecisionReinspectionOutcomeId;
use DvsaMotApi\Controller\ReinspectionReportController;

/**
 * Class ReinspectionReportControllerTest.
 */
class ReinspectionReportControllerTest extends AbstractMotApiControllerTestCase
{
    protected function setUp()
    {
        $this->controller = new ReinspectionReportController();
        parent::setUp();
    }

    public function testReinspectionReportControllerCreateCanBeAccessed()
    {
        $this->mockValidAuthorization([Role::VEHICLE_EXAMINER]);

        $this->request->setMethod('post');
        $this->request->getPost()->set(
            'reinspection-outcome',
            EnfDecisionReinspectionOutcomeId::AGREED_FULLY_WITH_TEST_RESULT
        );

        $this->controller->dispatch($this->request);
        $response = $this->controller->getResponse();
        $this->assertEquals(200, $response->getStatusCode());
    }
}
