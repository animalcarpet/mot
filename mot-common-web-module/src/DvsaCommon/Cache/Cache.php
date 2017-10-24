<?php
declare(strict_types=1);

namespace DvsaCommon\Cache;

use Zend\Cache\Storage\StorageInterface;

class Cache
{
    /**
     * @var StorageInterface
     */
    protected $storage;

    /**
     * @var CacheKeyGenerator
     */
     protected $cacheKeyGenerator;

    public function __construct(StorageInterface $storage, CacheKeyGenerator $cacheKeyGenerator)
    {
        $this->storage = $storage;
        $this->cacheKeyGenerator = $cacheKeyGenerator;
    }

    public function generateKey(...$args): string
    {
        return $this->cacheKeyGenerator->generateKey(...$args);
    }

    /**
     * @return StorageInterface
     */
    public function getStorage(): StorageInterface
    {
        return $this->storage;
    }

    /**
     * @param StorageInterface $storage
     */
    public function setStorage(StorageInterface $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @return CacheKeyGenerator
     */
    public function getCacheKeyGenerator(): CacheKeyGenerator
    {
        return $this->cacheKeyGenerator;
    }

    /**
     * @param CacheKeyGenerator $cacheKeyGenerator
     */
    public function setCacheKeyGenerator(CacheKeyGenerator $cacheKeyGenerator)
    {
        $this->cacheKeyGenerator = $cacheKeyGenerator;
    }

}