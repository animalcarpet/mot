<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace AccountApi\Factory\Service;

use AccountApi\Crypt\SecurityAnswerHashFunction;
use AccountApi\Mapper\SecurityQuestionMapper;
use AccountApi\Service\SecurityQuestionService;
use AccountApi\Service\Validator\PersonSecurityAnswerValidator;
use Doctrine\ORM\EntityManager;
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
    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return SecurityQuestionService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $config = $serviceLocator->get('config');
        $configKey = 'security_answer_verification_delay';

        if (!array_key_exists($configKey, $config)) {
            throw new \OutOfBoundsException(
                sprintf('Failed to find the required key "%s" from the config', $configKey)
            );
        }

        return new SecurityQuestionService(
            $serviceLocator->get(EntityManager::class)->getRepository(SecurityQuestion::class),
            new SecurityQuestionMapper(),
            $serviceLocator->get(PersonSecurityAnswerRecorder::class),
            $serviceLocator->get(EntityManager::class)->getRepository(Person::class),
            $serviceLocator->get(EntityManager::class)->getRepository(PersonSecurityAnswer::class),
            $serviceLocator->get(PersonSecurityAnswerValidator::class),
            $serviceLocator->get(ParamObfuscator::class),
            $serviceLocator->get(EntityManager::class),
            new SecurityAnswerHashFunction(),
            $config[$configKey]
        );
    }
}
