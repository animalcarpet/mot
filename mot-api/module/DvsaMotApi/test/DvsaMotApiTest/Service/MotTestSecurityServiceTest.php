<?php

namespace DvsaMotApiTest\Service;

use DvsaAuthentication\Identity;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaCommonApiTest\Service\AbstractServiceTestCase;
use DvsaCommonApiTest\Stub\ConfigurationRepositoryStub;
use DvsaCommonTest\Date\TestDateTimeHolder;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\MotTestStatus;
use DvsaEntities\Entity\MotTestType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Site;
use DvsaMotApi\Service\MotTestSecurityService;
use DvsaMotApi\Service\TesterService;

/**
 * Class MotTestSecurityServiceTest.
 */
class MotTestSecurityServiceTest extends AbstractServiceTestCase
{
    const SEVEN_DAYS_ODOMETER_MODIFICATION_PERIOD = 7;

    private $entityManagerMock;
    private $testerServiceMock;
    private $motIdentityProviderMock;
    /**
     * @var ConfigurationRepositoryStub
     */
    private $configurationRepositoryStub;

    /**
     * @var MotTestSecurityService
     */
    private $service;

    /**
     * @var MotTestRepository
     */
    private $motTestRepository;

    public static function testerAssignedToVtsDataProvider()
    {
        // vtsId 4 to 6 are in vehicleTestingStations in supplied tester
        return [
            [
                'vtsId' => 3,
                'result' => false,
            ],
            [
                'vtsId' => 4,
                'result' => true,
            ],
            [
                'vtsId' => 5,
                'result' => true,
            ],
        ];
    }

    public static function testerAssignedToMotTestDataProvider()
    {
        // params: id of tester in mot-test; id of currently logged tester; expected result of function
        return [[2, 2, true], [3, 4, false]];
    }

    public function setUp()
    {
        $this->entityManagerMock = $this->getMockEntityManager();
        $this->testerServiceMock = XMock::of(TesterService::class);
        $this->configurationRepositoryStub = ConfigurationRepositoryStub::returningValue(
            self::SEVEN_DAYS_ODOMETER_MODIFICATION_PERIOD
        );
        $this->motIdentityProviderMock = XMock::of(\Zend\Authentication\AuthenticationService::class);
        $this->motIdentityProviderMock->expects($this->any())
            ->method('getIdentity')
            ->will($this->returnValue($this->getTestIdentity()));

        $this->motTestRepository = XMock::of(\DvsaEntities\Repository\MotTestRepository::class);

        $this->service = new MotTestSecurityService(
            $this->entityManagerMock,
            $this->motIdentityProviderMock,
            $this->testerServiceMock,
            $this->configurationRepositoryStub,
            $this->getMockAuthorizationService(),
            $this->motTestRepository,
            new ReadMotTestAssertion(
                $this->getMockAuthorizationService(),
                $this->motIdentityProviderMock
            )
        );
    }

    protected function getTestIdentity()
    {
        $person = $this->getMockPerson();
        $person->setId(3);
        $person->setUsername('user');
        $identity = new Identity($person);

        return $identity;
    }

    protected function getTestTesterWithVts($testerId = 3)
    {
        $tester = new Person();
        $tester->setId($testerId);
        for ($i = 4; $i <= 6; ++$i) {
            $vts = new Site();
            $vts->setId($i);
            $tester->addVehicleTestingStation($vts);
        }

        return $tester;
    }

    /**
     * @param $status
     *
     * @dataProvider dataProvider_ineditableStatuses
     */
    public function testCanModifyOdometerForTest_givenTestInIneditableStatus_shouldNotAllowToUpdate($status)
    {
        // given
        $motTestNumber = 2;
        $this->motTestRepository->expects($this->any())
            ->method('getMotTestByNumber')
            ->will($this->returnValue($this->getMotTest($motTestNumber, '2010-01-01', $status)));

        // when
        $this->currentDateIs('2010-01-03');

        $result = $this->service->canModifyOdometerForTest($motTestNumber);
        // then
        $this->assertFalse($result, 'Should not allow to update but did!');
    }

    private function currentDateIs($date)
    {
        $this->service->setDateTimeHolder(new TestDateTimeHolder(DateUtils::toDate($date)));
    }

    public function testCanModifyOdometerForTest_givenActiveStatus_shouldAllowToUpdate()
    {
        // given
        $motTestNumber = 2;
        $this->currentDateIs('2010-01-03');
        $this->motTestRepository->expects($this->any())
            ->method('getMotTestByNumber')
            ->will($this->returnValue($this->getMotTest($motTestNumber, '2010-01-01')));

        // when
        $result = $this->service->canModifyOdometerForTest($motTestNumber);

        // then
        $this->assertTrue($result, 'Should allow to update but didnt!');
    }

    public function testCanModifyOdometerForTest_givenTestCompletedAndHitModificationWindow_shouldAllowToUpdate()
    {
        // given
        $motTestNumber = 2;
        $this->currentDateIs('2010-01-08');
        $this->givenOdometerReadingModificationWindowLengthInDaysEqualTo(7);

        $this->motTestRepository->expects($this->any())
            ->method('getMotTestByNumber')
            ->will($this->returnValue($this->getMotTest($motTestNumber, '2010-01-01')));

        // when
        $result = $this->service->canModifyOdometerForTest($motTestNumber);

        // then
        $this->assertTrue($result, 'Update should be allowed but it is not!');
    }

    private function givenOdometerReadingModificationWindowLengthInDaysEqualTo($numberOfDays)
    {
        $this->configurationRepositoryStub->returnValue($numberOfDays);
    }

    public function testCanUpdate_givenTestCompletedAndMissedModificationWindow_shouldNotAllowToUpdate()
    {
        $motTestNumber = 2;

        // given
        $this->currentDateIs('2010-01-09');

        $this->motTestRepository->expects($this->any())
            ->method('getMotTestByNumber')
            ->will($this->returnValue($this->getMotTest($motTestNumber, '2010-01-01')));

        // when
        $result = $this->service->canModifyOdometerForTest($motTestNumber);

        // then
        $this->assertFalse($result, 'Should now allow but did!');
    }

    public function dataProvider_ineditableStatuses()
    {
        return [[MotTestStatusName::ABANDONED], [MotTestStatusName::ABORTED],
                [MotTestStatusName::REFUSED], ];
    }

    /**
     * @param $testNumber
     * @param $issuedDate
     * @param string $status
     *
     * @return MotTest
     *
     * @throws \DvsaCommon\Date\Exception\IncorrectDateFormatException
     */
    private function getMotTest($testNumber, $issuedDate, $status = MotTestStatusName::PASSED)
    {
        $motTest = new MotTest();
        $motTest->setNumber($testNumber)
            ->setMotTestType(new MotTestType())
            ->setStatus($this->createMotTestStatus($status))
            ->setIssuedDate(DateUtils::toDate($issuedDate));

        return $motTest;
    }

    private function createMotTestStatus($name)
    {
        $status = XMock::of(MotTestStatus::class);
        $status
            ->expects($this->any())
            ->method('getName')
            ->willReturn($name);

        return $status;
    }
}
