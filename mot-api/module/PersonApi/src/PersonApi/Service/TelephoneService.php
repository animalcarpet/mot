<?php

namespace PersonApi\Service;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\AbstractService;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Validator\TelephoneNumberValidator;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\PersonContact;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Contact\PhoneDto;
use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use PersonApi\Helper\PersonDetailsChangeNotificationHelper;
use DvsaAuthorisation\Service\AuthorisationService;

class TelephoneService extends AbstractService
{
    /** @var ContactDetailsService */
    private $contactDetailsService;

    /** @var TelephoneNumberValidator */
    private $validator;

    /**
     * @var AuthorisationService
     */
    private $authService;

    /**
     * @var PersonDetailsChangeNotificationHelper
     */
    private $notificationHelper;

    /**
     * @param EntityManager                         $entityManager
     * @param ContactDetailsService                 $contactDetailsService
     * @param TelephoneNumberValidator              $validator
     * @param AuthorisationService                  $authorisationService
     * @param PersonDetailsChangeNotificationHelper $notificationHelper
     */
    public function __construct(
        EntityManager $entityManager,
        ContactDetailsService $contactDetailsService,
        TelephoneNumberValidator $validator,
        AuthorisationService $authorisationService,
        PersonDetailsChangeNotificationHelper $notificationHelper
    ) {
        parent::__construct($entityManager);

        $this->contactDetailsService = $contactDetailsService;
        $this->validator = $validator;
        $this->authService = $authorisationService;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * @param int    $personId
     * @param string $newPhoneNumber
     */
    public function updatePhoneNumber($personId, $newPhoneNumber)
    {
        $person = $this->findPerson($personId);
        $identity = $this->authService->getIdentity();

        /** @var PersonContact $personContactDetails */
        $personContact = ArrayUtils::firstOrNull($person->getContacts());
        if (null !== $personContact) {
            if (null === $newPhoneNumber) {
                $newPhoneNumber = '';
            }

            if (!$this->validator->isValid($newPhoneNumber)) {
                throw new BadRequestException('validation failed', 400);
            }

            /** @var ContactDetail $personContactDetails */
            $personContactDetails = $personContact->getDetails();

            $contactDto = new ContactDto();
            $newPhone = (new PhoneDto())
                ->setIsPrimary(true)
                ->setNumber($newPhoneNumber)
                ->setContactType(PhoneContactTypeCode::PERSONAL);

            $contactDto->setPhones([$newPhone]);

            if ($identity->getUserId() != $personId) {
                $this->notificationHelper->sendChangedPersonalDetailsNotification($person);
            }

            return $this->contactDetailsService->patchContactDetailsFromDto(
                $contactDto,
                $personContactDetails
            );
        }
    }
}
