<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Dvsa\Mot\Api\RegistrationModule\Factory\Service\BusinessRoleAssignerFactory;
use Dvsa\Mot\Api\RegistrationModule\Factory\Service\ContactDetailsCreatorFactory;
use Dvsa\Mot\Api\RegistrationModule\Factory\Service\OpenAMIdentityCreatorFactory;
use Dvsa\Mot\Api\RegistrationModule\Factory\Service\PersonCreatorFactory;
use Dvsa\Mot\Api\RegistrationModule\Factory\Service\PersonSecurityAnswerRecorderFactory;
use Dvsa\Mot\Api\RegistrationModule\Factory\Service\RegistrationServiceFactory;
use Dvsa\Mot\Api\RegistrationModule\Factory\Service\UsernameGeneratorFactory;
use Dvsa\Mot\Api\RegistrationModule\Factory\Validator\RegistrationValidatorFactory;
use Dvsa\Mot\Api\RegistrationModule\Service\BusinessRoleAssigner;
use Dvsa\Mot\Api\RegistrationModule\Service\ContactDetailsCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\OpenAMIdentityCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonCreator;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use Dvsa\Mot\Api\RegistrationModule\Service\RegistrationService;
use Dvsa\Mot\Api\RegistrationModule\Service\UsernameGenerator;
use Dvsa\Mot\Api\RegistrationModule\Validator\RegistrationValidator;
use MailerApi\Factory\Logic\UserNameCreatorFactory;
use MailerApi\Logic\UsernameCreator;

return [
    'factories' => [
        BusinessRoleAssigner::class => BusinessRoleAssignerFactory::class,
        ContactDetailsCreator::class => ContactDetailsCreatorFactory::class,
        OpenAMIdentityCreator::class => OpenAMIdentityCreatorFactory::class,
        PersonCreator::class => PersonCreatorFactory::class,
        PersonSecurityAnswerRecorder::class => PersonSecurityAnswerRecorderFactory::class,
        RegistrationService::class => RegistrationServiceFactory::class,
        RegistrationValidator::class => RegistrationValidatorFactory::class,
        RegistrationService::class => RegistrationServiceFactory::class,
        UsernameGenerator::class => UsernameGeneratorFactory::class,
        UsernameCreator::class => UserNameCreatorFactory::class,
    ],
];
