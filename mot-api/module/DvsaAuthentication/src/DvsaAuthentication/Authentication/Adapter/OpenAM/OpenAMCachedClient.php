<?php

namespace DvsaAuthentication\Authentication\Adapter\OpenAM;

use Dvsa\OpenAM\Model\OpenAMExistingIdentity;
use Dvsa\OpenAM\Model\OpenAMLoginDetails;
use Dvsa\OpenAM\Model\OpenAMNewIdentity;
use Dvsa\OpenAM\OpenAMClientInterface;
use Zend\Log\LoggerInterface;

/**
 * Class OpenAMCachedClient.
 *
 * Provides cache over getIdentityAttributes method
 */
class OpenAMCachedClient implements OpenAMClientInterface
{
    private $openAMClient;
    private $cacheProvider;
    private $logger;

    public function __construct(
        OpenAMClientInterface $openAMClient,
        OpenAMIdentityAttributesCacheProvider $cacheProvider,
        LoggerInterface  $logger
    ) {
        $this->openAMClient = $openAMClient;
        $this->cacheProvider = $cacheProvider;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function isTokenValid($token)
    {
        $this->openAMClient->isTokenValid($token);
    }

    /**
     * {@inheritdoc}
     */
    public function lockAccount(OpenAMLoginDetails $userDetails)
    {
        $this->openAMClient->lockAccount($userDetails);
    }

    /**
     * {@inheritdoc}
     */
    public function unlockAccount(OpenAMLoginDetails $userDetails)
    {
        $this->openAMClient->unlockAccount($userDetails);
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountLocked(OpenAMLoginDetails $userDetails)
    {
        $this->openAMClient->isAccountLocked($userDetails);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentityAttributes($subjectId)
    {
        $attributes = $this->cacheProvider->getAttributes($subjectId);

        if (!$attributes) {
            $this->logger->debug('Identity attributes not in cache. Retrieving attributes from OpenAM');
            $attributes = $this->openAMClient->getIdentityAttributes($subjectId);
            $this->cacheProvider->saveAttributes($subjectId, $attributes);
        } else {
            $this->logger->debug('Identity attributes found in cache');
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function authenticate(OpenAMLoginDetails $loginDetails)
    {
        $this->openAMClient->authenticate($loginDetails);
    }

    /**
     * {@inheritdoc}
     */
    public function logout($token)
    {
        $this->openAMClient->logout($token);
    }

    /**
     * {@inheritdoc}
     */
    public function createIdentity(OpenAMNewIdentity $newIdentity)
    {
        $this->openAMClient->createIdentity($newIdentity);
    }

    /**
     * {@inheritdoc}
     */
    public function updateIdentity(OpenAMExistingIdentity $identity)
    {
        $this->openAMClient->updateIdentity($identity);
    }

    /**
     * {@inheritdoc}
     */
    public function validateCredentials(OpenAMLoginDetails $loginDetails)
    {
        $this->openAMClient->validateCredentials($loginDetails);
    }

    /**
     * {@inheritdoc}
     */
    public function getPasswordExpiryDate(OpenAMLoginDetails $loginDetails)
    {
        $this->openAMClient->getPasswordExpiryDate($loginDetails);
    }
}
