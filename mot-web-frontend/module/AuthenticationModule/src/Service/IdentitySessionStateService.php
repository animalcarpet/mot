<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\AuthenticationModule\Service;

use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\OpenAMClient;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\IdentitySessionState;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Zend\Log\LoggerInterface;

/**
 * See https://jira.i-env.net/browse/VM-10957 for reference.
 */
class IdentitySessionStateService
{
    /**
     * @var OpenAMClientInterface
     */
    private $openAMClient;

    /**
     * @var MotIdentityProviderInterface
     */
    private $motIdentityProvider;

    /**
     * @var WebAuthenticationCookieService
     */
    private $cookieService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Dvsa\OpenAM\OpenAMClientInterface                                             $openAMClient
     * @param \DvsaCommon\Auth\MotIdentityProviderInterface                                  $motIdentityProvider
     * @param \Dvsa\Mot\Frontend\AuthenticationModule\Service\WebAuthenticationCookieService $cookieService
     * @param \Zend\Log\LoggerInterface                                                      $logger
     */
    public function __construct(
        OpenAMClientInterface $openAMClient,
        MotIdentityProviderInterface $motIdentityProvider,
        WebAuthenticationCookieService $cookieService,
        LoggerInterface $logger
    ) {
        $this->cookieService = $cookieService;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->openAMClient = $openAMClient;
        $this->logger = $logger;
    }

    /**
     * @return IdentitySessionState
     */
    public function getState()
    {
        $token = $this->cookieService->getToken();
        if (is_null($token)) {
            return new IdentitySessionState(false, true);
        }
        if (false === $this->validateToken($token)) {
            return new IdentitySessionState(false, true);
        }

        /** @var Identity $identity */
        $identity = $this->motIdentityProvider->getIdentity();
        if (is_null($identity)) {
            return new IdentitySessionState(true, true);
        }

        if ($identity->getAccessToken() !== $token) {
            return new IdentitySessionState(true, true);
        }

        return new IdentitySessionState(true, false);
    }

    /**
     * @param $token
     *
     * @return bool
     */
    private function validateToken($token)
    {
        try {
            $isValid = $this->openAMClient->isTokenValid($token);
        } catch (OpenAMClientException $ex) {
            $this->logger->err('Error while validating OpenAM token');
            $isValid = false;
        }

        return $isValid;
    }
}
