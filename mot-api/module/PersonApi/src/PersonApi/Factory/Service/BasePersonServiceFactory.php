<?php

namespace PersonApi\Factory\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\ContactDetailsService;
use DvsaEntities\Entity\Gender;
use DvsaEntities\Entity\Title;
use DvsaEntities\Entity\AuthenticationMethod;
use DvsaEntities\Mapper\PersonMapper as CommonEntitiesPersonMapper;
use PersonApi\Service\BasePersonService;
use PersonApi\Service\Validator\BasePersonValidator;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use DvsaCommonApi\Filter\XssFilter;

/**
 * Class BasePersonServiceFactory.
 */
class BasePersonServiceFactory implements FactoryInterface
{
    /**
     * @param \Zend\ServiceManager\ServiceLocatorInterface $serviceLocator
     *
     * @return \PersonApi\Service\BasePersonService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityManager = $serviceLocator->get(EntityManager::class);

        return new BasePersonService(
            $entityManager,
            $serviceLocator->get(BasePersonValidator::class),
            $serviceLocator->get(ContactDetailsService::class),
            new CommonEntitiesPersonMapper(
                $entityManager->getRepository(Title::class),
                $entityManager->getRepository(Gender::class),
                $entityManager->getRepository(AuthenticationMethod::class)
            ),
            $serviceLocator->get(XssFilter::class)
        );
    }
}
