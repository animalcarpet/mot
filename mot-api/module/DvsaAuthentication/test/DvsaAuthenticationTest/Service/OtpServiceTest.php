<?php

namespace DvsaAuthenticationTest\Service;

use DvsaAuthentication\Service\OtpFailedAttemptCounter;
use DvsaAuthentication\Service\OtpService;
use DvsaAuthentication\Service\OtpServiceAdapter;
use DvsaAuthentication\Service\PersonProvider;
use DvsaEntities\Entity\Person;

class OtpServiceTest extends \PHPUnit_Framework_TestCase
{
    const MAX_ATTEMPTS = 3;

    /**
     * @var OtpService
     */
    private $otpService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $adapter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $otpFailedAttemptCounter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $personProvider;

    protected function setUp()
    {
        $this->adapter = $this->getMockBuilder(OtpServiceAdapter::class)->disableOriginalConstructor()->getMock();

        $this->otpFailedAttemptCounter = $this->getMockBuilder(OtpFailedAttemptCounter::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->otpFailedAttemptCounter->expects($this->any())
            ->method('getMaxAttempts')
            ->willReturn(self::MAX_ATTEMPTS);

        $this->personProvider = $this->getMockBuilder(PersonProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->otpService = new OtpService($this->adapter, $this->otpFailedAttemptCounter, $this->personProvider);
    }

    /**
     * @expectedException \DvsaAuthentication\Service\Exception\OtpException
     */
    public function testItThrowsAnOtpExceptionIfTokenIsEmpty()
    {
        $this->otpService->authenticate('');
    }

    public function testItCountsASuccessfulAuthenticationAttempt()
    {
        $person = new Person();

        $this->adapter->expects($this->any())
            ->method('authenticate')
            ->with($person, '123456')
            ->willReturn(true);

        $this->otpFailedAttemptCounter->expects($this->once())
            ->method('attemptSucceeded')
            ->with($person);

        $this->personProvider->expects($this->any())
            ->method('getPerson')
            ->willReturn($person);

        $this->otpService->authenticate('123456');
    }

    /**
     * @expectedException \DvsaAuthentication\Service\Exception\OtpException
     */
    public function testItCountsAFailedAuthenticationAttempt()
    {
        $person = new Person();

        $this->adapter->expects($this->any())
            ->method('authenticate')
            ->with($person, '123456')
            ->willReturn(false);

        $this->otpFailedAttemptCounter->expects($this->once())
            ->method('attemptFailed')
            ->with($person);

        $this->personProvider->expects($this->any())
            ->method('getPerson')
            ->willReturn($person);

        $this->otpService->authenticate('123456');
    }
}
