<?php
namespace DvsaMotApi\Controller;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Model\ApiResponse;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use Zend\View\Model\JsonModel;

/**
 * Class ReasonForRejectionController
 */
class ReasonForRejectionController extends AbstractDvsaRestfulController
{
    public function __construct()
    {
        $this->setIdentifierName('motTestNumber');
    }

    const QUERY_PARAM_SEARCH = "search";
    const QUERY_PARAM_SEARCH_START_INDEX = "start";
    const QUERY_PARAM_SEARCH_END_INDEX = "end";

    const SEARCH_REQUIRED_MESSAGE = "search query string parameter is required";

    public function get($motTestNumber)
    {
        $searchString = (string)$this->getRequest()->getQuery(self::QUERY_PARAM_SEARCH);
        $start = (int)$this->getRequest()->getQuery(self::QUERY_PARAM_SEARCH_START_INDEX);
        $end = (int)$this->getRequest()->getQuery(self::QUERY_PARAM_SEARCH_END_INDEX);

        if (!$searchString) {
            return $this->returnBadRequestResponseModel(
                self::SEARCH_REQUIRED_MESSAGE,
                self::ERROR_CODE_REQUIRED,
                self::SEARCH_REQUIRED_MESSAGE
            );
        }

        //  --  get mot test --
        /** @var MotTestDto $motTest */
        $motTest = $this->getMotTestService()->getMotTestData($motTestNumber);

        $vehicleClassCode = null;

        /** @var VehicleDto $vehicle */
        $vehicle = $motTest->getVehicle();
        if ($vehicle instanceof VehicleDto) {
            $vehicleClassCode = $vehicle->getClassCode();
        }

        //  --  get items   --
        $data = $this->getTestItemSelectorService()->searchReasonsForRejection(
            $vehicleClassCode, $searchString, $start, $end
        );

        return ApiResponse::jsonOk($data + ['motTest' => $motTest]);
    }

    /**
     * @return \DvsaMotApi\Service\TestItemSelectorService
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
