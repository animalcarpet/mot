<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Service;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\RegistrationModule\Validator\RegistrationValidator;
use Dvsa\Mot\Frontend\RegistrationModule\Step\EmailStep;
use DvsaApplicationLogger\Log\Logger;
use DvsaCommon\Enum\BusinessRoleName;
use DvsaCommon\InputFilter\Registration\EmailInputFilter;
use DvsaCommon\InputFilter\Registration\PasswordInputFilter;
use DvsaEntities\Entity\Person;
use MailerApi\Logic\UsernameCreator;
use PersonApi\Service\DuplicateEmailCheckerService;

/**
 * In charge of:
 *   1. creating the person
 *   2. create an identity for it in the OpenAm
 *   3. create its contact detail
 *   4. assign USER role to it.
 *
 * Class RegistrationService
 */
class RegistrationService extends AbstractPersistableService
{
    const LOG_SIGNATURE = 'Registration: ';
    const LOG_REG_STARTED = 'begin Registration API process';
    const LOG_REG_VALID = 'validated data successfully';
    const LOG_REG_INVALID = 'failed on data validation';
    const LOG_REG_TRANSACTION_STARTED = 'started a transactional process';
    const LOG_REG_PERSON_CREATED = 'created a new person';
    const LOG_REG_PERSON_DUPLICATED_USERNAME = 'two users with the same name hit the registration service at the same time. The same username was generated for both.  The last one will fail';
    const LOG_REG_PERSON_FAILED = 'Failed to create a person';
    const LOG_REG_ROLE_ASSIGNED = 'assigned "USER" role to the registered person';
    const LOG_REG_ROLE_ASSIGNMENT_FAILED = 'Failed to assign "USER" role to the registered person';
    const LOG_REG_CONTACT_DETAIL = 'created new contact details and assigned it to the registered person';
    const LOG_REG_CONTACT_DETAIL_FAILED = 'Failed to create new contact details and assigning it to the registered user';
    const LOG_REG_OPENAM_SYNCED = "synced openAM with the registered person's credentials";
    const LOG_REG_OPENAM_SYNC_FAILED = "failed to update openAM with the registered person's credentials";
    const LOG_REG_EMAIL_SENT = 'sent the confirmation email to the registered person';
    const LOG_REG_EMAIL_FAILED = 'failed to send confirmation email';
    const LOG_REG_TRANSACTION_COMPLETED = 'completed transaction successfully';

    /**
     * @var OpenAMIdentityCreator
     */
    private $openAMService;

    /**
     * @var RegistrationValidator
     */
    private $registrationValidator;

    /**
     * @var PersonCreator
     */
    private $personService;

    /**
     * @var BusinessRoleAssigner
     */
    private $roleAssigner;

    /**
     * @var ContactDetailsCreator
     */
    private $contactDetailService;

    /**
     * @var Person
     */
    private $registeredPerson;

    /**
     * @var UsernameCreator
     */
    private $mailerLogic;

    /**
     * @var DuplicateEmailCheckerService
     */
    private $duplicateEmailCheckerService;

    /**
     * RegistrationService constructor.
     * @param EntityManager $entityManager
     * @param Logger $logger
     * @param OpenAMIdentityCreator $openAMService
     * @param RegistrationValidator $registrationValidator
     * @param PersonCreator $personService
     * @param BusinessRoleAssigner $roleAssigner
     * @param ContactDetailsCreator $contactDetailService
     * @param UsernameCreator $mailerLogic
     * @param DuplicateEmailCheckerService $duplicateEmailCheckerService
     */
    public function __construct(
        EntityManager $entityManager,
        Logger $logger,
        OpenAMIdentityCreator $openAMService,
        RegistrationValidator $registrationValidator,
        PersonCreator $personService,
        BusinessRoleAssigner $roleAssigner,
        ContactDetailsCreator $contactDetailService,
        UsernameCreator $mailerLogic,
        DuplicateEmailCheckerService $duplicateEmailCheckerService
    ) {
        parent::__construct($entityManager, $logger);
        $this->openAMService = $openAMService;
        $this->registrationValidator = $registrationValidator;
        $this->personService = $personService;
        $this->roleAssigner = $roleAssigner;
        $this->contactDetailService = $contactDetailService;
        $this->mailerLogic = $mailerLogic;
        $this->duplicateEmailCheckerService = $duplicateEmailCheckerService;
    }

