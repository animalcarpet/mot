<?php

namespace DvsaEntities\Factory\Repository;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Cache\Repository\CachedRbacRepository;
use DvsaEntities\Cache\TokenAwareCache;
use DvsaEntities\Repository\RbacRepository;
use DvsaEntities\Repository\SqlRbacRepository;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class RbacRepositoryFactory implements FactoryInterface
{
    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return RbacRepository
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $repository = new SqlRbacRepository($serviceLocator->get(EntityManager::class));

        if ($this->isCacheEnabled($serviceLocator)) {
            $repository = new CachedRbacRepository(
                $repository,
                new TokenAwareCache(
                    $serviceLocator->get(Cache::class),
                    $serviceLocator->get('tokenService')
                ),
                $this->getCacheOptions($serviceLocator)
            );
        }

        return $repository;
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return bool
     */
    private function isCacheEnabled(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        return isset($config['cache']['rbac_repository']['enabled']) && true === $config['cache']['rbac_repository']['enabled'];
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return array
     */
    private function getCacheOptions(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');

        if (isset($config['cache']['rbac_repository']['options']) && is_array($config['cache']['rbac_repository']['options'])) {
            return $config['cache']['rbac_repository']['options'];
        }

        return [];
    }
}
