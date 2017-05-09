<?php

namespace AccountApi\Factory\Service\Validator;

use AccountApi\Service\Validator\ClaimValidator;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\SecurityQuestion;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use AccountApi\Service\SecurityQuestionService;

/**
 * Class ClaimValidatorFactory.
 */
class ClaimValidatorFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new ClaimValidator(
            $serviceLocator->get(SecurityQuestionService::class),
            $serviceLocator->get(EntityManager::class)->getRepository(SecurityQuestion::class)
        );
    }
}
