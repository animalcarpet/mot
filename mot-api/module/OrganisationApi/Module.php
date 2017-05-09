<?php

namespace OrganisationApi;

use DvsaCommon\Factory\AutoWire\AutoWireFactory;
use DvsaCommon\Utility\Hydrator;
use DvsaCommonApi\Service\AddressService;
use DvsaCommonApi\Service\ContactDetailsService;
use OrganisationApi\Model\NominationVerifier;
use OrganisationApi\Model\Operation\DirectNominationOperation;
use OrganisationApi\Model\Operation\ConditionalNominationOperation;
use OrganisationApi\Model\RoleAvailability;
use OrganisationApi\Service\AuthorisedExaminerPrincipalService;
use OrganisationApi\Service\AuthorisedExaminerService;
use OrganisationApi\Service\AuthorisedExaminerSlotService;
use OrganisationApi\Service\AuthorisedExaminerStatusService;
use OrganisationApi\Service\Mapper\PersonContactMapper;
use OrganisationApi\Service\MotTestLogService;
use OrganisationApi\Service\NominateRoleServiceBuilder;
use OrganisationApi\Service\OrganisationNominationNotificationService;
use OrganisationApi\Service\OrganisationPositionService;
use OrganisationApi\Service\OrganisationRoleService;
use OrganisationApi\Service\OrganisationService;
use OrganisationApi\Service\OrganisationSlotUsageService;
use OrganisationApi\Service\SiteLinkService;
use OrganisationApi\Service\SiteService;
use OrganisationApi\Service\OrganisationEventService;
use OrganisationApi\Factory\Service\OrganisationEventServiceFactory;
use OrganisationApi\Factory\Service as ServiceX;
use OrganisationApi\Factory\Model as ModelX;

/**
 * Class Module.
 */
class Module
{
    public function getAutoloaderConfig()
    {
    }

    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                Hydrator::class => \OrganisationApi\Factory\HydratorFactory::class,
                OrganisationService::class => ServiceX\OrganisationServiceFactory::class,
                AddressService::class => ServiceX\AddressServiceFactory::class,
                ContactDetailsService::class => ServiceX\ContactDetailsServiceFactory::class,
                AuthorisedExaminerService::class => ServiceX\AuthorisedExaminerServiceFactory::class,
                AuthorisedExaminerSearchService::class => ServiceX\AuthorisedExaminerSearchServiceFactory::class,
                AuthorisedExaminerPrincipalService::class => ServiceX\AuthorisedExaminerPrincipalServiceFactory::class,
                AuthorisedExaminerSlotService::class => ServiceX\AuthorisedExaminerSlotServiceFactory::class,
                AuthorisedExaminerStatusService::class => ServiceX\AuthorisedExaminerStatusServiceFactory::class,
                OrganisationPositionService::class => ServiceX\OrganisationPositionServiceFactory::class,
                OrganisationSlotUsageService::class => ServiceX\OrganisationSlotUsageServiceFactory::class,
                OrganisationRoleService::class => ServiceX\OrganisationRoleServiceFactory::class,
                RoleAvailability::class => ModelX\RoleAvailabilityFactory::class,
                NominateRoleServiceBuilder::class => ServiceX\NominateRoleServiceBuilderFactory::class,
                ConditionalNominationOperation::class => ServiceX\NominateByRequestOperationFactory::class,
                DirectNominationOperation::class => ServiceX\DirectNominationOperationFactory::class,
                OrganisationNominationNotificationService::class => ServiceX\OrganisationNominationServiceFactory::class,
                NominationVerifier::class => ServiceX\NominateVerifierFactory::class,
                SiteService::class => ServiceX\SiteServiceFactory::class,
                MotTestLogService::class => ServiceX\MotTestLogServiceFactory::class,
                SiteLinkService::class => ServiceX\SiteLinkServiceFactory::class,
                OrganisationEventService::class => OrganisationEventServiceFactory::class,

            ],
            'invokables' => [
                PersonContactMapper::class => PersonContactMapper::class,
            ],
            'abstract_factories' => [
                AutoWireFactory::class,
            ],
        ];
    }
}
