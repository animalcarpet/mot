<?php

namespace DvsaAuthentication\Service;

use Dvsa\Mot\ApiClient\Service\AuthorisationService as AuthorisationServiceClient;
use DvsaAuthentication\Identity;
use DvsaAuthentication\TwoFactorStatus;
use DvsaAuthorisation\Service\AuthorisationService;
use DvsaCommon\Model\TradeRole;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;

class TwoFactorStatusService
{
    private $authorisationServiceClient;

    private $authorisationService;

    private $personRepository;

    public function __construct(
        AuthorisationServiceClient $authorisationServiceClient,
        AuthorisationService $authorisationService,
        PersonRepository $personRepository
    ) {
        $this->authorisationServiceClient = $authorisationServiceClient;
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

        $personAuthorisation = $this->authorisationService->getPersonAuthorization($identity->getUserId());
        if (TradeRole::containsTradeRole($personAuthorisation->getAllRoles())) {
            return TwoFactorStatus::INACTIVE_TRADE_USER;
        }

        $cardOrders = $this->authorisationServiceClient->getSecurityCardOrders($identity->getUsername());

        if (!$isActiveTwoFa && $cardOrders->getCount() > 0) {
            return TwoFactorStatus::AWAITING_CARD_ACTIVATION;
        }

        if (!$isActiveTwoFa) {
            return TwoFactorStatus::AWAITING_CARD_ORDER;
        }
    }
}
