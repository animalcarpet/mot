<?php

namespace DvsaAuthentication\IdentityFactory;

use Doctrine\Common\Cache\Cache;
use DvsaAuthentication\CacheableIdentity;
use DvsaAuthentication\Identity;
use DvsaAuthentication\IdentityFactory;
use DvsaEntities\Repository\PersonRepository;

class CacheableIdentityFactory implements IdentityFactory
{
    /**
     * @var IdentityFactory
     */
    private $identityFactory;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var PersonRepository
     */
    private $personRepository;

    /**
     * @var int
     */
    private $ttl;

    /**
     * @param IdentityFactory  $identityFactory
     * @param Cache            $cache
     * @param PersonRepository $personRepository
     * @param int              $ttl
     */
    public function __construct(
        IdentityFactory $identityFactory,
        Cache $cache,
        PersonRepository $personRepository,
        $ttl
    ) {
        $this->identityFactory = $identityFactory;
        $this->cache = $cache;
        $this->personRepository = $personRepository;
        $this->ttl = (int) $ttl;
    }

    /**
     * @param string $username
     * @param string $token
     * @param string $uuid
     *
     * @return Identity
     */
    public function create($username, $token, $uuid, $passwordExpiryDate)
    {
        $identity = $this->fetchIdentity($username, $token, $uuid, $passwordExpiryDate);

        if (!$identity instanceof CacheableIdentity) {
            throw new \RuntimeException('Failed to create a CacheableIdentity');
        }

        $identity->setPersonRepository($this->personRepository);
        $identity->setUuid($uuid);

        return $identity;
    }

    /**
     * @param string $username
     * @param string $token
     * @param string $uuid
     * @param string $passwordExpiryDate
     *
     * @return CacheableIdentity
     */
    private function fetchIdentity($username, $token, $uuid, $passwordExpiryDate)
    {
        $cacheKey = $this->calculateCacheKey($token);

        if (!$identity = @unserialize($this->cache->fetch($cacheKey))) {
            $identity = new CacheableIdentity(
                $this->identityFactory->create($username, $token, $uuid, $passwordExpiryDate)
            );

            if (!$identity->isAccountClaimRequired() && !$identity->isPasswordChangeRequired()) {
                $this->cache->save($cacheKey, serialize($identity), $this->ttl);
            }
        }

        return $identity;
    }

    /**
     * @param string $token
     *
     * @return string
     */
    private function calculateCacheKey($token)
    {
        return sprintf('%s_identity', sha1($token));
    }
}
