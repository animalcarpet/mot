<?php

namespace DvsaAuthentication\Login;

use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Dto\Authn\AuthenticationResponseDto;
use PersonApi\Service\PasswordExpiryService;

/**
 * Application service to perform all the logic relevant to login event.
 */
class LoginService
{
    private $authenticator;

    private $passwordExpiryService;

    private $mapper;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    public function __construct(
        UsernamePasswordAuthenticator $authenticator,
        PasswordExpiryService $passwordExpiryService,
        AuthenticationResponseMapper $mapper,
        MotIdentityProviderInterface $identityProvider
    ) {
        $this->authenticator = $authenticator;
        $this->passwordExpiryService = $passwordExpiryService;
        $this->mapper = $mapper;
        $this->identityProvider = $identityProvider;
    }

    /**
     * @param $username
     * @param $password
     *
     * @return AuthenticationResponseDto
     */
    public function login($username, $password)
    {
        $authResponse = $this->authenticator->authenticate($username, $password);
        $identityData = $this->mapper->mapToDto($authResponse, $username);

        return $identityData;
    }

    public function confirmPassword($password)
    {
        $identity = $this->identityProvider->getIdentity();

        return $this->authenticator->validateCredentials($identity->getUsername(), $password);
    }
}
