<?php

namespace DvsaAuthentication\Authentication\Adapter\OpenAM;

use Dvsa\OpenAM\Exception\OpenAMClientException;
use Dvsa\OpenAM\Exception\OpenAMUnauthorisedException;
use Dvsa\OpenAM\OpenAMClientInterface;
use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use DvsaAuthentication\Authentication\Adapter\AuthenticationAdapterTrait;
use DvsaAuthentication\IdentityFactory;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;
use Zend\Http\Exception\InvalidArgumentException;
use Zend\Http\PhpEnvironment\Request;
use Zend\Log\LoggerInterface;

class OpenAMApiTokenBasedAdapter implements AdapterInterface
{
    use AuthenticationAdapterTrait;

    private $identityFactory;
    private $openAMClient;
    private $usernameAttribute;
    private $tokenService;
    private $logger;
    private $uuidAttribute;

    /**
     * @param OpenAMClientInterface $openAMClient
     * @param                       $usernameAttribute
     * @param IdentityFactory       $identityFactory
     * @param LoggerInterface       $logger
     * @param                       $tokenService
     * @param string $uuidAttribute
     */
    public function __construct(
        OpenAMClientInterface $openAMClient,
        $usernameAttribute,
        IdentityFactory $identityFactory,
        LoggerInterface $logger,
        $tokenService,
        $uuidAttribute
    ) {
        $this->identityFactory = $identityFactory;
        $this->openAMClient = $openAMClient;
        $this->usernameAttribute = $usernameAttribute;
        $this->logger = $logger;
        $this->tokenService = $tokenService;
        $this->uuidAttribute = $uuidAttribute;
    }

    public function authenticate()
    {
        $token = $this->tokenService->parseToken();
        if (!$token) {
            $this->logger->info('Token not found or invalid!');
            return self::invalidTokenResult();
        }

        try {
            $identityAttrs = $this->openAMClient->getIdentityAttributes(
                $token
            );
        } catch (OpenAMUnauthorisedException $ex) {
            $this->logger->debug('OpenAM client - message ' . $ex->getMessage());
            return self::invalidTokenResult();
        } catch (OpenAMClientException $clientEx) {
            $this->logger->err('OpenAM client - message ' . $clientEx->getMessage());
            return self::identityResolutionFailedResult();
        }

        $username = $this->findIdentityAttribute($this->usernameAttribute, $identityAttrs);
        if (empty($username)) {
            $this->logger->err($this->usernameAttribute . ' not found in identity attributes!');
            return self::identityResolutionFailedResult();
        }

        $uuid = $this->findIdentityAttribute($this->uuidAttribute, $identityAttrs);
        if (empty($uuid)) {
            $this->logger->err($this->uuidAttribute . ' not found in identity attributes!');
            return self::identityResolutionFailedResult();
        }

        try {
            $identity = $this->identityFactory->create($username, $token, $uuid);

            return new Result(Result::SUCCESS, $identity);
        } catch (\InvalidArgumentException $e) {
            $this->logger->err(sprintf('Person: %s not found in database!', $username));

            return self::identityResolutionFailedResult();
        }
    }
}
