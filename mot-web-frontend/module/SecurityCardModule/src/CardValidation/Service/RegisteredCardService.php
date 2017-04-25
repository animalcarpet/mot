<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardValidation\Service;

use Dvsa\Mot\ApiClient\Exception\ResourceNotFoundException;
use Dvsa\Mot\ApiClient\Resource\Item\SecurityCardValidation;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use GuzzleHttp\Exception\RequestException;
use Zend\Authentication\AuthenticationService;

class RegisteredCardService
{
    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * @var AuthorisationService
     */
    private $authorisationServiceClient;

    /**
     * @param AuthenticationService $authenticationService
     * @param AuthorisationService $authorisationServiceClient
     */
    public function __construct
    (
        AuthenticationService $authenticationService,
        AuthorisationService $authorisationServiceClient
    ) {
        $this->authenticationService = $authenticationService;
        $this->authorisationServiceClient = $authorisationServiceClient;
    }

    /**
     *
     * @param $pin
     * @return bool
     */
    public function validatePin($pin)
    {
        $isValid = false;
        try {
            $isValid = $this->authorisationServiceClient->validatePersonSecurityCard($pin)->isPinValid();

            if ($isValid === true) {
                $this->setAuthenticatedWith2FA();
            }

            return $isValid;
        } catch (RequestException $error) {
            return $isValid;
        }
    }

    /**
     * @param $pin
     * @return SecurityCardValidation
     */
    public function getSecurityCardValidation($pin) {
        $data = null;
        try {
            $data = $this->authorisationServiceClient->validatePersonSecurityCard($pin);
            if($data->isPinValid() === true) {
                $this->setAuthenticatedWith2FA();
            }
            return $data;
        } catch (RequestException $error) {
            return $data;
        }
    }

    /**
     * Sets the user as authenticated with 2FA.
     */
    public function setAuthenticatedWith2FA() {
        $identity = $this->authenticationService->getIdentity();
        $identity->setAuthenticatedWith2FA(true);
    }

    public function isLockedOut() {
        return $this->authorisationServiceClient->pinLockedOut();
    }

    public function getSerialNumber()
    {
        try {
            $username = $this->authenticationService->getIdentity()->getUsername();
            return $this->authorisationServiceClient->getSecurityCardForUser($username)->getSerialNumber();
        } catch (ResourceNotFoundException $error) {
            return "";
        }
    }

    public function is2faActiveUser($username)
    {
        try {
            return $this->authorisationServiceClient->getSecurityCardForUser($username)->isActive();
        } catch (ResourceNotFoundException $error) {
            return false;
        }
    }

    public function getLastRegisteredCard()
    {
        try {
            $username = $this->authenticationService->getIdentity()->getUsername();
            return $this->authorisationServiceClient->getSecurityCardForUser($username);
        } catch (ResourceNotFoundException $error) {
            return false;
        }
    }

    public function is2FALoginApplicableToCurrentUser()
    {
        $identity = $this->authenticationService->getIdentity();

        $hasAuthenticatedWith2FA = $identity->isAuthenticatedWith2FA();
        $isCardActivated = $identity->isSecondFactorRequired();

        return !$hasAuthenticatedWith2FA && $isCardActivated;
    }
}