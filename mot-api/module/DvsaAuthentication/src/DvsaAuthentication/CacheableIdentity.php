<?php

namespace DvsaAuthentication;

use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use Serializable;

/**
 * An identity that's easy to cache.
 *
 * It avoids serializing of Person entity as it is not recommended,
 * mainly due to the way doctrine proxies work.
 * Unfortunately a PersonRepository needs to be set on the identity at some
 * point in order for Person to be lazy loaded.
 */
class CacheableIdentity extends Identity implements Serializable
{
    /**
     * @var PersonRepository
     */
    private $personRepository;

    public function __construct(Identity $identity)
    {
        parent::__construct($identity->getPerson());

        $this->setUuid($identity->getUuid());
        $this->setToken($identity->getToken());
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        $person = parent::getPerson();

        if (null === $this->personRepository && !$person instanceof Person) {
            throw new \BadMethodCallException('Set PersonRepository on the CacheableIdentity in order to lazy load a Person');
        }

        if ($person instanceof Person) {
            return $person;
        }

        return $this->personRepository->get($this->getUserId());
    }

    /**
     * @param PersonRepository $personRepository
     */
    public function setPersonRepository(PersonRepository $personRepository)
    {
        $this->personRepository = $personRepository;
    }

    /**
     * @return string
     */
    public function serialize()
    {
        return serialize([
            'username' => $this->username,
            'displayName' => $this->displayName,
            'userId' => $this->userId,
            'token' => $this->token,
            'uuid' => $this->uuid,
            'isAccountClaimRequired' => $this->isAccountClaimRequired,
            'isPasswordChangeRequired' => $this->isPasswordChangeRequired,
        ]);
    }

    /**
     * @param string $serialized
     */
    public function unserialize($serialized)
    {
        $details = unserialize($serialized);
        $requiredKeys = ['username', 'displayName', 'userId', 'token', 'uuid', 'isAccountClaimRequired', 'isPasswordChangeRequired'];

        if (!is_array($details) || [] !== array_diff($requiredKeys, array_keys($details))) {
            return;
        }

        $this->username = $details['username'];
        $this->displayName = $details['displayName'];
        $this->userId = $details['userId'];
        $this->token = $details['token'];
        $this->uuid = $details['uuid'];
        $this->isAccountClaimRequired = $details['isAccountClaimRequired'];
        $this->isPasswordChangeRequired = $details['isPasswordChangeRequired'];
    }
}
