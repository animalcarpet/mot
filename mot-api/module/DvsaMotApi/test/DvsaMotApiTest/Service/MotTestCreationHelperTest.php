<?php

namespace DvsaMotApiTest\Service;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Language;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestComplaintRef;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestReasonForRejectionComment;
use DvsaEntities\Entity\MotTestReasonForRejectionDescription;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaEntities\Repository\MotTestTypeRepository;
use DvsaMotApi\Service\MotTestCreationHelper;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;
use Exception;

/**
 * Class MotTestCreationHelperTest.
 */
class MotTestCreationHelperTest extends AbstractServiceTestCase
{
    const TEST_VEHICLE_ID = 1;
    const TEST_VEHICLE_TESTING_STATION_ID = 1;
    const TEST_PRIMARY_COLOUR_CODE = 1;
    const SECONDARY_COLOUR_CODE = 1;
    const TEST_FUEL_TYPE_CODE = 1;
    const TEST_CYLINDER_CAPACITY = 1200;
    const TEST_VEHICLE_CLASS_CODE = 1;
    const TEST_HAS_REGISTRATION = true;
    const TEST_MOT_TEST_TYPE_CODE = 'NT';
    const TEST_MOT_TEST_NUMBER_ORIGINAL = 1;
    const TEST_CLIENT_IP = '127.0.0.1';
    const TEST_CONTINGENCY_ID = 1;
    const TEST_CONTINGENCY_DTO = null;
    const COUNTRY_OF_REGISTRATION = 1;

    private $motTestServiceTest;

    /** @var  MotTestCreationHelper */
    private $motTestCreationHelper;

    private $mocks;

    private $mockTesterService;

    private $mockEntityManager;

    private $mockMotTestRepository;

    private $mockRetestEligibilityValidator;

    public function setUp()
    {
        $this->motTestServiceTest = new MotTestServiceTest();
        $this->mocks = $this->motTestServiceTest->getMocksForMotTestService();
        $this->mockTesterService = $this->mocks['mockTesterService'];
        $this->mockEntityManager = $this->mocks['mockEntityManager'];
        $this->mockMotTestRepository = $this->mocks['mockMotTestRepository'];
        $this->mockRetestEligibilityValidator =
            $this->motTestServiceTest->getMockWithDisabledConstructor(RetestEligibilityValidator::class);
    }

    public function testSaveRfrsForRetest()
    {
        //given
        $rfrFail = $this->prepareMotTestRfrEntity(
            1,
            (new ReasonForRejectionType())->setReasonForRejectionType(ReasonForRejectionTypeName::FAIL),
            'RFR fail example'
        );

        $rfrPrs = $this->prepareMotTestRfrEntity(
            1,
            (new ReasonForRejectionType())->setReasonForRejectionType(ReasonForRejectionTypeName::PRS),
            'RFR PRS example'
        );

        $rfrAdv = $this->prepareMotTestRfrEntity(
            1,
            (new ReasonForRejectionType())->setReasonForRejectionType(ReasonForRejectionTypeName::ADVISORY),
            'RFR Advisory example'
        );

        $motTestOrig = new MotTest();
        $motTestOrig->addMotTestReasonForRejection($rfrFail);
        $motTestOrig->addMotTestReasonForRejection($rfrPrs);
        $motTestOrig->addMotTestReasonForRejection($rfrAdv);

        $motTestNew = new MotTest();

        //when
        $this->constructMotTestCreationHelper();
        $this->motTestCreationHelper->saveRfrsForRetest($motTestOrig, $motTestNew);

        /** @var MotTestReasonForRejection[] $retestReasons */
        $retestReasons = $motTestNew->getMotTestReasonForRejections();
        $this->assertEquals(2, count($retestReasons));

        foreach ($retestReasons as $retestReason) {
            $this->assertTrue($retestReason->getType() != ReasonForRejectionTypeName::PRS);
        }
    }

