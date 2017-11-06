<?php
namespace DvsaMotApiTest\Service\ReasonForRejection;

use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\RfrDeficiencyCategoryCode;
use DvsaCommon\Enum\VehicleClassCode;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionInterface;
use DvsaCommon\ReasonForRejection\SearchReasonForRejectionResponseInterface;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Service\ReasonForRejection\SearchReasonForRejectionService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaEntities\Repository\RfrRepository;

class SearchReasonForRejectionServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var  AuthorisationServiceInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $authService;
    /** @var  RfrRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $rfrRepository;

    /** @var SearchReasonForRejectionService  */
    private $sut;

    protected function setUp()
    {
        $this->authService = XMock::of(AuthorisationServiceInterface::class);
        $this->rfrRepository = XMock::of(RfrRepository::class);

        $this->sut = new SearchReasonForRejectionService($this->authService, $this->rfrRepository);
    }

    /**
     * @expectedException \Exception
     */
    public function testSearchReasonForRejectionService_throwsErrors_whenTesterTriesRetrieveRfrWithVEflag()
    {
        $this
            ->authService
            ->expects($this->at(0))
            ->method("assertGranted")
            ->with(PermissionInSystem::RFR_LIST)
        ;

        $this
            ->authService
            ->expects($this->at(1))
            ->method("assertGranted")
            ->with(PermissionInSystem::VE_RFR_ITEMS_NOT_TESTED)
            ->willThrowException(new \Exception())
        ;

        $this->sut->search("brake", VehicleClassCode::CLASS_4, SearchReasonForRejectionInterface::VEHICLE_EXAMINER_ROLE_FLAG, 1);
    }

    public function testSearchReasonForRejectionService_returnReasonForRejectionResponse()
    {
        $this->rfrRepository->method("count")->willReturn(15);
        $this->rfrRepository->method("findBySearchQuery")->willReturn($this->getReasonForRejectionArrayData());

        $response = $this->sut->search("brake", VehicleClassCode::CLASS_4, SearchReasonForRejectionInterface::VEHICLE_EXAMINER_ROLE_FLAG, 1);

        $this->assertInstanceOf(SearchReasonForRejectionResponseInterface::class, $response);
    }

    private function getReasonForRejectionArrayData()
    {
        return [
            [
                "rfrId" => 1,
                "testItemSelectorId" => 3,
                "description" => "description",
                "testItemSelectorName" => "selector name",
                "advisoryText" => "advisory",
                "inspectionManualReference" => "manual refernce",
                "isAdvisory" => "1",
                "isPrsFail" => "0",
                "vehicleClasses" => join(",", [VehicleClassCode::CLASS_4, VehicleClassCode::CLASS_5]),
                "minorItem" => "1",
                "locationMarker" => "1",
                "qtMarker" => "1",
                "note" => "1",
                "canBeDangerous" => "1",
                "specProc" => "1",
                "categoryName" => "category name",
                "categoryDescription" => "category description",
                "deficiencyCategoryCode" => RfrDeficiencyCategoryCode::PRE_EU_DIRECTIVE
            ]
        ];
    }
}
