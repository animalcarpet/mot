<?php

namespace UserAdmin\Controller;

use DvsaClient\Mapper\DemoTestAssessmentMapper;
use DvsaClient\Mapper\PersonMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use UserAdmin\ViewModel\RecordDemoTestOutcome\DemoTestAssessment;
use Zend\View\Model\ViewModel;

class RecordDemoTestController extends AbstractDvsaMotTestController
{
    private $demoTestAssessmentMapper;

    private $authorisationService;

    private $personMapper;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationServiceInterface,
        DemoTestAssessmentMapper $demoTestAssessmentMapper,
        PersonMapper $personMapper
    ) {
        $this->demoTestAssessmentMapper = $demoTestAssessmentMapper;
        $this->authorisationService = $authorisationServiceInterface;
        $this->personMapper = $personMapper;
    }

    public function recordDemoTestAction()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::ASSESS_DEMO_TEST);

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageSubTitle', 'User profile');
        $this->layout()->setVariable('pageTitle', 'Change qualification status');

        $tester = $this->personMapper->getById($this->params()->fromRoute('personId'));
        $vehicleClassGroup = $this->params()->fromRoute('vehicleClassGroup');

        if ($this->getRequest()->isPost()) {
            $this->demoTestAssessmentMapper->createAssessment($tester->getId(), $vehicleClassGroup);

            $this->redirect()->toRoute('user_admin/user-profile', ['id' => $tester->getId()]);
        }

        $params = $this->getRequest()->getQuery()->toArray();

        return new ViewModel(['viewModel' => new DemoTestAssessment($tester, $vehicleClassGroup, $params)]);
    }
}
