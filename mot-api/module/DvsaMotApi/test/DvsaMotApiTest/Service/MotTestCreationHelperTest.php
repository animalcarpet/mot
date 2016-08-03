<?php

namespace DvsaMotApiTest\Service;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\MethodSpy;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Language;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestReasonForRejection;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\ReasonForRejection;
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
        $mocks              = $motTestServiceTest->getMocksForMotTestService();

        $this->mockTesterService = $mocks['mockTesterService'];

        $mockAuthService = $mocks['mockAuthService'];
        $mockAuthService->expects($this->any())
            ->method('personHasRole')
            ->willReturn(false);

        $mockEntityManager     = $mocks['mockEntityManager'];

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

        $this->motTestCreationHelper = new MotTestCreationHelper(
            $mockEntityManager,
            $mocks['mockAuthService'],
            $this->mockTesterService,
            null,
            $mocks['mockMotTestValidator'],
            $motTestServiceTest->getMockWithDisabledConstructor(RetestEligibilityValidator::class),
            $mocks['mockOtpService'],
            $mocks['mockNewVehicleService']
        );
    }

    public function testSaveRfrsForRetest()
    {
        //given
        $rfrFail = $this->prepareMotTestRfrEntity(
            1, ReasonForRejectionTypeName::FAIL,
            'RFR fail example'
        );

        $rfrPrs = $this->prepareMotTestRfrEntity(
            1, ReasonForRejectionTypeName::PRS,
            'RFR PRS example'
        );

        $rfrAdv = $this->prepareMotTestRfrEntity(
            1, ReasonForRejectionTypeName::ADVISORY,
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
        $testerMock = Xmock::of(Person::class);
        $testerMock->expects($this->any())
            ->method('isTester')
            ->willReturn(true);

        $this->setExpectedException(ForbiddenException::class);
        $this->motTestCreationHelper->createMotTest($testerMock, 1, 1, 1, 1, 1, 1, true, 1, 1, 1, false, 1, null, '127.0.0.1', null);
    }
}
