<?php

namespace Dvsa\Mot\Frontend\AuthenticationModule\AuthAdapter;

use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use Dvsa\Mot\Frontend\AuthenticationModule\Model\Identity;
use DvsaCommon\Date\DateUtils;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\HttpRestJson\Exception\ForbiddenOrUnauthorisedException;
use DvsaCommon\UrlBuilder\UrlBuilder;
use Zend\Authentication\Adapter\AdapterInterface;
use Zend\Authentication\Result;

class Rest implements AdapterInterface
{
    /**
     * @var \DvsaCommon\HttpRestJson\Client
     */
    private $client;

    /**
     * @var \DvsaApplicationLogger\TokenService\TokenServiceInterface
     */
    private $tokenService;

    /**
     * @param \DvsaCommon\HttpRestJson\Client                           $client
     * @param \DvsaApplicationLogger\TokenService\TokenServiceInterface $tokenServices
     */
    public function __construct(HttpRestJsonClient $client, TokenServiceInterface $tokenService)
    {
        $this->client = $client;
        $this->tokenService = $tokenService;
    }

    /**
     * @return \Zend\Authentication\Result
     */
    public function authenticate()
    {
        try {
            $restResult = $this->client->get((new UrlBuilder())->identityData()->toString());
        } catch (ForbiddenOrUnauthorisedException $fe) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null, ['Credentials not valid']);
        }

        if ($restResult == null || $restResult['data'] == null) {
            return new Result(Result::FAILURE_UNCATEGORIZED, null, ['Service not available.']);
        }

        $restResultData = $restResult['data'];

        if ($restResultData['user'] == null) {
            return new Result(Result::FAILURE_CREDENTIAL_INVALID, null);
        }

        $identity = new Identity();
        $identity
            ->setUserId($restResultData['user']['userId'])
            ->setUsername($restResultData['user']['username'])
            ->setDisplayName($restResultData['user']['displayName'])
            ->setDisplayRole($restResultData['user']['role'])
            ->setAccessToken($this->tokenService->getToken())
            ->setPasswordExpiryDate(DateUtils::toDateTime($restResultData['user']['passwordExpiryDate']))
            ->setAccountClaimRequired($restResultData['user']['isAccountClaimRequired'])
            ->setPasswordChangeRequired($restResultData['user']['isPasswordChangeRequired'])
            ->setSecondFactorRequired($restResultData['user']['isSecondFactorRequired']);

        return new Result(Result::SUCCESS, $identity);
    }
}
