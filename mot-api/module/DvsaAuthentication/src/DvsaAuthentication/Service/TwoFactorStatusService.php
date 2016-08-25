<?php

namespace DvsaAuthentication\Service;

use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use DvsaAuthentication\Identity;
use DvsaAuthentication\TwoFactorStatus;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;

class TwoFactorStatusService
{
    private $authorisationService;

    private $personRepository;

    public function __construct(AuthorisationService $authorisationService, PersonRepository $personRepository)
    {
        $this->authorisationService = $authorisationService;
        $this->personRepository = $personRepository;
    }

    public function getStatusForPerson(Person $person)
    {
        $personIdentity = $this->personRepository->findIdentity($person->getUsername());
        $identity = new Identity($personIdentity);

        return $this->getStatusForIdentity($identity);
    }

    public function getStatusForIdentity(Identity $identity)
    {
        $isActiveTwoFa = $identity->isSecondFactorRequired();

        if ($isActiveTwoFa) {
            return TwoFactorStatus::ACTIVE;
        }

        $cardOrders = $this->authorisationService->getSecurityCardOrders($identity->getUsername());

        if (!$isActiveTwoFa && $cardOrders->getCount() > 0) {
            return TwoFactorStatus::AWAITING_CARD_ACTIVATION;
        }

        if (!$isActiveTwoFa) {
            return TwoFactorStatus::AWAITING_CARD_ORDER;
        }
    }
}
