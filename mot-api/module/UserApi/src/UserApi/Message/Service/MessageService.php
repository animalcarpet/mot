<?php

namespace UserApi\Message\Service;

use AccountApi\Service\OpenAmIdentityService;
use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Enum\MessageTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ServiceException;
use DvsaEntities\Entity\Message;
use DvsaEntities\Entity\MessageType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\MessageRepository;
use DvsaEntities\Repository\MessageTypeRepository;
use DvsaEntities\Repository\PersonRepository;
use UserApi\Message\Service\Validator\MessageValidator;

class MessageService
{
    /** @var MessageRepository $messageRepository */
    private $messageRepository;
    /** @var MessageTypeRepository $messageTypeRepository */
    private $messageTypeRepository;
    /** @var PersonRepository $personRepository */
    private $personRepository;
    /** @var MotAuthorisationServiceInterface $authService */
    private $authorisationService;
    /** @var MessageValidator $validator */
    private $validator;

    private $dateTimeHolder;

    /**
     * @var OpenAmIdentityService
     */
    private $openAmIdentityService;

    /**
     * @param MessageRepository     $messageRepository
     * @param MessageTypeRepository $messageTypeRepository
     * @param PersonRepository      $personRepository
     * @param MessageValidator      $validator
     * @param OpenAmIdentityService $openAmIdentityService
     */
    public function __construct(
        MessageRepository $messageRepository,
        MessageTypeRepository $messageTypeRepository,
        PersonRepository $personRepository,
        MotAuthorisationServiceInterface $authorisationService,
        MessageValidator $validator,
        DateTimeHolder $dateTimeHolder,
        OpenAmIdentityService $openAmIdentityService
    ) {
        $this->messageRepository = $messageRepository;
        $this->messageTypeRepository = $messageTypeRepository;
        $this->personRepository = $personRepository;
        $this->authorisationService = $authorisationService;
        $this->validator = $validator;
        $this->dateTimeHolder = $dateTimeHolder;
        $this->openAmIdentityService = $openAmIdentityService;
    }

    /**
     * @param array $data
     *
     * @return int
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function createMessage($data)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::CREATE_MESSAGE_FOR_OTHER_USER);

        TypeCheck::assertArray($data);

        $this->validator->validate($data);

        $personId = ArrayUtils::get($data, 'personId');
        $messageTypeCode = ArrayUtils::get($data, 'messageTypeCode');

        $person = $this->personRepository->get($personId);
        $messageType = $this->messageTypeRepository->getByCode($messageTypeCode);

        if ($messageTypeCode === MessageTypeCode::PASSWORD_RESET_BY_LETTER) {
            $this->assertCreationOfNewPasswordResetIsPossible($person, $messageType);
        } elseif ($messageTypeCode === MessageTypeCode::ACCOUNT_RESET_BY_LETTER) {
            $this->assertCreationOfNewAccountResetIsPossible($person, $messageType);
        }

        $this->openAmIdentityService->unlockAccount($person->getUsername());

        $message = (new Message())
            ->setMessageType($messageType)
            ->setPerson($person)
            ->setIssueDate($this->dateTimeHolder->getCurrent());

        $this->messageRepository->persist($message);
        $this->messageRepository->flush($message);

        return $message->getId();
    }

    /**
     * @param Person      $person
     * @param MessageType $messageType
     *
     * @throws ServiceException
     */
    private function assertCreationOfNewPasswordResetIsPossible(Person $person, MessageType $messageType)
    {
        if (
            $messageType->isPasswordResetByLetter()
            && $this->messageRepository->hasAlreadyRequestedMessage($person, $messageType, $this->dateTimeHolder->getCurrentDate())
        ) {
            $errorMessage = 'A password reset letter has already been requested for '.
                $person->getDisplayName().
                ' today.';

            throw new BadRequestException($errorMessage, BadRequestException::ERROR_CODE_BUSINESS_FAILURE);
        }
    }

    /**
     * @param Person      $person
     * @param MessageType $messageType
     *
     * @throws ServiceException
     */
    private function assertCreationOfNewAccountResetIsPossible(Person $person, MessageType $messageType)
    {
        if (
            $messageType->isAccountResetByLetter()
            && $this->messageRepository->hasAlreadyRequestedMessage($person, $messageType, $this->dateTimeHolder->getCurrentDate())
        ) {
            $errorMessage = 'A request to re-set this user\'s account has already been made today';

            throw new BadRequestException($errorMessage, BadRequestException::ERROR_CODE_BUSINESS_FAILURE);
        }
    }
}
