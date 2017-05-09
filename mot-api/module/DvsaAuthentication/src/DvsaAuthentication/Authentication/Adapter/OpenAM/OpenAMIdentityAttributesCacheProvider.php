<?php

namespace DvsaAuthentication\Authentication\Adapter\OpenAM;

use Doctrine\Common\Cache\Cache;

class OpenAMIdentityAttributesCacheProvider
{
    private $cache;
    private $lifeTime;
    private $cacheNamespace;

    public function __construct(Cache $cache, $lifeTime)
    {
        $this->cache = $cache;
        $this->lifeTime = $lifeTime;

        $this->cacheNamespace = 'identity-attributes-';
    }

    public function getAttributes($token)
    {
        return $this->cache->fetch($this->cacheNamespace.$token);
    }

    public function saveAttributes($token, $attributes)
    {
        $this->cache->save($this->cacheNamespace.$token, $attributes, $this->lifeTime);
    }
}
