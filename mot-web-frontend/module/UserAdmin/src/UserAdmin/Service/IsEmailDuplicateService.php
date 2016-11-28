<?php

namespace UserAdmin\Service;

use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;

class IsEmailDuplicateService
{
    const URL = 'person/email/is-duplicate?email=';
    /** @var  HttpRestJsonClient */
    private $jsonClient;

    public function __construct(HttpRestJsonClient $client)
    {
        $this->jsonClient = $client;
    }

    public function isEmailDuplicate($emailAddress)
    {
        $response = $this->jsonClient->get(self::URL . urlencode($emailAddress));

        if (
            !is_array($response) ||
            !array_key_exists('data', $response) ||
            !array_key_exists('isDuplicate',  $response['data'])
        ){
            throw new \LogicException('Expected to receive a valid response from the API containing nesting "data" and "isDuplicate" keys');
        }

        return $response['data']['isDuplicate'];
    }
}