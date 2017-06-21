<?php

namespace DvsaMotApi\Controller;

use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommonApi\Controller\AbstractDvsaRestfulController;
use DvsaCommonApi\Model\ApiResponse;
use DvsaMotApi\Service\MotTestService;
use DvsaMotApi\Service\TestItemSelectorService;
use Zend\View\Model\JsonModel;

/**
 * Class ReasonForRejectionController.
 */
class ReasonForRejectionController extends AbstractDvsaRestfulController
{
    private $testItemSelectorService;
    private $motTestService;

    public function __construct(TestItemSelectorService $testItemSelectorService, MotTestService $motTestService)
    {
        $this->setIdentifierName('motTestNumber');
        $this->testItemSelectorService = $testItemSelectorService;
        $this->motTestService = $motTestService;
    }

    const QUERY_PARAM_SEARCH = 'search';

    const SEARCH_REQUIRED_MESSAGE = 'search query string parameter is required';

    /**
     * Search for defects during an MOT test using the "search" parameter.
     *
     * @param mixed $motTestNumber
     *
     * @return JsonModel
     */
    public function get($motTestNumber)
    {
        $searchString = (string) $this->getRequest()->getQuery(self::QUERY_PARAM_SEARCH);

        if (!$searchString) {
            return $this->returnBadRequestResponseModel(
                self::SEARCH_REQUIRED_MESSAGE,
                self::ERROR_CODE_REQUIRED,
                self::SEARCH_REQUIRED_MESSAGE
            );
        }

        //  --  get mot test --
        /** @var MotTestDto $motTest */
        $motTest = $this->motTestService->getMotTestData($motTestNumber);

        $vehicleClassCode = null;

        /** @var VehicleDto $vehicle */
        $vehicle = $motTest->getVehicle();
        if ($vehicle instanceof VehicleDto) {
            $vehicleClassCode = $vehicle->getClassCode();
        }

        //  --  get items   --
        $data = $this->testItemSelectorService->searchReasonsForRejection($vehicleClassCode, $searchString);

        return ApiResponse::jsonOk($data + ['motTest' => $motTest]);
    }
}
