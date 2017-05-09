<?php

namespace Dashboard\Service;

use DvsaCommon\InputFilter\Account\ChangePasswordInputFilter;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\HttpRestJson\Client;
use Core\Service\MotFrontendIdentityProviderInterface;
use DvsaCommon\HttpRestJson\Exception\ValidationException;

class PasswordService
{
    private $client;

    private $identityProvider;

    private $errors = [];

    public function __construct(Client $client, MotFrontendIdentityProviderInterface $identityProvider)
    {
        $this->client = $client;
        $this->identityProvider = $identityProvider;
    }

    public function changePassword(array $data)
    {
        $personId = $this->identityProvider->getIdentity()->getUserId();
        $url = PersonUrlBuilder::personPassword($personId)->toString();
        $this->errors = [];

        try {
            $this->client->put($url, $data);
            $this->identityProvider->getIdentity()->setPasswordExpired(false);

            return true;
        } catch (ValidationException $e) {
            $this->extractErrors($e);

            return false;
        }
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
