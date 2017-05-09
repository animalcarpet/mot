<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Service\MotTestCompareService;

/**
 * Class MotTestCompareController.
 */
class MotTestCompareController extends AbstractDvsaRestfulController
{
    const COMPARE_VIN_DISPLAY_MESSAGE = 'The VINs for the two tests must match';

    public function __construct()
    {
        $this->setIdentifierName('motTestNumber');
    }

    public function get($motTestNumber)
    {
        $motTestData = $this->getService()->getMotTestCompareData($motTestNumber);

        return ApiResponse::jsonOk($motTestData);
    }

    public function compareMotTestAction()
    {
        $request = $this->getRequest();

        $motTestNumber = $request->getQuery('motTestNumber');
        $motTestNumberToCompare = $request->getQuery('motTestNumberToCompare');

        $motTestData = $this->getService()
            ->getMotTestCompareDataFromTwoTest($motTestNumber, $motTestNumberToCompare);

        return ApiResponse::jsonOk($motTestData);
    }

    /**
     * @return MotTestCompareService
     */
    private function getService()
    {
        return $this->getServiceLocator()->get('MotTestCompareService');
    }
}
