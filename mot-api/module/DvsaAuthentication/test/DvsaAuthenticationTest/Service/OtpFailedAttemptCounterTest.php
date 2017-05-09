<?php

namespace DvsaAuthenticationTest\Service;

use DvsaAuthentication\Service\OtpFailedAttemptCounter;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\ConfigurationRepositoryInterface;
use DvsaEntities\Repository\PersonRepository;

class OtpFailedAttemptCounterTest extends \PHPUnit_Framework_TestCase
{
    const MAX_ATTEMPTS = 3;

    /**
     * @var OtpFailedAttemptCounter
     */
    private $otpFailedAttemptCounter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $personRepository;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $configurationRepository;

    protected function setUp()
    {
        $this->personRepository = $this->getMockBuilder(PersonRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configurationRepository = $this->getMockBuilder(ConfigurationRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->configurationRepository->expects($this->any())
            ->method('getValue')
            ->with(OtpFailedAttemptCounter::CONFIG_PARAM_OTP_MAX_NUMBER_OF_ATTEMPTS)
            ->willReturn(self::MAX_ATTEMPTS);

        $this->otpFailedAttemptCounter = new OtpFailedAttemptCounter(
            $this->personRepository,
            $this->configurationRepository
        );
    }

    public function testItClearsAttemptsIfAttemptSucceeded()
    {
        $person = (new Person())->setOtpFailedAttempts(2);

        $this->expectPersonToBePersisted($person);

        $this->otpFailedAttemptCounter->attemptSucceeded($person);

        $this->assertSame(0, $person->getOtpFailedAttempts());
    }

    public function testItDoesNotClearAttemptsIfFirstAttemptSucceeded()
    {
        $person = (new Person())->setOtpFailedAttempts(0);

        $this->expectNoPersonToBePersisted();

        $this->otpFailedAttemptCounter->attemptSucceeded($person);

        $this->assertSame(0, $person->getOtpFailedAttempts());
    }

    public function testItIncrementsFailedAttempts()
    {
        $person = (new Person())->setOtpFailedAttempts(0);

        $this->expectPersonToBePersisted($person);

        $this->otpFailedAttemptCounter->attemptFailed($person);

        $this->assertSame(1, $person->getOtpFailedAttempts());
    }

    public function testItReturnsMaxAttempts()
    {
        $this->assertSame(self::MAX_ATTEMPTS, $this->otpFailedAttemptCounter->getMaxAttempts());
    }

    public function testItReturnsLeftAttempts()
    {
        $person = (new Person())->setOtpFailedAttempts(2);

        $this->assertSame(1, $this->otpFailedAttemptCounter->getLeftAttempts($person));
    }

    public function testLeftAttemptsCannotBeLowerThanZero()
    {
        $person = (new Person())->setOtpFailedAttempts(4);

        $this->assertSame(0, $this->otpFailedAttemptCounter->getLeftAttempts($person));
    }

    /**
     * @param $person
     */
    protected function expectPersonToBePersisted($person)
    {
        $this->personRepository->expects($this->once())
            ->method('persist')
            ->with($person);
        $this->personRepository->expects($this->once())
            ->method('flush')
            ->with($person);
    }

    /**
     * @param $person
     */
    protected function expectNoPersonToBePersisted()
    {
        $this->personRepository->expects($this->never())
            ->method('persist');
        $this->personRepository->expects($this->never())
            ->method('flush');
    }
}
