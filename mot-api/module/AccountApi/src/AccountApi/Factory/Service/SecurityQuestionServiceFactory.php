<?php

namespace AccountApi\Factory\Service;

use AccountApi\Service\Validator\PersonSecurityAnswerValidator;
use Doctrine\ORM\EntityManager;
use AccountApi\Mapper\SecurityQuestionMapper;
use AccountApi\Service\SecurityQuestionService;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSecurityAnswer;
use DvsaEntities\Entity\SecurityQuestion;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class SecurityQuestionServiceFactory
 * @package AccountApi\Factory
 */
class SecurityQuestionServiceFactory implements FactoryInterface
{
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return new SecurityQuestionService(
            $serviceLocator->get(EntityManager::class)->getRepository(SecurityQuestion::class),
            new SecurityQuestionMapper(),
            $serviceLocator->get(PersonSecurityAnswerRecorder::class),
            $serviceLocator->get(EntityManager::class)->getRepository(Person::class),
            $serviceLocator->get(EntityManager::class)->getRepository(PersonSecurityAnswer::class),
            $serviceLocator->get(PersonSecurityAnswerValidator::class),
            $serviceLocator->get(ParamObfuscator::class),
            $serviceLocator->get(EntityManager::class)
        );
    }
}
