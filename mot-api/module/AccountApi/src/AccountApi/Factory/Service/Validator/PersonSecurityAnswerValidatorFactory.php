<?php

namespace AccountApi\Factory\Service\Validator;

use AccountApi\Service\Validator\PersonSecurityAnswerValidator;
use Doctrine\ORM\EntityManager;
use DvsaEntities\Entity\SecurityQuestion;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class PersonSecurityAnswerValidatorFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new PersonSecurityAnswerValidator(
            $serviceLocator->get(EntityManager::class)->getRepository(SecurityQuestion::class)
        );
    }
}
