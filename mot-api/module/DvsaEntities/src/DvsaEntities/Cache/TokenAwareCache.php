<?php

namespace DvsaEntities\Cache;

use Doctrine\Common\Cache\Cache;
use DvsaApplicationLogger\TokenService\TokenServiceInterface;
use DvsaAuthentication\Service\ApiTokenService;

class TokenAwareCache implements Cache
{
    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var ApiTokenService
     */
    private $tokenService;

    /**
     * @param Cache                 $cache
     * @param TokenServiceInterface $tokenService
     */
    public function __construct(Cache $cache, TokenServiceInterface $tokenService)
    {
        $this->cache = $cache;
        $this->tokenService = $tokenService;
    }

    /**
     * @param string $id The id of the cache entry to fetch
     *
     * @return mixed The cached data or FALSE, if no cache entry exists for the given id
     */
    public function fetch($id)
    {
        return $this->cache->fetch($this->calculateId($id));
    }

    /**
     * @param string $id The cache id of the entry to check for
     *
     * @return bool TRUE if a cache entry exists for the given cache id, FALSE otherwise
     */
    public function contains($id)
    {
        return $this->cache->contains($this->calculateId($id));
    }

    /**
     * @param string $id       The cache id
     * @param mixed  $data     The cache entry/data
     * @param int    $lifeTime The cache lifetime.
     *                         If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime)
     *
     * @return bool TRUE if the entry was successfully stored in the cache, FALSE otherwise
     */
    public function save($id, $data, $lifeTime = 0)
    {
        return $this->cache->save($this->calculateId($id), $data, $lifeTime);
    }

    /**
     * @param string $id The cache id
     *
     * @return bool TRUE if the cache entry was successfully deleted, FALSE otherwise
     */
    public function delete($id)
    {
        return $this->cache->delete($this->calculateId($id));
    }

    /**
     * @return array|null An associative array with server's statistics if available, NULL otherwise
     */
    public function getStats()
    {
        return $this->cache->getStats();
    }

    /**
     * @param string $id
     *
     * @return string
     */
    private function calculateId($id)
    {
        return sha1($this->tokenService->getToken()).'_'.$id;
    }
}