    /**
     * Validate and attempt to record provided data, return true in case of success and false if it fails either during
     * validation or registration.
     * More detail about the reason of the failure can be gathered using getMessages() method.s.
     *
     * @param array $data
     *
     * @return bool
     */
    public function register($data)
    {
        $this->logInfo(self::LOG_REG_STARTED);
        $email = $data['stepEmail'][EmailInputFilter::FIELD_EMAIL];

        if ($this->registrationValidator->validate($data)->isValid() &&
            !$this->duplicateEmailCheckerService->isEmailDuplicated($email)) {
            $this->logInfo(self::LOG_REG_VALID);

            $registeredPerson = $this->registeredPerson;
            $roleAssigner = $this->roleAssigner;
            $contactDetailService = $this->contactDetailService;
            $openAMService = $this->openAMService;

            $this->registeredPerson = $this->inTransaction(

                function () use (
                    $data,
                    $registeredPerson,
                    $roleAssigner,
                    $contactDetailService,
                    $openAMService
                ) {

                    $this->logInfo(self::LOG_REG_TRANSACTION_STARTED);

                    try {
                        $registeredPerson = $this->personService->create($data);
                        $this->logInfo(self::LOG_REG_PERSON_CREATED);
                    } catch (UniqueConstraintViolationException $e) {
                        if (true === $this->isExceptionOnDuplicatedUsername($e)) {
                            $this->logError(self::LOG_REG_PERSON_DUPLICATED_USERNAME);
                            throw new \Exception('Two users with same name registered at same time');
                        } else {
                            $this->logDebug(self::LOG_REG_PERSON_FAILED);
                            $this->logError($e->getMessage());
                        }
                    } catch (\Exception $e) {
                        $this->logDebug(self::LOG_REG_PERSON_FAILED);
                        $this->logError($e->getMessage());
                    }

                    try {
                        $roleAssigner->assignRoleToPerson(
                            $registeredPerson,
                            BusinessRoleName::USER
                        );
                        $this->logInfo(self::LOG_REG_ROLE_ASSIGNED);
                    } catch (\Exception $e) {
                        $this->logDebug(self::LOG_REG_ROE_ASSIGNEMENT_FAILED);
                        $this->logError($e->getMessage());
                    }

                    try {
                        $contactDetailService->create(
                            $registeredPerson,
                            $data
                        );
                        $this->logInfo(self::LOG_REG_CONTACT_DETAIL);
                    } catch (\Exception $e) {
                        $this->logDebug(self::LOG_REG_CONTACT_DETAIL_FAILED);
                        $this->logError($e->getMessage());
                    }

                    try {
                        $openAMService->createIdentity(
                            $registeredPerson->getUsername(),
                            $data[$this->getPasswordStepName()][PasswordInputFilter::FIELD_PASSWORD],
                            $registeredPerson->getFirstName(),
                            $registeredPerson->getFamilyName()
                        );
                        $this->logInfo(self::LOG_REG_OPENAM_SYNCED);
                    } catch (\Exception $e) {
                        $this->logDebug(self::LOG_REG_OPENAM_SYNC_FAILED);
                        $this->logError($e->getMessage());
                    }

                    try {
                        if (!$this->sendEmail($registeredPerson)) {
                            $this->logDebug(self::LOG_REG_EMAIL_FAILED);

                            return false;
                        }
                        $this->logInfo(self::LOG_REG_EMAIL_SENT);
                    } catch (\Exception $e) {
                        $this->logDebug(self::LOG_REG_EMAIL_FAILED);
                        $this->logError($e->getMessage());
                    }

                    return $registeredPerson;
                }
            );

            $this->logInfo(self::LOG_REG_TRANSACTION_COMPLETED);

            return true;
        }

        $this->logError(self::LOG_REG_INVALID);

        return false;
    }

    /**
     * Uses the mailer logic to prepare and send the email.
     *
     * @param Person $person
     *
     * @return bool
     */
    private function sendEmail(Person $person)
    {
        $this->mailerLogic->setPerson($person);
        $subject = $this->mailerLogic->prepareSubject();
        $message = $this->mailerLogic->prepareMessage();

        return $this->mailerLogic->send($person->getPrimaryEmail(), $subject, $message);
    }

    /**
     * @return Person
     */
    public function getRegisteredPerson()
    {
        return $this->registeredPerson;
    }

    public function getMessages()
    {
        return $this->registrationValidator->getMessages();
    }

    /**
     * To make sure if it is a exception we want to handle
     * i.e. //SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry 'GREE0005' for key 'username'.
     *
     * @param \Exception $e
     *
     * @return bool
     */
    private function isExceptionOnDuplicatedUsername(\Exception $e)
    {
        $checks = [
            strstr($e->getMessage(), 'SQLSTATE[23000]: Integrity constraint violation:'),
            strstr($e->getMessage(), '1062'),
            strstr($e->getMessage(), 'Duplicate'),
            strstr($e->getMessage(), 'username'),
        ];

        return !in_array(false, $checks);
    }

    /**
     * Log an information.
     *
     * @param string $message
     */
    private function logInfo($message)
    {
        $this->logger->info(self::LOG_SIGNATURE . $message);
    }

    /**
     * Log debugging detail.
     *
     * @param $message
     */
    private function logDebug($message)
    {
        $this->logger->debug(self::LOG_SIGNATURE . $message);
    }

    /**
     * Log errors.
     *
     * @param $message
     */
    private function logError($message)
    {
        $this->logger->err(self::LOG_SIGNATURE . $message);
    }
}
