<?php

namespace PersonApi\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Enum\PersonContactTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\DataValidationException;
use DvsaEntities\Entity\Email;
use DvsaEntities\Repository\PersonContactRepository;
use OrganisationApi\Service\Mapper\PersonContactMapper;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;
use PersonApi\Service\Validator\PersonalDetailsValidator;
use Zend\Authentication\AuthenticationService;

class PersonContactService extends AbstractService
{
    /**
     * @var PersonContactRepository
     */
    private $personContactRepository;
    /**
     * @var EntityRepository
     */
    private $emailRepository;
    /**
     * @var PersonContactMapper
     */
    private $personContactMapper;
    /**
     * @var PersonalDetailsValidator
     */
    private $personalDetailsValidator;
    /**
     * @var AuthenticationService
     */
    private $authenticationService;
    /**
     * @var AuthorisationService
     */
    private $authorisationService;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var PersonDetailsChangeNotificationHelper
     */
    private $notificationHelper;

    public function __construct(
        PersonContactRepository $repository,
        PersonContactMapper $mapper,
        EntityRepository $emailRepository,
        PersonalDetailsValidator $validator,
        AuthenticationService $authenticationService,
        AuthorisationService $authorisationService,
        EntityManager $em,
        PersonDetailsChangeNotificationHelper $notificationHelper
    ) {
        parent::__construct($em);

        $this->personContactRepository = $repository;
        $this->personContactMapper = $mapper;
        $this->emailRepository = $emailRepository;
        $this->personalDetailsValidator = $validator;
        $this->authenticationService = $authenticationService;
        $this->authorisationService = $authorisationService;
        $this->em = $em;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * @param int   $personId
     * @param array $data
     *
     * @return \DvsaCommon\Dto\Person\PersonContactDto
     *
     * @throws DataValidationException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function updateEmailForPerson($personId, $data)
    {
        if ($this->authenticationService->getIdentity()->getUserId() != $personId) {
            $this->authorisationService->assertGranted(PermissionInSystem::PROFILE_EDIT_OTHERS_EMAIL_ADDRESS);
        }

        if (!isset($data['email'])
            || !$this->personalDetailsValidator->validateEmail($data['email'])
        ) {
            $exception = new DataValidationException();
            $exception->addError('Email Address not Valid', 1);
            throw $exception;
        }

        $contact = $this->personContactRepository->getHydratedByTypeCode($personId, PersonContactTypeCode::PERSONAL);
        $contactDetails = $contact->getDetails();
        $emails = $contactDetails->getEmails();

        /** @var Email[] $primaryEmails */
        $primaryEmails = ArrayUtils::filter($emails, function (Email $email) {
            return $email->getIsPrimary();
        });

        if (!$primaryEmails) {
            $email = new Email();
            $email->setEmail($data['email']);
            $email->setContact($contactDetails);
            $email->setIsPrimary(true);
            $this->entityManager->persist($email);
        } else {
            /* @var Email $primaryEmail */
            if (count($primaryEmails) > 1) {
                $this->markExcessivePrimaryEmailsAsNotPrimary($primaryEmails);
            }
            $primaryEmail = ArrayUtils::first($primaryEmails);
            $primaryEmail->setEmail($data['email']);
            $this->entityManager->persist($primaryEmail);
        }
        $this->em->flush();

        if ($this->authenticationService->getIdentity()->getUserId() != $personId) {
            $person = $this->findPerson($personId);
            $this->notificationHelper->sendChangedPersonalDetailsNotification($person);
        }

        $dto = $this->personContactMapper->toDto($contact);

        return $dto;
    }

    /**
     * @param Email[] $emails
     */
    private function markExcessivePrimaryEmailsAsNotPrimary(array $emails)
    {
        $firstEmail = ArrayUtils::first($emails);
        /** @var Email[] $excessiveEmails */
        $excessiveEmails = ArrayUtils::filter($emails, function (Email $email) use ($firstEmail) {
            return $email !== $firstEmail;
        });

        foreach ($excessiveEmails as $excessiveEmail) {
            $excessiveEmail->setIsPrimary(false);
        }
    }
}
