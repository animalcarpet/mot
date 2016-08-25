<?php

namespace DvsaAuthentication\Factory;

use Doctrine\Common\Cache\Cache;
use Doctrine\ORM\EntityManager;
use DvsaAuthentication\IdentityFactory\CacheableIdentityFactory;
use DvsaAuthentication\IdentityFactory\DoctrineIdentityFactory;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\PersonRepository;
use DvsaFeature\FeatureToggles;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class IdentityFactoryFactory implements FactoryInterface
{
    const DEFAULT_TTL = 300;

    /**
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);
        $personRepository = $entityManager->getRepository(Person::class);
        $featureToggles = $serviceLocator->get('Feature\FeatureToggles');

        $identityFactory = new DoctrineIdentityFactory($personRepository, $featureToggles);

        $config = $serviceLocator->get('config');

        if ($this->isCacheEnabled($config)) {
            $identityFactory = new CacheableIdentityFactory(
                $identityFactory,
                $serviceLocator->get(Cache::class),
                $personRepository,
                $this->getTtl($config)
            );
        }

        return $identityFactory;
    }

    /**
     * @param array $config
     *
     * @return bool
     */
    private function isCacheEnabled($config)
    {
        return isset($config['cache']['identity_factory']['enabled']) && $config['cache']['identity_factory']['enabled'];
    }

    private function getTtl($config)
    {
        return isset($config['cache']['identity_factory']['options']['ttl'])
            ? (int) $config['cache']['identity_factory']['options']['ttl']
            : self::DEFAULT_TTL;
    }
}