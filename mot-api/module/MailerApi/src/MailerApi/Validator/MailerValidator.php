<?php

namespace MailerApi\Validator;

use DvsaCommon\Dto\Mailer\MailerDto;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaMotApi\Service\UserService;
use Zend\Validator\EmailAddress;

/**
 * Ensures the type is valid, if not throws a bad command exception.
 */
class MailerValidator
{
    protected $serviceManager;

    const ERROR_UNKNOWN_TYPE = 'Unknown mail request type';
    const ERROR_NO_USERID = 'Username reminder requires a user-id';
    const ERROR_INVALID_DTO = 'DTO object was null';
    const ERROR_NO_RECIPIENT = 'Email recipient must be provided';
    const ERROR_NO_FIRSTNAME = 'Recipient first name must be provided';
    const ERROR_NO_FAMILYNAME = 'Recipient last name must be provided';
    const ERROR_NO_ATTACHMENT = 'An attachment must be provided';

    /** Code to generate a username reminder by email */
    const TYPE_REMIND_USERNAME = 1;

    /** Code to generate a password reminder by email */
    const TYPE_REMIND_PASSWORD = 2;

    /** Code to generate a reclaim account by email */
    const TYPE_RECLAIM_ACCOUNT = 3;

    /** Code to send a customer certificate by email */
    const TYPE_CUSTOMER_CERTIFICATE = 4;

    /** @var UserService $userService */
    protected $userService;

    /**
     * @var EmailAddress
     */
    private $emailValidator;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService, EmailAddress $emailValidator = null)
    {
        $this->userService = $userService;
        $this->emailValidator = $emailValidator;
    }

    /**
     * Designated validation entry point.
     *
     * @param MailerDto $dto  contains the JSON as a DTO instance
     * @param int       $type contains the request context type
     *
     * @return bool TRUE on exit, otherwise it would have failed with an exception
     *
     * @throws BadRequestException
     */
    public function validate(MailerDto $dto, $type)
    {
        switch ($type) {
            case self::TYPE_REMIND_USERNAME:
                return $this->validateUsernameReminder($dto);

            case self::TYPE_REMIND_PASSWORD:
                return $this->validatePasswordReminder($dto);

            case self::TYPE_RECLAIM_ACCOUNT:
                return $this->validateReclaimAccount($dto);

            case self::TYPE_CUSTOMER_CERTIFICATE:
                return $this->validateCustomerCertificate($dto);

            default:
                throw new BadRequestException(
                    self::ERROR_UNKNOWN_TYPE,
                    BadRequestException::BAD_REQUEST_STATUS_CODE
                );
        }
    }

    /**
     * Check that the username reminder contains the correct information.
     *
     * @param MailerDto $dto
     *
     * @return bool
     *
     * @throws BadRequestException
     */
    protected function validateUsernameReminder(MailerDto $dto)
    {
        $this->ensureUserIdPresent($dto);

        return true;
    }

    /**
     * Check that the password reminder contains the correct information.
     *
     * @param MailerDto $dto
     *
     * @return bool
     *
     * @throws BadRequestException
     */
    protected function validatePasswordReminder(MailerDto $dto)
    {
        $this->ensureUserIdPresent($dto);

        return true;
    }

    /**
     * Check that the reclaim account contains the correct information.
     *
     * @param MailerDto $dto
     *
     * @return bool
     *
     * @throws BadRequestException
     */
    protected function validateReclaimAccount(MailerDto $dto)
    {
        $this->ensureUserIdPresent($dto);

        return true;
    }

    protected function validateCustomerCertificate($dto)
    {
        $data = $dto->getData();

        $recipient = ArrayUtils::tryGet($data, 'email', null);
        $this->validateEmail($recipient);

        $firstName = ArrayUtils::tryGet($data, 'firstName', null);
        $this->assertNotNull($firstName, self::ERROR_NO_FIRSTNAME);

        $familyName = ArrayUtils::tryGet($data, 'familyName', null);
        $this->assertNotNull($familyName, self::ERROR_NO_FAMILYNAME);

        $attachment = ArrayUtils::tryGet($data, 'attachment', null);
        $this->assertNotNull($attachment, self::ERROR_NO_ATTACHMENT);

        return true;
    }

    /**
     * Check that required data for a username reminder is present.
     * If we locate the user then we will add a new field into the
     * DTA data field called 'user', this contains the Person data
     * which can be used to personalise the outgoing message.
     *
     * @param MailerDto $dto
     *
     * @throws BadRequestException
     */
    protected function ensureUserIdPresent(MailerDto $dto)
    {
        $data = $dto->getData();
        $userid = ArrayUtils::tryGet($data, 'userid', null);

        $this->assertNotNull($userid, self::ERROR_NO_USERID);

        $userData = $this->userService->findPerson($userid);

        $data['user'] = $userData;
        $dto->setData($data);
    }

    protected function assertNotNull($value, $error)
    {
        if (is_null($value)) {
            throw new BadRequestException(
                $error,
                BadRequestException::BAD_REQUEST_STATUS_CODE
            );
        }
    }

    protected function validateEmail($email)
    {
        $this->assertNotNull($email, self::ERROR_NO_RECIPIENT);
        if (is_null($this->emailValidator)) {
            $this->emailValidator = new EmailAddressValidator();
        }

        if (!$this->emailValidator->isValid($email)) {
            throw new BadRequestException(
                implode(';', $this->emailValidator->getMessages()),
                BadRequestException::BAD_REQUEST_STATUS_CODE
            );
        }
    }
}
