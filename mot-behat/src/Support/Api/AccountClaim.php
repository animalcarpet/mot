<?php

namespace Dvsa\Mot\Behat\Support\Api;

class AccountClaim extends MotApi
{
    const PATH_IDENTITY = 'identity-data';
    const PATH_ACCOUNT_CLAIM = 'account/claim/{user_id}';

    public function getIdentityData($token)
    {
        return $this->sendGetRequest(
            $token,
            self::PATH_IDENTITY
        );
    }

    public function updateAccountClaim($token, $userId, $accountClaim)
    {
        $params = array_merge($accountClaim, [
            'personId' => $userId,
            'emailConfirmation' => $accountClaim['email'],
            'passwordConfirmation' => $accountClaim['password'],
        ]);

        return $this->sendPutRequest(
            $token,
            str_replace('{user_id}', $userId, self::PATH_ACCOUNT_CLAIM),
            $params
        );
    }
}
