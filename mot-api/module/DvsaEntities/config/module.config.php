<?php

use DvsaEntities\Audit\EntityAuditListener;
use DvsaEntities\Factory\Audit\EntityAuditListenerFactory;
use DvsaEntities\Factory\Repository\AuthForAeStatusRepositoryFactory;
use DvsaEntities\Factory\Repository\MotTestingCertificateRepositoryFactory;
use DvsaEntities\Factory\Repository\AuthorisationForTestingMotRepositoryFactory;
use DvsaEntities\Factory\Repository\AuthorisationForTestingMotStatusRepositoryFactory;
use DvsaEntities\Factory\Repository\QualificationAnnualCertificateRepositoryFactory;
use DvsaEntities\Factory\Repository\RfrDeficiencyCategoryRepositoryFactory;
use DvsaEntities\Factory\Repository\SiteRiskAssessmentRepositoryFactory;
use DvsaEntities\Factory\Repository\TqiRfrCountRepositoryFactory;
use DvsaEntities\Factory\Repository\TqiTestCountRepositoryFactory;
use DvsaEntities\Factory\Repository\VehicleClassRepositoryFactory;
use DvsaEntities\Factory\Repository\VehicleClassGroupRepositoryFactory;
use DvsaEntities\Factory\Repository\PersonRepositoryFactory;
use DvsaEntities\Factory\Repository\EventPersonMapRepositoryFactory;
use DvsaEntities\Factory\Repository\CompanyTypeRepositoryFactory;
use DvsaEntities\Factory\Repository\OrganisationContactTypeRepositoryFactory;
use DvsaEntities\Factory\Repository\OrganisationRepositoryFactory;
use DvsaEntities\Factory\Repository\PhoneContactTypeRepositoryFactory;
use DvsaEntities\Factory\Repository\RbacRepositoryFactory;
use DvsaEntities\Factory\Repository\SiteRepositoryFactory;
use DvsaEntities\Repository\AuthForAeStatusRepository;
use DvsaEntities\Repository\CompanyTypeRepository;
use DvsaEntities\Repository\OrganisationContactTypeRepository;
use DvsaEntities\Repository\OrganisationRepository;
use DvsaEntities\Repository\PhoneContactTypeRepository;
use DvsaEntities\Repository\QualificationAnnualCertificateRepository;
use DvsaEntities\Repository\RbacRepository;
use DvsaEntities\Repository\RfrDeficiencyCategoryRepository;
use DvsaEntities\Repository\SiteRepository;
use DvsaEntities\Repository\QualificationAwardRepository;
use DvsaEntities\Repository\SiteRiskAssessmentRepository;
use DvsaEntities\Repository\TqiRfrCountRepository;
use DvsaEntities\Repository\TqiTestCountRepository;
use DvsaEntities\Repository\VehicleClassGroupRepository;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\EventPersonMapRepository;
use DvsaEntities\Repository\AuthorisationForTestingMotRepository;
use DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository;
use DvsaEntities\Repository\VehicleClassRepository;
use DvsaEntities\Repository\MotTestStatusRepository;
use DvsaEntities\Factory\Repository\MotTestStatusRepositoryFactory;
use DvsaEntities\Repository\NotificationRepository;
use DvsaEntities\Factory\Repository\NotificationRepositoryFactory;

return [
    'service_manager' => [
        'factories' => [
            EntityAuditListener::class => EntityAuditListenerFactory::class,
            RbacRepository::class => RbacRepositoryFactory::class,
            OrganisationContactTypeRepository::class => OrganisationContactTypeRepositoryFactory::class,
            CompanyTypeRepository::class => CompanyTypeRepositoryFactory::class,
            OrganisationRepository::class => OrganisationRepositoryFactory::class,
            PhoneContactTypeRepository::class => PhoneContactTypeRepositoryFactory::class,
            AuthForAeStatusRepository::class => AuthForAeStatusRepositoryFactory::class,
            QualificationAwardRepository::class => MotTestingCertificateRepositoryFactory::class,
            AuthorisationForTestingMotRepository::class => AuthorisationForTestingMotRepositoryFactory::class,
            AuthorisationForTestingMotStatusRepository::class => AuthorisationForTestingMotStatusRepositoryFactory::class,
            VehicleClassRepository::class => VehicleClassRepositoryFactory::class,
            VehicleClassGroupRepository::class => VehicleClassGroupRepositoryFactory::class,
            PersonRepository::class => PersonRepositoryFactory::class,
            SiteRepository::class => SiteRepositoryFactory::class,
            SiteRiskAssessmentRepository::class => SiteRiskAssessmentRepositoryFactory::class,
            EventPersonMapRepository::class => EventPersonMapRepositoryFactory::class,
            QualificationAnnualCertificateRepository::class => QualificationAnnualCertificateRepositoryFactory::class,
            MotTestStatusRepository::class => MotTestStatusRepositoryFactory::class,
            TqiRfrCountRepository::class => TqiRfrCountRepositoryFactory::class,
            TqiTestCountRepository::class => TqiTestCountRepositoryFactory::class,
            NotificationRepository::class => NotificationRepositoryFactory::class,
            RfrDeficiencyCategoryRepository::class => RfrDeficiencyCategoryRepositoryFactory::class
        ],
    ],
    'doctrine' => [
        'eventmanager' => [
            'orm_default' => [
                'subscribers' => [
                    EntityAuditListener::class,
                ],
            ],
        ],
        'driver' => [
            'entities' => [
                'class' => \Doctrine\ORM\Mapping\Driver\AnnotationDriver::class,
                'cache' => 'array',
                'paths' => [
                    __DIR__.'/../src/DvsaEntities/Entity',
                ],
            ],
            'orm_default' => [
                'drivers' => [
                    'DvsaEntities\Entity' => 'entities',
                ],
            ],
        ],
        'configuration' => [
            'orm_default' => [
                'types' => [
                    'datetime' => \Doctrine\DBAL\Types\VarDateTimeType::class,
                    'datetimemicro' => \DvsaEntities\Type\DateTimeMicroType::class,
                    'Time' => \DvsaEntities\Type\TimeType::class,
                ],
                'string_functions' => [
                    'REGEXP' => \DvsaEntities\CustomDql\Functions\Regexp::class,
                    'DATE' => \DvsaEntities\CustomDql\Functions\Date::class,
                    'YEAR' => \DvsaEntities\CustomDql\Functions\Year::class,
                ],
            ],
        ],
    ],
];
