<?php

namespace DvsaAuthentication\Login;

use DvsaAuthentication\Login\Response\AuthenticationResponse;
use DvsaAuthentication\Login\Response\AuthenticationSuccess;
use DvsaCommon\Authn\AuthenticationResultCode;
use DvsaCommon\Dto\Authn\AuthenticatedUserDto;
use DvsaCommon\Dto\Authn\AuthenticationResponseDto;
use DvsaCommon\Dto\Common\KeyValue;
use Zend\Authentication\Result;

class AuthenticationResponseMapper
{
    public function mapToDto(AuthenticationResponse $result, /* legacy */$username)
    {
        $isAuthenticationSuccessful = $result->getCode() === AuthenticationResultCode::SUCCESS;

        $authnDto = (new AuthenticationResponseDto())
            ->setAuthnCode($result->getCode())
            ->setExtra(KeyValue::fromMap($result->getExtra()))
            ->setCode($isAuthenticationSuccessful ? Result::SUCCESS : Result::FAILURE)
            ->setIsValid($isAuthenticationSuccessful)
            ->setIdentity($username) // legacy
            ->setMessages([$result->getMessage()]);

        if ($isAuthenticationSuccessful) {
            /** @var AuthenticationSuccess $result */
            $identity = $result->getIdentity();
            $authnDto->setAccessToken($result->getIdentity()->getToken());
            $authnUserDto = (new AuthenticatedUserDto())
                ->setUserId($identity->getUserId())
                ->setUsername($identity->getUsername())
                ->setDisplayName($identity->getDisplayName())
                ->setRole('')
                ->setPasswordExpiryDate($identity->getPasswordExpiryDate())
                ->setIsAccountClaimRequired($identity->isAccountClaimRequired())
                ->setIsPasswordChangeRequired($identity->isPasswordChangeRequired())
                ->setIsSecondFactorRequired($identity->isSecondFactorRequired());

            $authnDto->setUser($authnUserDto);
        }

        return $authnDto;
    }
}
