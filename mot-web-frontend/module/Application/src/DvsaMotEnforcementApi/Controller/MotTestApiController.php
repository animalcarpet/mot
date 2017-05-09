<?php

namespace DvsaMotEnforcementApi\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\HttpRestJson\Exception\RestApplicationException;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaMotEnforcement\Model\MotTest as MotTestModel;
use Zend\View\Model\JsonModel;

/**
 * Class MotTestApiController.
 */
class MotTestApiController extends AbstractAuthActionController
{
    /**
     * Fetch last 2 days of MOT test results back to front-end as JSON.
     */
    public function examinerFetchRecentMotTestDataAction()
    {
        $this->assertGranted(PermissionInSystem::DVSA_SITE_SEARCH);

        $siteNumber = (string) $this->params()->fromRoute('siteNumber', null);
        $params['siteNumber'] = $siteNumber;
        $params['format'] = 'DATA_TABLES';
        $params['sortDirection'] = 'DESC';
        $params['rowCount'] = 25000;
        $params['searchRecent'] = true;
        $results = [];

        // Fetch the vehicle testing station MOTs in question
        try {
            $apiUrl = MotTestUrlBuilder::search()->toString();
            $restResult = $this->getRestClient()->getWithParams($apiUrl, $params);
            $resultData = $restResult['data']['data'];
            $motTestModel = new MotTestModel();
            $viewRender = $this->getServiceLocator()->get('ViewRenderer');
            $viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
            $catalog = $this->getCatalogService();

            // $escapeHtml can be called as function because of its __invoke method
            $escapeHtml = $viewHelperManager->get('escapeHtml');

            if (is_array($resultData) && !empty($resultData)) {
                $preparedResultData = $motTestModel->prepareDataForVehicleExaminerListRecentMotTestsView(
                    $resultData, $viewRender, $catalog
                );
                foreach ($preparedResultData as $motTestNumber => $motTest) {
                    $results[] = [
                                    'display_date' => $escapeHtml($motTest['display_date']),
                                    'popover' => $this->getStatusPopover($motTest),  // contains html entity - don't escape.
                                    'link' => $this->createSummaryLinkAttributes($motTestNumber, $motTest['status']),
                                    'registration' => $escapeHtml($motTest['registration']),
                                    'make' => $escapeHtml($motTest['make']),
                                    'model' => $escapeHtml($motTest['model']),
                                    'display_test_type' => $escapeHtml($motTest['display_test_type']),
                                    'username' => $escapeHtml($motTest['testerUsername']),
                                    'test_date' => $motTest['test_date'],
                    ];
                }
            }
        } catch (RestApplicationException $e) {
            $this->addErrorMessages($e->getDisplayMessages());
        }

        // JSON results in DataTables required format.
        $result = new JsonModel(['aaData' => $results]);

        return $result;
    }

    private function getStatusPopover($motTest)
    {
        $response = [
            'display_status' => $motTest['display_status'],
            'popover' => $motTest['popover'],
        ];

        return $response;
    }

    protected function createSummaryLinkAttributes($motTestNumber, $status)
    {
        $response = [
            'id' => "id=\"mot-$motTestNumber\"",
            'text' => MotTestStatusName::ACTIVE === $status ? 'In progress' : 'View',
            'url' => $this->url()->fromRoute('enforcement-view-mot-test', ['motTestNumber' => $motTestNumber]),
            'status' => $status,
        ];

        return $response;
    }

    /**
     * Fetch MOT test results back to front-end as JSON.
     */
    public function examinerFetchMotTestByDateAction()
    {
        $this->assertGranted(PermissionInSystem::DVSA_SITE_SEARCH);

        $request = $this->getRequest();
        $searchType = $this->params()->fromQuery('type', 'vts');
        $data = [];
        $errorData = false;
        $apiResult = null;
        $totalResultCount = 0;
        $sEcho = '';

        if ($request->isPost()) {
            $aPosts = $request->getPost();
            $sEcho = $aPosts['sEcho'];
            try {
                $apiUrl = MotTestUrlBuilder::search()->toString();

                if ($searchType == 'tester') {
                    $params['tester'] = (string) $this->params()->fromRoute('search', 0);
                } else {
                    $params['siteNumber'] = (string) $this->params()->fromRoute('search', 0);
                }
                $params['format'] = 'DATA_TABLES';
                $params['rowCount'] = $aPosts['iDisplayLength'];
                $params['start'] = $aPosts['iDisplayStart'];
                $params['searchFilter'] = $aPosts['sSearch'];
                $params['sortColumnId'] = $aPosts['iSortCol_0'];
                $params['sortDirection'] = $aPosts['sSortDir_0'];
                $params['pageNumber'] = $sEcho;
                $params['dateFrom'] = $this->params()->fromQuery('dateFrom');
                $params['dateTo'] = $this->params()->fromQuery('dateTo');
                $apiResult = $this->getRestClient()->getWithParams(
                    $apiUrl, $params
                );
            } catch (RestApplicationException $e) {
                $this->addErrorMessages($e->getDisplayMessages());
                $errorData = $e->getExpandedErrorData();
                $this->addFormErrorMessagesToSession($e->getFormErrorDisplayMessages());
            }
            if ($apiResult) {
                if (!empty($apiResult['data']['data'])) {
                    $motTestModel = new MotTestModel();
                    $viewHelperManager = $this->getServiceLocator()->get('ViewHelperManager');
                    $viewRender = $this->getServiceLocator()->get('ViewRenderer');
                    $escapeHtml = $viewHelperManager->get('escapeHtml');
                    $catalog = $this->getCatalogService();
                    $preparedResultData = $motTestModel->prepareDataForVehicleExaminerListRecentMotTestsView(
                        $apiResult['data']['data'], $viewRender, $catalog
                    );
                    foreach ($preparedResultData as $motTestNumber => $motTest) {
                        $data[] = [
                            'display_date' => $escapeHtml($motTest['display_date']),
                            'test_date' => $escapeHtml($motTest['test_date']),
                            'status' => $escapeHtml($motTest['display_status']),
                            'vin' => $escapeHtml($motTest['vin']),
                            'registration' => $escapeHtml($motTest['registration']),
                            'link' => $this->createSummaryLinkAttributes($motTestNumber, $motTest['status']),
                            'make' => $escapeHtml($motTest['make']),
                            'model' => $escapeHtml($motTest['model']),
                            'display_test_type' => $escapeHtml($motTest['display_test_type']),
                            'site_number' => $escapeHtml($motTest['siteNumber']),
                            'username' => $escapeHtml($motTest['testerUsername']),
                        ];
                    }
                }
                if (!empty($apiResult['data']['totalResultCount'])) {
                    $totalResultCount = $apiResult['data']['totalResultCount'];
                }
            }
        }

        $result = new JsonModel(
            [
                'data' => $data,
                'sEcho' => $sEcho,
                'iTotalRecords' => $totalResultCount,
                'iTotalDisplayRecords' => $totalResultCount,
                'errorData' => $errorData,
            ]
        );

        return $result;
    }
}
