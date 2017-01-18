<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace MotTestResultTest\TransportForLondon\Service;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Make;
use DvsaEntities\Entity\Model;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Repository\MotTestRepository;
use IntegrationApi\TransportForLondon\Mapper\TransportForLondonMotTestMapper as Mapper;
use IntegrationApi\TransportForLondon\Service\TransportForLondonMotTestService;

class TransportForLondonMotTestServiceTest extends AbstractServiceTestCase
{
    const TEST_RESULT_KEY = 'testResult';
    const EXPIRED_WARNING_KEY = 'expiredWarning';
    const LATER_TEST_IN_SCOPE_KEY = 'laterTestInScope';
    const LATER_TEST_OUT_SCOPE_KEY = 'laterTestOutScope';

    const SHORT_STATUS_PASS = 'P';
    const SHORT_STATUS_FAIL = 'F';

    /**
     * @var TransportForLondonMotTestService
     */
    private $underTest;
    private $mockRepository;

    protected function setUp()
    {
        $this->mockRepository = $this->getMockWithDisabledConstructor(MotTestRepository::class);
        $this->underTest = new TransportForLondonMotTestService($this->mockRepository);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function test_givenMotTestNotFound_throwAnException()
    {
        //given
        $this->mockRepositoryNoTestsFound();

        //when
        $this->underTest->getMotTest($this->anything(), $this->anything());

        //then an exception is thrown
        $this->assertTrue(false, "NotFoundException should be thrown.");
    }

    public function test_givenExpiredPassFoundAndNoLaterInScopeAndNoLaterOutScope_shouldSetAppropriateFlags()
    {
        //given
        $this->mockRepositoryFindPassAndSetExpired(true);

        //when
        $result = $this->underTest->getMotTest($this->anything(), $this->anything());

        //then
        $this->assertEquals(self::SHORT_STATUS_PASS, $result[self::TEST_RESULT_KEY]);
        $this->assertEquals(Mapper::FLAG_YES, $result[self::EXPIRED_WARNING_KEY]);
        $this->assertEquals(Mapper::FLAG_NO, $result[self::LATER_TEST_IN_SCOPE_KEY]);
        $this->assertEquals(Mapper::FLAG_NO, $result[self::LATER_TEST_OUT_SCOPE_KEY]);
    }

    public function test_givenUnexpiredPassFoundAndNoLaterInScopeAndNoLaterOutScope_shouldSetAppropriateFlags()
    {
        //given
        $this->mockRepositoryFindPassAndSetExpired(false);

        //when
        $result = $this->underTest->getMotTest($this->anything(), $this->anything());

        //then
        $this->assertEquals(self::SHORT_STATUS_PASS, $result[self::TEST_RESULT_KEY]);
        $this->assertEquals(Mapper::FLAG_NO, $result[self::EXPIRED_WARNING_KEY]);
        $this->assertEquals(Mapper::FLAG_NO, $result[self::LATER_TEST_IN_SCOPE_KEY]);
        $this->assertEquals(Mapper::FLAG_NO, $result[self::LATER_TEST_OUT_SCOPE_KEY]);
    }

    public function test_givenUnexpiredPassFoundAndLaterInScope_shouldSetAppropriateFlags()
    {
        //given
        $this->mockRepositoryFindPassAndSetExpired(false);
        $this->mockRepositoryFindNonPass(MotTestStatusName::FAILED, 2);

        //when
        $result = $this->underTest->getMotTest($this->anything(), $this->anything());

        //then
        $this->assertEquals(self::SHORT_STATUS_PASS, $result[self::TEST_RESULT_KEY]);
        $this->assertEquals(Mapper::FLAG_NO, $result[self::EXPIRED_WARNING_KEY]);
        $this->assertEquals(Mapper::FLAG_YES, $result[self::LATER_TEST_IN_SCOPE_KEY]);
        $this->assertEquals(Mapper::FLAG_NO, $result[self::LATER_TEST_OUT_SCOPE_KEY]);
    }

    public function test_givenExpiredPassFoundAndLaterOutScope_shouldSetAppropriateFlags()
    {
        //given
        $this->mockRepositoryFindPassAndSetExpired(true);
        $this->mockRepositoryThereIsTestWithDifferentV5cReference();

        //when
        $result = $this->underTest->getMotTest($this->anything(), $this->anything());

        //then
        $this->assertEquals(self::SHORT_STATUS_PASS, $result[self::TEST_RESULT_KEY]);
        $this->assertEquals(Mapper::FLAG_YES, $result[self::EXPIRED_WARNING_KEY]);
        $this->assertEquals(Mapper::FLAG_NO, $result[self::LATER_TEST_IN_SCOPE_KEY]);
        $this->assertEquals(Mapper::FLAG_YES, $result[self::LATER_TEST_OUT_SCOPE_KEY]);
    }

    public function test_givenFailFoundAndLaterOutScope_shouldSetAppropriateFlags()
    {
        //given
        $this->mockRepositoryFindNonPass(MotTestStatusName::FAILED);
        $this->mockRepositoryThereIsTestWithDifferentV5cReference();

        //when
        $result = $this->underTest->getMotTest($this->anything(), $this->anything());

        //then
        $this->assertEquals(self::SHORT_STATUS_FAIL, $result[self::TEST_RESULT_KEY]);
        $this->assertEquals(Mapper::FLAG_NO, $result[self::EXPIRED_WARNING_KEY]);
        $this->assertEquals(Mapper::FLAG_NA, $result[self::LATER_TEST_IN_SCOPE_KEY]);
        $this->assertEquals(Mapper::FLAG_YES, $result[self::LATER_TEST_OUT_SCOPE_KEY]);
    }

    private function mockRepositoryNoTestsFound()
    {
        $this->mockRepository->expects($this->once())
            ->method('findLastPass')
            ->will($this->returnValue(false));
        $this->mockRepository->expects($this->once())
            ->method('findNonPassIssuedAfter')
            ->will($this->returnValue(false));
    }

    private function mockRepositoryFindPassAndSetExpired($makeMotTestExpired)
    {
        $pass = $this->createEmptyMotTest();
        $pass->setStatus($this->createMotTestStatus(MotTestStatusName::PASSED));

        if ($makeMotTestExpired) {
            $expiryDate = (new \DateTime())->modify('-7 day'); // week ago
        } else {
            $expiryDate = (new \DateTime())->modify('+7 day'); // nex week
        }

        $pass->setExpiryDate($expiryDate);

        $this->mockRepository->expects($this->once())
            ->method('findLastPass')
            ->will($this->returnValue($pass));
    }

    private function mockRepositoryFindNonPass($statusForTest, $methodCalledTimes = 1)
    {
        if (MotTestStatusName::PASSED === $statusForTest) {
            $this->assertTrue(false, "Not a Pass - that's the purpose of this helper method!");
        }

        $fail = $this->createEmptyMotTest();
        $fail->setStatus($this->createMotTestStatus($statusForTest));

        $this->mockRepository->expects($this->exactly($methodCalledTimes))
            ->method('findNonPassIssuedAfter')
            ->will($this->returnValue($fail));
    }

    private function mockRepositoryThereIsTestWithDifferentV5cReference()
    {
        $this->mockRepository->expects($this->once())
            ->method('isAnyWithDifferentV5cReferenceIssuedAfter')
            ->will($this->returnValue(true));
    }

    private function createEmptyMotTest()
    {
        $vts = new Site();
        $contactDetail = new ContactDetail();
        $contactDetail->addPhone(new Phone());

        $vts->setContact($contactDetail, (new SiteContactType()));

        $motTest = new MotTest();
        $motTest->setVehicleTestingStation($vts);
        $motTest->setOdometerResultType(OdometerReadingResultType::NO_ODOMETER);

        return $motTest;
    }

    private function createMotTestStatus($name)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method("getName")
            ->willReturn($name);

        return $status;
    }
}
