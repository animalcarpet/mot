<?php

namespace Organisation\Controller;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Organisation\Action\TestQualityInformationAction;

/**
 * Class TestQualityInformationController.
 */
class TestQualityInformationController extends AbstractDvsaMotTestController implements AutoWireableInterface
{
    /**
     * @var MotAuthorisationServiceInterface
     */
    private $authService;

    /**
     * @var TestQualityInformationAction
     */
    private $testQualityInformationAction;

    public function __construct(
        MotAuthorisationServiceInterface $authService,
        TestQualityInformationAction $testQualityInformationAction
    ) {
        $this->authService = $authService;
        $this->testQualityInformationAction = $testQualityInformationAction;
    }

    public function indexAction()
    {
        $organisationId = $this->params('id');
        $page = $this->getRequest()->getQuery('pageNumber') !== null ? $this->getRequest()->getQuery('pageNumber') : '1';

        $this->authService->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_VIEW_TEST_QUALITY, $organisationId);

        return $this->applyActionResult(
            $this->testQualityInformationAction->execute($organisationId, $page)
        );
    }
}
