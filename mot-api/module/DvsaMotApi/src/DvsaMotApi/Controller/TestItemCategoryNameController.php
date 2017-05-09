<?php

namespace DvsaMotApi\Controller;

use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaMotApi\Service\TestItemSelectorService;

/**
 * Class TestItemSelectorController.
 */
class TestItemCategoryNameController extends AbstractDvsaRestfulController
{
    public function __construct()
    {
        $this->setIdentifierName('motTestNumber');
    }

    public function get($motTestNumber)
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber', null);

        //  --  get mot test --
        $motTest = $this->getMotTestService()->getMotTest($motTestNumber);

        $data = $this->getTestItemSelectorService()->getCurrentNonEmptyTestItemCategoryNamesByMotTest($motTest);

        return ApiResponse::jsonOk($data);
    }

    /**
     * @return TestItemSelectorService
     */
    private function getTestItemSelectorService()
    {
        return $this->getServiceLocator()->get('TestItemSelectorService');
    }

    /**
     * @return \DvsaMotApi\Service\MotTestService
     */
    private function getMotTestService()
    {
        return $this->getServiceLocator()->get('MotTestService');
    }
}
