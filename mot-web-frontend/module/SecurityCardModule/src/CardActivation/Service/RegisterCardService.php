<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardActivation\Service;

use Core\Service\MotFrontendIdentityProviderInterface;
use Dvsa\Mot\ApiClient\Request\ActivateCardRequest;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use DvsaAuthentication\IdentityProvider;

class RegisterCardService
{
    /** @var AuthorisationService */
    private $authorisationServiceClient;

    /** @var IdentityProvider */
    private $identityProvider;

    public function __construct(
        AuthorisationService $authorisationServiceClient,
        MotFrontendIdentityProviderInterface $identityProvider
    ) {
        $this->authorisationServiceClient = $authorisationServiceClient;
        $this->identityProvider = $identityProvider;
    }

    /**
     * Uses the Authorisation Service in the API client to register the card in the Java service.
     *
     * @param $pinNumber
     * @param $serialNumber
     */
    public function registerCard($serialNumber, $pinNumber)
    {
        $request = new ActivateCardRequest();

        $request->setPinNumber($pinNumber)->setSerialNumber($serialNumber);

        $this->authorisationServiceClient->activatePersonSecurityCard($request);

        /** @var \Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity $identity */
        $identity = $this->identityProvider->getIdentity();
        $identity->setSecondFactorRequired(true);
        $identity->setAuthenticatedWith2FA(true);
    }

    public function isUserRegistered()
    {
        $isSecondFactorRequired = $this->identityProvider->getIdentity()->isSecondFactorRequired();

        return $isSecondFactorRequired;
    }
}