    public function testSaveRfrsWithOriginalComments()
    {
        /** @var MotTestReasonForRejection $rfrFail */
        $rfrFail = XMock::of(\DvsaEntities\Entity\MotTestReasonForRejection::class);
        $rfrFail->expects($this->any())
            ->method('getMotTestReasonForRejectionComment')
            ->willReturn(true);

        $rfrFail->expects($this->any())
            ->method('getCustomDescription')
            ->willReturn(true);

        /** @var MotTestReasonForRejectionComment $rfrComment */
        $rfrComment = XMock::of(\DvsaEntities\Entity\MotTestReasonForRejectionComment::class);
        $rfrComment->expects($this->any())
            ->method('getComment')
            ->willReturn("original test comment");
        $rfrFail->expects($this->any())
            ->method('popComment')
            ->willReturn($rfrComment);

        /** @var MotTestReasonForRejectionDescription $rfrDescription */
        $rfrDescription = XMock::of(\DvsaEntities\Entity\MotTestReasonForRejectionDescription::class);
        $rfrDescription->expects($this->any())
            ->method('getCustomDescription')
            ->willReturn("original test description");
        $rfrFail->expects($this->any())
            ->method('popDescription')
            ->willReturn($rfrDescription);

        /** @var ReasonForRejectionType $rfrType */
        $rfrType = XMock::of(\DvsaEntities\Entity\ReasonForRejectionType::class);
        $rfrType->expects($this->any())
            ->method('getReasonForRejectionType')
            ->willReturn(ReasonForRejectionTypeName::FAIL);
        $rfrFail->expects($this->any())
            ->method('getType')
            ->willReturn($rfrType);

        $motTestOrig = new MotTest();
        $motTestOrig->addMotTestReasonForRejection($rfrFail);

        $motTestNew = new MotTest();

        //when
        $this->constructMotTestCreationHelper();
        $this->motTestCreationHelper->saveRfrsForRetest($motTestOrig, $motTestNew);

        //then
        /** @var MotTestReasonForRejection[] $retestReasons */
        $retestReasons = $motTestNew->getMotTestReasonForRejections();

        $this->mockEntityManager->expects($this->at(1))
            ->method('persist')
            ->with(MotTestReasonForRejectionComment::class);

        $this->mockEntityManager->expects($this->at(1))
            ->method('persist')
            ->with(MotTestReasonForRejectionDescription::class);

        $this->assertEquals(1, count($retestReasons));
    }

    public function testCreateMotTestWithNotAllowedClasses()
    {
        $this->setupMotTestTypeMocks(true, false);
        $tester = Xmock::of(Person::class);
        $tester->expects($this->any())
            ->method('isTester')
            ->willReturn(true);

        $this->setExpectedException(ForbiddenException::class);

        $this->constructMotTestCreationHelper();
        $this->callCreateMotTestWithStandardTestParams($tester);
    }

    public function testThrowsExceptionWhenNoTestTypeCodeSupplied()
    {
        $tester = Xmock::of(Person::class);
        $motTestTypeCode = null;

        $this->setExpectedException(Exception::class);

        $this->constructMotTestCreationHelper();
        $this->motTestCreationHelper->createMotTest(
            $tester,
            self::TEST_VEHICLE_ID,
            self::TEST_VEHICLE_TESTING_STATION_ID,
            self::TEST_VEHICLE_CLASS_CODE,
            self::TEST_HAS_REGISTRATION,
            $motTestTypeCode,
            self::TEST_MOT_TEST_NUMBER_ORIGINAL,
            self::TEST_CLIENT_IP,
            self::TEST_CONTINGENCY_ID,
            self::TEST_CONTINGENCY_DTO,
            $this->getTestComplaintRef()
        );
    }

    public function testThrowsExceptionWhenNoTestTypeFoundForCode()
    {
        $this->setupMotTestTypeMocks(false, self::TEST_MOT_TEST_TYPE_CODE, false);
        $tester = Xmock::of(Person::class);

        $this->setExpectedException(Exception::class);

        $mockMotTestTypeRepository = Xmock::of(MotTestTypeRepository::class, ['findOneByCode']);
        $mockMotTestTypeRepository->expects($this->any())
            ->method('findOneByCode')
            ->willReturn(false);

        $this->mockEntityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($mockMotTestTypeRepository);

        $this->constructMotTestCreationHelper();
        $this->callCreateMotTestWithStandardTestParams($tester);
    }

    public function testWhenNotATesterThrowsNotFoundException()
    {
        $this->setupMotTestTypeMocks(true, self::TEST_MOT_TEST_TYPE_CODE, false);
        $tester = Xmock::of(Person::class);
        $tester->expects($this->any())
            ->method('isTester')
            ->willReturn(false);

        $this->setExpectedException(NotFoundException::class);

        $this->constructMotTestCreationHelper();
        $this->callCreateMotTestWithStandardTestParams($tester);
    }

    public function testWhenExistingTestInProgressForVehicleThrowsBadRequestException()
    {
        $this->setupMotTestTypeMocks(true, self::TEST_MOT_TEST_TYPE_CODE, false);
        $tester = Xmock::of(Person::class);
        $tester->expects($this->any())
            ->method('isTester')
            ->willReturn(true);

        $this->mockTesterService->expects($this->any())
            ->method('verifyTesterAllowedToTestClass')
            ->willReturn(true);

        $this->mockMotTestRepository->expects($this->any())
            ->method('isTestInProgressForVehicle')
            ->willReturn(true);

        $this->setExpectedException(BadRequestException::class);

        $this->constructMotTestCreationHelper();
        $this->callCreateMotTestWithStandardTestParams($tester);
    }

