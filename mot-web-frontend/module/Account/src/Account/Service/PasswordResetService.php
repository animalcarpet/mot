<?php

namespace Account\Service;

use DvsaClient\MapperFactory;

/**
 * Class PasswordResetService.
 */
class PasswordResetService
{
    /** @var MapperFactory $mapper */
    private $mapper;

    /**
     * @param MapperFactory $mapper
     */
    public function __construct(MapperFactory $mapper)
    {
        $this->mapper = $mapper;
    }

    /**
     * @param string $username
     *
     * @return int
     */
    public function validateUsername($username)
    {
        return $this->mapper->Account->validateUsername($username);
    }

    /**
     * @param string $token
     *
     * @return \DvsaCommon\Dto\Account\MessageDto|null
     */
    public function getToken($token)
    {
        if (empty($token)) {
            return null;
        }

        return $this->mapper->Account->getMessageByToken($token);
    }
}
