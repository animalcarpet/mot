<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Factory\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\RegistrationModule\Service\BusinessRoleAssigner;
use Dvsa\Mot\Api\RegistrationModule\Service\ContactDetailsCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\OpenAMIdentityCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\RegistrationService;
use Dvsa\Mot\Api\RegistrationModule\Validator\RegistrationValidator;
use DvsaApplicationLogger\Log\Logger;
use MailerApi\Logic\UsernameCreator;
use PersonApi\Service\DuplicateEmailCheckerService;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class RegistrationServiceFactory.
 */
class RegistrationServiceFactory implements FactoryInterface
{
    /**
     * Create service.
     *
     * @param ServiceLocatorInterface $serviceLocator
     *
     * @return RegistrationService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $serviceLocator->get(EntityManager::class);

        /** @var Logger $applicationLogger */
        $applicationLogger = $serviceLocator->get('Application/Logger');

        /** @var OpenAMIdentityCreator $openAMService */
        $openAMService = $serviceLocator->get(OpenAMIdentityCreator::class);

        /** @var RegistrationValidator $registrationValidator */
        $registrationValidator = $serviceLocator->get(RegistrationValidator::class);

        /** @var PersonCreator $personService */
        $personService = $serviceLocator->get(PersonCreator::class);

        /** @var BusinessRoleAssigner $roleAssigner */
        $roleAssigner = $serviceLocator->get(BusinessRoleAssigner::class);

        /** @var ContactDetailsCreator $contactDetailCreator */
        $contactDetailCreator = $serviceLocator->get(ContactDetailsCreator::class);

        /*
         * Not used by this service but required by the UsernameCreator logic
         */
        $mailerLogic = $serviceLocator->get(UsernameCreator::class);

        $duplicateEmailChecker = $serviceLocator->get(DuplicateEmailCheckerService::class);

        $service = new RegistrationService(
            $entityManager,
            $applicationLogger,
            $openAMService,
            $registrationValidator,
            $personService,
            $roleAssigner,
            $contactDetailCreator,
            $mailerLogic,
            $duplicateEmailChecker
        );

        return $service;
    }
}
