<?php

namespace Dashboard\Service;

use Dvsa\OpenAM\OpenAMClientInterface;
use Dvsa\OpenAM\Options\OpenAMClientOptions;
use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\HttpRestJson\Client;
use Core\Service\MotFrontendIdentityProviderInterface;
use DvsaCommon\HttpRestJson\Exception\ValidationException;

class PasswordService
{
    private $apiClient;

    private $authClient;

    private $openAMClientOptions;

    private $identityProvider;

    private $errors = [];

    public function __construct(Client $apiClient, OpenAMClientInterface $authClient, OpenAMClientOptions $openAMClientOptions,
                                MotFrontendIdentityProviderInterface $identityProvider)
    {
        $this->apiClient = $apiClient;
        $this->authClient = $authClient;
        $this->openAMClientOptions = $openAMClientOptions;
        $this->identityProvider = $identityProvider;
    }

    public function changePassword(array $data)
    {
        $personId = $this->identityProvider->getIdentity()->getUserId();
        $url = PersonUrlBuilder::personPassword($personId)->toString();
        $this->errors = [];

        try {
            $this->apiClient->put($url, $data);
            $this->identityProvider->getIdentity()->setPasswordExpired(false);

            return true;
        } catch (ValidationException $e) {
            $this->extractErrors($e);

            return false;
        }
    }

    public function shouldWarnUserAboutFailedAttempts(): bool
    {
        $username = $this->identityProvider->getIdentity()->getUsername();
        return $this->openAMClientOptions->getWarnUserAfterNFailures() ==
            $this->authClient->getNumberOfFailedAtempts($username);
    }

    public function isAccountLocked(): bool
    {
        $username = $this->identityProvider->getIdentity()->getUsername();
        return $this->openAMClientOptions->getLoginFailureLockoutCount() ==
        $this->authClient->getNumberOfFailedAtempts($username);
    }

    private function extractErrors(ValidationException $e)
    {
        foreach ($e->getErrors() as $error) {
            $msg = $error['displayMessage'];
            if ($msg === ChangePasswordInputFilter::MSG_PASSWORD_INVALID) {
                $this->addError($msg, ChangePasswordInputFilter::FIELD_OLD_PASSWORD);
            } elseif ($msg === ChangePasswordInputFilter::MSG_PASSWORD_MATCH_USERNAME) {
                $this->addError($msg, ChangePasswordInputFilter::FIELD_PASSWORD);
            } elseif ($msg === ChangePasswordInputFilter::MSG_PASSWORD_HISTORY) {
                $this->addError($msg, ChangePasswordInputFilter::FIELD_PASSWORD);
            } else {
                $this->addError($msg);
            }
        }
    }

    public function getErrors()
    {
        return $this->errors;
    }

    private function addError($message, $field = '')
    {
        if (!array_key_exists($field, $this->errors)) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }
}
