<?php

namespace UserAdmin\Controller;

use Core\Controller\AbstractDvsaActionController;
use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use UserAdmin\Service\DemoTestRequestService;
use Zend\View\Model\ViewModel;

class DemoTestRequestsController extends AbstractDvsaActionController implements AutoWireableInterface
{
    protected $breadcrumbs = [
        'Demo test requests' => '',
    ];
    private $authorisationService;
    private $demoTestRequestService;
    private $qualificationDetailsMapper;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        QualificationDetailsMapper $qualificationDetailsMapper,
        DemoTestRequestService $demoTestRequestService
    ) {
        $this->authorisationService = $authorisationService;
        $this->demoTestRequestService = $demoTestRequestService;
        $this->qualificationDetailsMapper = $qualificationDetailsMapper;
    }

    public function indexAction()
    {
        $this->assertUserCanViewDemoTestRequests();
        $this->configureLayout();
        $sortParams = $this->demoTestRequestService->getSortParams($this->getRequest()->getQuery());
        $users = $this->qualificationDetailsMapper->getDemoTestRequests($sortParams);
        $table = $this->demoTestRequestService->getGdsTable($users, $sortParams);
        $sortParamsCsv = $this->demoTestRequestService->getSortParamsForCsv($this->getRequest()->getQuery());

        return new ViewModel([
            'table' => $table,
            'downloadCsvLink' => $this->url()->fromRoute(
                'user_admin/demo-test-requests/download-csv',
                [],
                ['query' => $sortParamsCsv->toQueryParams()->toArray()]
            ),
        ]);
    }

    public function downloadCsvAction()
    {
        $this->assertUserCanViewDemoTestRequests();
        $sortParamsForCsv = $this->demoTestRequestService->getSortParamsForCsv($this->getRequest()->getQuery());
        $users = $this->qualificationDetailsMapper->getDemoTestRequests($sortParamsForCsv);

        return $this->demoTestRequestService->getCsvResponse($users, $this->getResponse());
    }

    private function configureLayout()
    {
        $this->layout('layout/layout-govuk.phtml');
        $this->setBreadcrumbs(['breadcrumbs' => $this->breadcrumbs]);
    }

    protected function assertUserCanViewDemoTestRequests()
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_USERS_IN_DEMO_TEST_NEEDED_STATE);
    }
}
