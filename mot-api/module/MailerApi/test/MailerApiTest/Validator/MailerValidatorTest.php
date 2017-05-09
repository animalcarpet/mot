<?php

namespace MailerApiTest\Validator;

use DvsaCommon\Dto\Mailer\MailerDto;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaMotApi\Service\UserService;
use MailerApi\Service\MailerService;
use MailerApi\Validator\MailerValidator;
use MailerApiTest\Mixin\ServiceManager;
use PHPUnit_Framework_TestCase;

class MailerValidatorTest extends PHPUnit_Framework_TestCase
{
    use ServiceManager;

    protected $validator;
    protected $mockUserService;

    public function setUp()
    {
        $this->prepServiceManager();

        $this->mockUserService = $this->setMockServiceClass(UserService::class, ['findPerson']);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testFailsIfUseridNotInDtoData()
    {
        $dto = new MailerDto();
        $dto->setData(['userid-not-here' => 0]);
        $this->withRealEmailValidator();
        $this->validator->validate($dto, '');
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testFailsWithUnrecognisedReminderType()
    {
        $dto = new MailerDto();
        $dto->setData(['userid' => 5]);
        $this->withRealEmailValidator();

        $this->validator->validate($dto, 999);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testFailsWithMissingUseridInDtoForUsername()
    {
        $dto = new MailerDto();
        $dto->setData(['userid-not-here' => 5]);
        $this->withRealEmailValidator();
        $this->validator->validate($dto, MailerValidator::TYPE_REMIND_USERNAME);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testFailsWithMissingUseridInDtoForPassword()
    {
        $dto = new MailerDto();
        $dto->setData(['userid-not-here' => 5]);
        $this->withRealEmailValidator();
        $this->validator->validate($dto, MailerValidator::TYPE_REMIND_PASSWORD);
    }

    public function testOkWithMissingUseridInDtoForPassword()
    {
        $dto = new MailerDto();
        $dto->setData(['userid' => 5]);
        $this->withRealEmailValidator();
        $this->validator->validate($dto, MailerValidator::TYPE_REMIND_PASSWORD);
    }

    public function testValidatesOkWithGoodUserIdPresentAndCorrect()
    {
        $dto = new MailerDto();
        $dto->setData(['userid' => 5]);

        $this->ensureUserIdGood(5);
        $this->withRealEmailValidator();
        $this->assertTrue(
            $this->validator->validate(
                $dto, MailerValidator::TYPE_REMIND_USERNAME
            )
        );
    }

    public function testGivenCorrectValuesArePassed_whenValidatingCustomerCertificate_shouldReturnTrue()
    {
        $dto = new MailerDto();
        $dto->setData([
            'email' => MailerService::AWS_MAIL_SIMULATOR_SUCCESS,
            'firstName' => 'some name',
            'familyName' => 'familyName',
            'attachment' => 'dummy attachment',
        ]);
        $this->withMockEmailValidator();
        $this->assertTrue($this->validator->validate($dto, MailerValidator::TYPE_CUSTOMER_CERTIFICATE));
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testGivenEmailIsTooLong_whenValidatingCustomerCertificate_shouldThrowBadRequestException()
    {
        $dto = new MailerDto();
        $dto->setData([
            'email' => MailerService::getTestEmailAddress('mailervalidatortestemailtoolonglonglonglonglonglonglonglonglonglonglonglonglonglonglonglong@'),
            'firstName' => 'some name',
            'familyName' => 'familyName',
            'attachment' => 'dummy attachment',
        ]);
        $this->withRealEmailValidator();
        $this->validator->validate($dto, MailerValidator::TYPE_CUSTOMER_CERTIFICATE);
    }

    /**
     * @expectedException \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function testGivenNullValuesArePassed_whenValidatingCustomerCertificate_shouldThrowBadRequestException()
    {
        $dto = new MailerDto();
        $dto->setData([]);
        $this->withRealEmailValidator();

        $this->validator->validate($dto, MailerValidator::TYPE_CUSTOMER_CERTIFICATE);
    }

    protected function ensureUserIdGood($id, $with = [])
    {
        // Must pass validating the user-id first
        $this->mockUserService->expects($this->once())
            ->method('findPerson')
            ->with($id)
            ->willReturn($with);

        return $this;
    }

    private function withRealEmailValidator()
    {
        $this->validator = new MailerValidator($this->mockUserService, new EmailAddressValidator());
    }

    private function withMockEmailValidator()
    {
        $mock = $this->getMockBuilder(EmailAddressValidator::class)
            ->setMethods(['isValid'])
            ->getMock();

        $mock->expects($this->any())
            ->method('isValid')
            ->willReturn(true);

        $this->validator = new MailerValidator($this->mockUserService, $mock);
    }
}
