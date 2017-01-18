<?php

namespace DvsaMotApiTest\Service;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaAuthentication\Identity;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Language;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestComplaintRef;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\ReasonForRejection;
use DvsaEntities\Entity\ReasonForRejectionType;
use DvsaEntities\Repository\MotTestTypeRepository;
use DvsaMotApi\Service\MotTestCreationHelper;
use DvsaMotApi\Service\Validator\RetestEligibility\RetestEligibilityValidator;

/**
 * Class MotTestCreationHelperTest.
 */
class MotTestCreationHelperTest extends AbstractServiceTestCase
{
    /** @var  MotTestCreationHelper */
    private $motTestCreationHelper;
    private $mockTesterService;

    public function setUp()
    {
        $motTestServiceTest = new MotTestServiceTest();
        $mocks = $motTestServiceTest->getMocksForMotTestService();

        $this->mockTesterService = $mocks['mockTesterService'];

        $mockAuthService = $mocks['mockAuthService'];
        $mockAuthService->expects($this->any())
            ->method('personHasRole')
            ->willReturn(false);

        $mockEntityManager = $mocks['mockEntityManager'];

        $mockMotTestType = Xmock::of(MotTestType::class);
        $mockMotTestType->expects($this->any())
            ->method('getIsDemo')
            ->willReturn(false);

        $mockMotTestTypeRepository = Xmock::of(MotTestTypeRepository::class, ['findOneByCode']);
        $mockMotTestTypeRepository->expects($this->any())
            ->method('findOneByCode')
            ->willReturn($mockMotTestType);

        $mockEntityManager->expects($this->any())
            ->method('persist')
            ->will($this->returnValue(null));

        $mockEntityManager->expects($this->any())
            ->method('getRepository')
            ->willReturn($mockMotTestTypeRepository);

        $identityMock = XMock::of(Identity::class);
        $identityMock
            ->expects($this->any())
            ->method('isSecondFactorRequired')
            ->willReturn(true);

        $mockIdentityProvider = XMock::of(MotIdentityProviderInterface::class);
        $mockIdentityProvider
            ->expects($this->any())
            ->method('getIdentity')
            ->willReturn($identityMock);

        $this->motTestCreationHelper = new MotTestCreationHelper(
            $mockEntityManager,
            $mocks['mockAuthService'],
            $this->mockTesterService,
            null,
            $mocks['mockMotTestValidator'],
            $motTestServiceTest->getMockWithDisabledConstructor(RetestEligibilityValidator::class),
            $mocks['mockOtpService'],
            $mockIdentityProvider,
            $mocks['mockNewVehicleService']
        );
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
        $this->motTestCreationHelper->saveRfrsForRetest($motTestOrig, $motTestNew);

        /** @var MotTestReasonForRejection[] $retestReasons */
        $retestReasons = $motTestNew->getMotTestReasonForRejections();
        $this->assertEquals(2, count($retestReasons));

        foreach ($retestReasons as $retestReason) {
            $this->assertTrue($retestReason->getType() != ReasonForRejectionTypeName::PRS);
        }
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

    public function testCreateMotTestWithNotAllowedClasses()
    {
        $tester = Xmock::of(Person::class);
        $tester->expects($this->any())
            ->method('isTester')
            ->willReturn(true);

        $this->setExpectedException(ForbiddenException::class);

        $vehicleId = 1;
        $vehicleTestingStationId = 1;
        $primaryColourCode = 1;
        $secondaryColourCode = 1;
        $fuelTypeCode = 1;
        $vehicleClassCode = 1;
        $hasRegistration = true;
        $motTestTypeCode = 'NT';
        $motTestNumberOriginal = 1;
        $oneTimePassword = '123456';
        $clientIp = '127.0.0.1';
        $contingencyId = 1;
        $contingencyDto = null;
        $complaintRef = (new MotTestComplaintRef())->setComplaintRef(1);


        $this->motTestCreationHelper->createMotTest(
            $tester,
            $vehicleId,
            $vehicleTestingStationId,
            $primaryColourCode,
            $secondaryColourCode,
            $fuelTypeCode,
            $vehicleClassCode,
            $hasRegistration,
            $motTestTypeCode,
            $motTestNumberOriginal,
            $oneTimePassword,
            $clientIp,
            $contingencyId,
            $contingencyDto,
            $complaintRef
        );
    }
}
