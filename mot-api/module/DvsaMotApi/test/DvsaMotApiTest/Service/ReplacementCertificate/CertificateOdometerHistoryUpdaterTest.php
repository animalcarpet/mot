<?php

namespace DvsaMotApiTest\Service\ReplacementCertificate;

use DvsaCommon\Auth\MotIdentityInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Enum\MotTestStatusCode;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\CertificateReplacement;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Vehicle;
use DvsaEntities\Repository\CertificateReplacementRepository;
use DvsaEntities\Repository\CertificateTypeRepository;
use DvsaEntities\Repository\MotTestHistoryRepository;
use DvsaMotApi\Helper\MysteryShopperHelper;
use DvsaMotApi\Service\CertificateCreationService;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\ReplacementCertificate\CertificateOdometerHistoryUpdater;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class CertificateOdometerHistoryUpdaterTest extends \PHPUnit_Framework_TestCase
{
    const DEFAULT_ODOMETER_VALUE = 12345;
    const DEFAULT_ODOMETER_UNIT = 'km';
    const DEFAULT_ODOMETER_RESULT_TYPE = OdometerReadingResultType::OK;
    const DEFAULT_ISSUED_DATE = '1 Jan 2010';
    const DEFAULT_MOT_TEST_NUMBER = '987654321';
    const DEFAULT_VEHICLE_ID = 345;
    const DEFAULT_USER_ID = 999;


    /** @var CertificateOdometerHistoryUpdater */
    private $sut;

    /** @var MotTestHistoryRepository | MockObject */
    private $motTestHisotryRepository;
    /** @var CertificateTypeRepository | MockObject */
    private $certificateTypeRepository;
    /** @var CertificateCreationService | MockObject */
    private $certificateCreationService;
    /** @var MotTestMapper | MockObject */
    private $motTestMapper;
    /** @var MysteryShopperHelper | MockObject */
    private $mysteryShopperHelper;
    /** @var CertificateReplacementRepository | MockObject */
    private $certificateReplacementRepository;
    /** @var MotIdentityProviderInterface | MockObject */
    private $identityProvider;

    public function setUp()
    {
        $this->motTestHisotryRepository = Xmock::of(MotTestHistoryRepository::class);
        $this->certificateTypeRepository = Xmock::of(CertificateTypeRepository::class);
        $this->certificateCreationService = Xmock::of(CertificateCreationService::class);
        $this->motTestMapper = Xmock::of(MotTestMapper::class);
        $this->mysteryShopperHelper = Xmock::of(MysteryShopperHelper::class);
        $this->certificateReplacementRepository = Xmock::of(CertificateReplacementRepository::class);
        $this->identityProvider = Xmock::of(MotIdentityProviderInterface::class);

        $this->createSUT();
    }

    private function createSUT()
    {
        $this->sut = new CertificateOdometerHistoryUpdater(
            $this->motTestHisotryRepository,
            $this->certificateTypeRepository,
            $this->certificateCreationService,
            $this->motTestMapper,
            $this->mysteryShopperHelper,
            $this->certificateReplacementRepository,
            $this->identityProvider
        );
    }

    /**
     * @dataProvider testIsOdometerModifiedDP
     *
     * @param MotTest $motTest
     * @param CertificateReplacementDraft $draft
     * @param bool $expectedResult
     */
    public function testIsOdometerModified($motTest, $draft, $expectedResult)
    {
        $result = $this->sut->isOdometerModified($motTest, $draft);

        $this->assertEquals($expectedResult, $result);
    }

    public function testIsOdometerModifiedDP()
    {
        return [
            [
                $this->generateMotTestEntity(),
                $this->generateCertificateDraft(),
                false
            ],
            [
                $this->generateMotTestEntity(54321),
                $this->generateCertificateDraft(),
                true
            ],
            [
                $this->generateMotTestEntity(),
                $this->generateCertificateDraft(54321),
                true
            ],
            [
                $this->generateMotTestEntity(null, 'mi'),
                $this->generateCertificateDraft(),
                true
            ],
            [
                $this->generateMotTestEntity(),
                $this->generateCertificateDraft(null, 'mi'),
                true
            ],
            [
                $this->generateMotTestEntity(null, null, OdometerReadingResultType::NO_ODOMETER),
                $this->generateCertificateDraft(),
                true
            ],
            [
                $this->generateMotTestEntity(),
                $this->generateCertificateDraft(null, null, OdometerReadingResultType::NO_ODOMETER),
                true
            ],
            [
                $this->generateMotTestEntity(null, 'mi', OdometerReadingResultType::OK),
                $this->generateCertificateDraft(),
                true
            ],
            [
                $this->generateMotTestEntity(),
                $this->generateCertificateDraft(null, 'mi', OdometerReadingResultType::OK),
                true
            ],
            [
                $this->generateMotTestEntity(54321, 'mi', OdometerReadingResultType::OK),
                $this->generateCertificateDraft(),
                true
            ],
            [
                $this->generateMotTestEntity(),
                $this->generateCertificateDraft(54321, 'mi', OdometerReadingResultType::OK),
                true
            ],
        ];
    }


    /**
     * @param array $motTestHistory - mot test history of a vehicle fetched from repository in chronological order
     * @param array $expectedMotTestNumbersToBeUpdated - array of arrays containing motTest numbers to be updated during the process of refreshing odometer history
     *
     * @dataProvider testUpdateOdometerHistoryDP
     */
    public function testUpdateOdometerHistory(array $motTestHistory, array $expectedMotTestNumbersToBeUpdated)
    {
        $withUserIdentity = count($expectedMotTestNumbersToBeUpdated) > 0;
        $motTestsModifiedCount = count($expectedMotTestNumbersToBeUpdated);

        $this->withUserIdentity($withUserIdentity);
        $this->withMotTestMapper($motTestsModifiedCount);

        $motTest = $this->generateMotTestEntity();
        $draft = $this->generateCertificateDraft('121212');

        // define motTestHistory returned from repository (without the original motTest) in chronological order (newest first)
        $motTestHistoryForVehicle = $this->generateMotTestHistory($motTest, $motTestHistory);
        $this->motHistoryRepoWillReturn($motTestHistoryForVehicle, $motTest);

        $this->checkIfCertificatesWereGeneratedForGivenMotTestNumbers($expectedMotTestNumbersToBeUpdated);

        $this->checkIfMotTestsWerePersisted($motTestsModifiedCount);
        $this->checkIfCertificateReplacementWerePersisted($motTestsModifiedCount);

        $this->sut->updateOdometerHistoryOnSubsequentCertificates($motTest, $draft);
    }


    public function testUpdateOdometerHistoryDP()
    {
        return [
            [
                // for all normal PASSED tests => SHOULD update 3 consecutive tests after the original test from self::DEFAULT_ISSUED_DATE = '1 Jan 2010'
                'motTestHistory' => [
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2016', 'number' => '1111111'],
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2015', 'number' => '2222222'],
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2014', 'number' => '3333333'],
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2013', 'number' => '4444444'],
                ],
                'expectedMotTestNumbersToBeUpdated' => [
                    ['4444444'],
                    ['3333333'],
                    ['2222222'],
                ]
            ],
            [
                // for mixture of PASSES/FAILURES => SHOULD update only 3 consecutive PASSED tests
                'motTestHistory' => [
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2016', 'number' => '1111111'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2015', 'number' => '2222222'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2014', 'number' => '3333333'],
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2013', 'number' => '4444444'],
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2013', 'number' => '5555555'],
                ],
                'expectedMotTestNumbersToBeUpdated' => [
                    ['5555555'],
                    ['4444444'],
                    ['1111111'],
                ]
            ],
            [
                // for mixture of PASSES/FAILURES with INVERTED_APPEAL & STATUTORY_APPEAL test types => SHOULD update only 3 consecutive PASSED tests
                'motTestHistory' => [
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::INVERTED_APPEAL, 'issuedDate' => '1 Jan 2016', 'number' => '1111111'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2015', 'number' => '2222222'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2014', 'number' => '3333333'],
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::STATUTORY_APPEAL, 'issuedDate' => '1 Jan 2013', 'number' => '4444444'],
                    [ 'status' => MotTestStatusName::PASSED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2013', 'number' => '5555555'],
                ],
                'expectedMotTestNumbersToBeUpdated' => [
                    ['5555555'],
                    ['4444444'],
                    ['1111111'],
                ]
            ],
            [
                // for all normal FAILED tests => SHOULD NOT update any tests
                'motTestHistory' => [
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2016', 'number' => '1111111'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2015', 'number' => '2222222'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2014', 'number' => '3333333'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2013', 'number' => '4444444'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2013', 'number' => '5555555'],
                ],
                'expectedMotTestNumbersToBeUpdated' => []
            ],
            [
                // for all FAILED normal/re-test tests => SHOULD NOT update any tests
                'motTestHistory' => [
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::NORMAL_TEST, 'issuedDate' => '1 Jan 2016', 'number' => '1111111'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::RE_TEST, 'issuedDate' => '1 Jan 2015', 'number' => '2222222'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::RE_TEST, 'issuedDate' => '1 Jan 2014', 'number' => '3333333'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::RE_TEST, 'issuedDate' => '1 Jan 2013', 'number' => '4444444'],
                    [ 'status' => MotTestStatusName::FAILED, 'type' => MotTestTypeCode::RE_TEST, 'issuedDate' => '1 Jan 2013', 'number' => '5555555'],
                ],
                'expectedMotTestNumbersToBeUpdated' => []
            ],
        ];
    }



    /**
     * @param int $odometerValue
     * @param string $odometerUnit
     * @param string $odometerResultType
     * @param string $statusCode
     * @param string $number
     * @param string $motTestTypeCode
     * @param string $issuedDate
     *
     * @return MotTest
     */
    private function generateMotTestEntity(
        $odometerValue = self::DEFAULT_ODOMETER_VALUE,
        $odometerUnit = self::DEFAULT_ODOMETER_UNIT,
        $odometerResultType = self::DEFAULT_ODOMETER_RESULT_TYPE,
        $statusCode = MotTestStatusCode::PASSED,
        $number = self::DEFAULT_MOT_TEST_NUMBER,
        $motTestTypeCode = MotTestTypeCode::NORMAL_TEST,
        $issuedDate = self::DEFAULT_ISSUED_DATE
    )
    {
        $motTest = new MotTest();

        $motTest->setOdometerValue($odometerValue);
        $motTest->setOdometerUnit($odometerUnit);
        $motTest->setOdometerResultType($odometerResultType);
        $motTest->setStatus($this->createMotTestStatus($statusCode));
        $motTest->setNumber($number);
        $motTest->setMotTestType($this->createMotTestType($motTestTypeCode));
        $motTest->setIssuedDate(new \DateTime($issuedDate));

        $vehicle = (new Vehicle())->setId(self::DEFAULT_VEHICLE_ID);
        $motTest->setVehicle($vehicle);

        return $motTest;
    }

    /**
     * @param int|null $odometerValue
     * @param string|null $odometerUnit
     * @param string|null $odometerResultType
     * @return CertificateReplacementDraft
     */
    private function generateCertificateDraft(
        $odometerValue = null,
        $odometerUnit = null,
        $odometerResultType = null
    )
    {
        $draft = new CertificateReplacementDraft();

        $draft->setOdometerValue($odometerValue ? $odometerValue : self::DEFAULT_ODOMETER_VALUE);
        $draft->setOdometerUnit($odometerUnit ? $odometerUnit : self::DEFAULT_ODOMETER_UNIT);
        $draft->setOdometerResultType($odometerResultType ? $odometerResultType : self::DEFAULT_ODOMETER_RESULT_TYPE);

        return $draft;
    }

    /**
     * @param array $motTestHistory
     * @param MotTest $originalMotTest
     */
    private function motHistoryRepoWillReturn(array $motTestHistory, MotTest $originalMotTest)
    {
        $this->motTestHisotryRepository
            ->expects($this->once())
            ->method('findTestsForVehicle')
            ->with(
                self::DEFAULT_VEHICLE_ID,
                $originalMotTest->getIssuedDate(),
                $this->mysteryShopperHelper
            )
            ->willReturn($motTestHistory);
    }

    /**
     * @param MotTest $motTest
     * @param array $motTestHistory
     *
     * @return array|MotTest[]
     */
    private function generateMotTestHistory(MotTest $motTest, array $motTestHistory)
    {
        // the repo is expected to return motTests in chronological order (from newest to oldest)
        // the original motTest is expected to be there as well because it fits within query condition: issuedDate >= $motTest->getIssuedDate()
        $return = [];

        foreach ($motTestHistory as $historyEntry) {
            $newMotTest = $this->generateMotTestEntity(
                '',
                '',
                '',
                $historyEntry['status'],
                $historyEntry['number'],
                $historyEntry['type'],
                $historyEntry['issuedDate']
            );

            $return[] = $newMotTest;
        }

        // add originalMotTest at the end of the list
        $return[] = $motTest;

        return $return;
    }

    /**
     * @param string $statusNameCode
     *
     * @return MotTestStatus|MockObject
     */
    private function createMotTestStatus($statusNameCode = MotTestStatusName::PASSED)
    {
        $status = XMock::of(MotTestStatus::class);
        $status->expects($this->any())
            ->method('getName')
            ->willReturn($statusNameCode);

        return $status;
    }

    /**
     * @param string $typeCode
     * @return MotTestType|MockObject
     */
    private function createMotTestType($typeCode = MotTestTypeCode::NORMAL_TEST)
    {
        $type = XMock::of(MotTestType::class);
        $type->expects($this->any())
            ->method('getCode')
            ->willReturn($typeCode);

        return $type;
    }

    private function checkIfMotTestsWerePersisted($expectedCount = 3)
    {
        // check if motTests were updated during cert generation
        $this->motTestHisotryRepository
            ->expects($this->exactly($expectedCount))
            ->method('persist')
            ->with(
                $this->isInstanceOf(MotTest::class)
            );
    }

    private function checkIfCertificateReplacementWerePersisted($expectedCount = 3)
    {
        // check if we've created/persisted appropriate amount of CertificateReplacement entities during the whole process
        $this->certificateReplacementRepository
            ->expects($this->exactly($expectedCount))
            ->method('persist')
            ->with(
                $this->isInstanceOf(CertificateReplacement::class)
            );
    }

    /**
     * @param array $motTestNumbersToBeUpdated -
     */
    private function checkIfCertificatesWereGeneratedForGivenMotTestNumbers(array $motTestNumbersToBeUpdated = [])
    {
        // check if certificates were generated only for specific motTests in the right order
        $this->certificateCreationService
            ->expects($this->exactly(count($motTestNumbersToBeUpdated)))
            ->method('create')
            ->withConsecutive(
                ...$motTestNumbersToBeUpdated
            )
        ;
    }

    private function withMotTestMapper($expectedInvocations = 3)
    {
        $motTestDtoMock = Xmock::of(MotTestDto::class);

        $this->motTestMapper
            ->expects($this->exactly($expectedInvocations))
            ->method('mapMotTest')
            ->with(
               $this->isInstanceOf(MotTest::class)
            )
            ->willReturn($motTestDtoMock);
    }

    private function withUserIdentity($shouldBeCalled = true)
    {
        $identityMock = Xmock::of(MotIdentityInterface::class);
        $identityMock
            ->expects($shouldBeCalled ? $this->atLeastOnce() : $this->never())
            ->method('getUserId')
            ->willReturn(self::DEFAULT_USER_ID);

        $this->identityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identityMock);
    }
}