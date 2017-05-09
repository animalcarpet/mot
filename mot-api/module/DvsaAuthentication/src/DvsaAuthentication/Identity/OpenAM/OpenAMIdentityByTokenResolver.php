<?php

namespace DvsaAuthentication\Identity\OpenAM;

use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\OpenAMClient;
use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaAuthentication\Identity\IdentityByTokenResolver;
use DvsaAuthentication\Identity\OpenAM\Utils\IdentityAttributeFinder;
use DvsaAuthentication\Identity\OpenAM\Utils\PasswordExpiryAttributeParser;
use DvsaAuthentication\IdentityFactory;
use Zend\Log\LoggerInterface;

class OpenAMIdentityByTokenResolver implements IdentityByTokenResolver
{
    /** @var OpenAMClientOptions $options */
    private $options;

    /** @var OpenAMClientInterface $openAMClient */
    private $openAMClient;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        OpenAMClientInterface $openAMClient,
        OpenAMClientOptions $openAMClientOptions,
        LoggerInterface $logger,
        IdentityFactory $identityFactory
    ) {
        $this->openAMClient = $openAMClient;
        $this->options = $openAMClientOptions;
        $this->logger = $logger;
        $this->identityFactory = $identityFactory;
    }

    public function resolve($token)
    {
        try {
            $attributes = $this->openAMClient->getIdentityAttributes($token);
        } catch (OpenAMClientException $clientEx) {
            $this->logger->err('OpenAM client - message: '.$clientEx->getMessage());

            return null;
        }

        $username = $this->findAttribute($this->options->getIdentityAttributeUsername(), $attributes);
        if (empty($username)) {
            return null;
        }

        $uuid = $this->findAttribute($this->options->getIdentityAttributeUuid(), $attributes);
        if (empty($uuid)) {
            return null;
        }

        $passwordExpiryDateRaw = $this->findAttribute($this->options->getIdentityAttributePasswordExpiryTime(), $attributes);
        if (!$passwordExpiryDateRaw) {
            return null;
        }
        $passwordExpiryDate = PasswordExpiryAttributeParser::parse($passwordExpiryDateRaw);
        if (!$passwordExpiryDate) {
            $this->logger->err('Failure to parse password expiry date for user: '.$username);

            return null;
        }

        return $this->identityFactory->create($username, $token, $uuid, $passwordExpiryDate);
    }

    private function findAttribute($attributeName, $attributes)
    {
        $value = IdentityAttributeFinder::find($attributeName, $attributes);
        if (empty($value)) {
            $this->logger->err($attributeName.' not found in identity attributes!');

            return null;
        }

        return $value;
    }
}