    public function testWhenExistingTesterHasTestInProgressThrowsBadRequestException()
    {
        $this->setupMotTestTypeMocks(true, self::TEST_MOT_TEST_TYPE_CODE, false);
        $tester = Xmock::of(Person::class);
        $tester->expects($this->any())
            ->method('isTester')
            ->willReturn(true);

        $this->mockTesterService->expects($this->any())
            ->method('verifyTesterAllowedToTestClass')
            ->willReturn(true);

        $this->mockMotTestRepository->expects($this->any())
            ->method('isTestInProgressForVehicle')
            ->willReturn(false);

        $this->mockMotTestRepository->expects($this->any())
            ->method('findInProgressTestNumberForPerson')
            ->willReturn(true);

        $this->setExpectedException(BadRequestException::class);

        $this->constructMotTestCreationHelper();
        $this->callCreateMotTestWithStandardTestParams($tester);
    }

    public function testWhenExistingTesterHasDemoTestInProgressThrowsBadRequestException()
    {
        $this->setupMotTestTypeMocks(true, self::TEST_MOT_TEST_TYPE_CODE, true);
        $tester = Xmock::of(Person::class);
        $tester->expects($this->any())
            ->method('isTester')
            ->willReturn(true);

        $this->mockTesterService->expects($this->any())
            ->method('verifyTesterAllowedToTestClass')
            ->willReturn(true);

        $this->mockMotTestRepository->expects($this->any())
            ->method('findInProgressDemoTestNumberForPerson')
            ->willReturn(true);

        $this->setExpectedException(BadRequestException::class);

        $this->constructMotTestCreationHelper();
        $this->callCreateMotTestWithStandardTestParams($tester);
    }
    
    /**
     * @param $id
     * @param $type
     * @param $name
     *
     * @return \DvsaEntities\Entity\MotTestReasonForRejection
     */
    protected function prepareMotTestRfrEntity($id, $type, $name)
    {
        $rfr = new ReasonForRejection();
        $rfr->setDescriptions(
            new ArrayCollection(
                [
                    (new \DvsaEntities\Entity\ReasonForRejectionDescription())
                        ->setLanguage((new Language())->setCode('EN'))
                        ->setName($name),
                ]
            )
        );

        $motTestRfr = new MotTestReasonForRejection();
        $motTestRfr->setId($id);
        $motTestRfr->setType($type);
        $motTestRfr->setReasonForRejection($rfr);
        return $motTestRfr;
    }

    private function setupMotTestTypeMocks($setupMockTestType, $testType, $isDemo = null)
    {
        $mockMotTestType = null;
        if ($setupMockTestType) {
            $mockMotTestType = Xmock::of(MotTestType::class);
            $mockMotTestType->expects($this->any())
                ->method('getIsDemo')
                ->willReturn($isDemo);

            $mockMotTestType->expects($this->any())
                ->method('getCode')
                ->willReturn($testType);
        }

        $mockMotTestTypeRepository = Xmock::of(MotTestTypeRepository::class, ['findOneByCode']);
        $mockMotTestTypeRepository->expects($this->any())
            ->method('findOneByCode')
            ->willReturn($mockMotTestType);

        $this->mockEntityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($mockMotTestTypeRepository);
    }

    private function constructMotTestCreationHelper()
    {
        $this->motTestCreationHelper = new MotTestCreationHelper(
            $this->mockEntityManager,
            $this->mocks['mockAuthService'],
            $this->mockTesterService,
            $this->mockMotTestRepository,
            $this->mocks['mockMotTestValidator'],
            $this->mockRetestEligibilityValidator,
            XMock::of(MotIdentityProviderInterface::class),
            $this->mocks['mockNewVehicleService']
        );
    }

    /**
     * @return $this
     */
    private function getTestComplaintRef()
    {
        return (new MotTestComplaintRef())->setComplaintRef(1);
    }

    /**
     * @param $tester
     * @throws Exception
     * @throws NotFoundException
     */
    private function callCreateMotTestWithStandardTestParams($tester)
    {
        $this->motTestCreationHelper->createMotTest(
            $tester,
            self::TEST_VEHICLE_ID,
            self::TEST_VEHICLE_TESTING_STATION_ID,
            self::TEST_VEHICLE_CLASS_CODE,
            self::TEST_HAS_REGISTRATION,
            self::TEST_MOT_TEST_TYPE_CODE,
            self::TEST_MOT_TEST_NUMBER_ORIGINAL,
            self::TEST_CLIENT_IP,
            self::TEST_CONTINGENCY_ID,
            self::TEST_CONTINGENCY_DTO,
            $this->getTestComplaintRef()
        );
    }
}
