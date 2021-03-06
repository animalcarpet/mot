<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\Account\ClaimStartDto;
use DvsaCommon\UrlBuilder\AccountUrlBuilder;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

/**
 * Class AccountMapper.
 */
class AccountMapper extends DtoMapper
{
    /**
     * @param int $userId
     *
     * @return \DvsaCommon\Dto\AbstractDataTransferObject
     */
    public function resetPassword($userId)
    {
        $url = AccountUrlBuilder::resetPassword();
        $response = $this->client->post($url, ['userId' => $userId]);

        return DtoHydrator::jsonToDto($response['data']);
    }

    /**
     * @param string $token
     * @param string $password
     *
     * @return bool
     */
    public function changeForgottenPassword($token, $password)
    {
        $url = AccountUrlBuilder::changeForgottenPassword();

        $response = $this->client->post($url, ['token' => $token, 'newPassword' => $password]);

        if (array_key_exists('data', $response) && array_key_exists('success', $response['data'])) {
            return $response['data']['success'];
        }

        return false;
    }

    /**
     * @param $username
     *
     * @return \DvsaCommon\Dto\AbstractDataTransferObject
     */
    public function validateUsername($username)
    {
        return $this->getWithParams(
            AccountUrlBuilder::of()->validateUsername(),
            ['username' => $username]
        );
    }

    /**
     * @param string $token
     *
     * @return \DvsaCommon\Dto\Account\MessageDto
     */
    public function getMessageByToken($token)
    {
        return $this->get(AccountUrlBuilder::resetPassword($token));
    }

    /**
     * @param int $userId
     *
     * @return ClaimStartDto
     */
    public function getClaimData($userId)
    {
        return $this->get(UrlBuilder::claimAccount($userId));
    }

    /**
     * @return bool
     */
    public function claimUpdate($userId, $data)
    {
        $apiUrl = UrlBuilder::claimAccount($userId);

        return $this->put($apiUrl, $data);
    }
}
