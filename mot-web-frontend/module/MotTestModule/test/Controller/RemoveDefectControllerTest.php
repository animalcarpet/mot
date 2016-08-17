<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotTestTest\Controller;

use CoreTest\Controller\AbstractFrontendControllerTestCase;
use Dvsa\Mot\Frontend\MotTestModule\Controller\RemoveDefectController;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyContextProvider;
use Dvsa\Mot\Frontend\MotTestModule\View\DefectsJourneyUrlGenerator;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\MotTesting\DefectDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommon\UrlBuilder\MotTestUrlBuilder;
use DvsaCommonTest\Bootstrap;

/**
 * Class RemoveDefectControllerTest.
 */
class RemoveDefectControllerTest extends AbstractFrontendControllerTestCase
{
    /**
     * @var MotTestDto | \PHPUnit_Framework_MockObject_MockObject
     */
    private $motTestMock;

    /**
     * @var DefectsJourneyContextProvider | \PHPUnit_Framework_MockObject_MockObject
     */
    private $defectsJourneyContextProviderMock;

    /**
     * @var DefectsJourneyUrlGenerator | \PHPUnit_Framework_MockObject_MockObject
     */
    private $defectsJourneyUrlGeneratorMock;

    /**
     * @var array[]
     */
    private $reasonsForRejection;

    protected function setUp()
    {
        $motTestTypeMock = $this->getMockBuilder(MotTestTypeDto::class)->getMock();
        $motTestTypeMock->expects($this->any())
            ->method('getCode')
            ->willReturn(MotTestTypeCode::NORMAL_TEST);
        $this->motTestMock = $this->getMockBuilder(MotTestDto::class)->getMock();
        $this->motTestMock
            ->expects($this->once())
            ->method('getTestType')
            ->willReturn($motTestTypeMock);

        $this->defectsJourneyContextProviderMock = $this
            ->getMockBuilder(DefectsJourneyContextProvider::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->defectsJourneyUrlGeneratorMock = $this
            ->getMockBuilder(DefectsJourneyUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);

        $this->setServiceManager($this->serviceManager);
        $this->setController(
            new RemoveDefectController($this->defectsJourneyContextProviderMock, $this->defectsJourneyUrlGeneratorMock)
        );

        parent::setUp();
    }

    /**
     * Test that the Remove a Defect page loads correctly.
     */
    public function testRemoveActionWithGetMethod()
    {
        $motTestNumber = 1;
        $defectId = 1;

        $this->withFailuresPrsAndAdvisories(1, 2, 3)->setUpMotTestMock();
        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->at(0))
            ->method('get')
            ->willReturn(['data' => $this->motTestMock]);

        $restClientMock->expects($this->at(1))
            ->method('get')
            ->willReturn(['data' => $this->getDefectDto()]);

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'defectItemId' => $defectId,
        ];

        $this->getResultForAction2('get', 'remove', $routeParams);
        $this->assertResponseStatus(self::HTTP_OK_CODE);
    }

    /**
     * Test that POSTing to the Remove action removes a defect.
     */
    public function testRemoveActionWithPostMethod()
    {
        $motTestNumber = 1;
        $defectId = 1;

        $this->withFailuresPrsAndAdvisories(1, 2, 3)->setUpMotTestMock();
        $restClientMock = $this->getRestClientMockForServiceManager();
        $restClientMock->expects($this->at(0))
            ->method('get')
            ->willReturn(['data' => $this->motTestMock]);

        $restClientMock->expects($this->at(1))
            ->method('get')
            ->willReturn(['data' => $this->getDefectDto()]);

        $restClientMock->expects($this->once())
            ->method('delete')
            ->with(MotTestUrlBuilder::reasonForRejection($motTestNumber, $defectId));

        $this->defectsJourneyUrlGeneratorMock->method('goBack')->willReturn('user-home');

        $routeParams = [
            'motTestNumber' => $motTestNumber,
            'defectItemId' => $defectId,
        ];

        $this->getResultForAction2('post', 'remove', $routeParams);

        // We should get redirected...
        $this->assertResponseStatus(self::HTTP_REDIRECT_CODE);
    }

    private function setUpMotTestMock()
    {
        $this->motTestMock->expects($this->any())
            ->method('getReasonsForRejection')
            ->willReturn($this->reasonsForRejection);
    }

    /**
     * @param array $fail
     * @param array $prs
     * @param array $advisory
     */
    private function setUpReasonForRejectionsArrayForMotTestMock(array $fail, array $prs, array $advisory)
    {
        $this->reasonsForRejection = [
            'FAIL' => $fail,
            'PRS' => $prs,
            'ADVISORY' => $advisory,
        ];
    }

    /**
     * @param int $failures
     * @param int $prs
     * @param int $advisories
     *
     * @return $this
     */
    private function withFailuresPrsAndAdvisories($failures, $prs, $advisories)
    {
        $failArray = [];
        $prsArray = [];
        $advisoryArray = [];

        $defectId = 1;

        for ($i = 0; $i < $failures; ++$i) {
            $rfr = [];
            $rfr['type'] = ReasonForRejectionTypeName::FAIL;
            $rfr['locationLateral'] = '';
            $rfr['locationLongitudinal'] = '';
            $rfr['locationVertical'] = '';
            $rfr['comment'] = '';
            $rfr['failureDangerous'] = '';
            $rfr['testItemSelectorDescription'] = '';
            $rfr['failureText'] = '';
            $rfr['id'] = $defectId;
            $rfr['rfrId'] = '';

            $failArray[] = $rfr;
            ++$defectId;
        }

        for ($i = 0; $i < $prs; ++$i) {
            $rfr = [];
            $rfr['type'] = ReasonForRejectionTypeName::PRS;
            $rfr['locationLateral'] = '';
            $rfr['locationLongitudinal'] = '';
            $rfr['locationVertical'] = '';
            $rfr['comment'] = '';
            $rfr['failureDangerous'] = '';
            $rfr['testItemSelectorDescription'] = '';
            $rfr['failureText'] = '';
            $rfr['id'] = $defectId;
            $rfr['rfrId'] = '';

            $prsArray[] = $rfr;

            ++$defectId;
        }

        for ($i = 0; $i < $advisories; ++$i) {
            $rfr = [];
            $rfr['type'] = ReasonForRejectionTypeName::ADVISORY;
            $rfr['locationLateral'] = '';
            $rfr['locationLongitudinal'] = '';
            $rfr['locationVertical'] = '';
            $rfr['comment'] = '';
            $rfr['failureDangerous'] = '';
            $rfr['testItemSelectorDescription'] = '';
            $rfr['failureText'] = '';
            $rfr['id'] = $defectId;
            $rfr['rfrId'] = '';

            $advisoryArray[] = $rfr;
            ++$defectId;
        }

        $this->setUpReasonForRejectionsArrayForMotTestMock($failArray, $prsArray, $advisoryArray);

        return $this;
    }

    /**
     * @return DefectDto
     */
    private function getDefectDto()
    {
        $defectDto = new DefectDto();

        $defectDto->setId(1);
        $defectDto->setParentCategoryId(0);
        $defectDto->setDescription('');
        $defectDto->setDefectBreadcrumb('');
        $defectDto->setAdvisory('');
        $defectDto->setInspectionManualReference('');
        $defectDto->setAdvisory(false);
        $defectDto->setPrs(false);
        $defectDto->setFailure(false);

        return $defectDto;
    }
}
