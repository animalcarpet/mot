<?php

namespace DvsaEntities\Cache\Repository;

use Doctrine\Common\Cache\Cache;
use DvsaCommon\Model\PersonAuthorization;
use DvsaEntities\Repository\RbacRepository;

class CachedRbacRepository implements RbacRepository
{
    /**
     * @var RbacRepository
     */
    private $rbacRepository;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var array
     */
    private $options;

    /**
     * @param RbacRepository $rbacRepository
     * @param Cache          $cache
     * @param array          $options
     */
    public function __construct(RbacRepository $rbacRepository, Cache $cache, array $options = [])
    {
        $this->rbacRepository = $rbacRepository;
        $this->cache = $cache;
        $this->options = $options;
    }

    /**
     * @param int    $personId
     * @param string $roleName
     *
     * @return bool
     */
    public function personIdHasRole($personId, $roleName)
    {
        return $this->rbacRepository->personIdHasRole($personId, $roleName);
    }

    /**
     * @param int $personId
     *
     * @return PersonAuthorization
     */
    public function authorizationDetails($personId)
    {
        $cacheId = $this->calculatePersonAuthorizationCacheId($personId);

        if (!$personAuthorization = $this->cache->fetch($cacheId)) {
            $personAuthorization = $this->rbacRepository->authorizationDetails($personId);

            $this->cache->save($cacheId, $personAuthorization, $this->getAuthorizationDetailsTtl());
        }

        return $personAuthorization;
    }

    /**
     * @param int $personId
     *
     * @return string
     */
    private function calculatePersonAuthorizationCacheId($personId)
    {
        return 'person_authorization_'.$personId;
    }

    /**
     * @return int
     */
    private function getAuthorizationDetailsTtl()
    {
        return isset($this->options['ttl']['authorization_details']) ? (int) $this->options['ttl']['authorization_details'] : 0;
    }
}
