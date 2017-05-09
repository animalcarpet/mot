<?php

namespace UserAdmin;

use UserAdmin\Factory\Service\DateOfBirthFilterServiceFactory;
use UserAdmin\Factory\Service\HelpdeskAccountAdminServiceFactory;
use UserAdmin\Factory\Service\IsEmailDuplicateServiceFactory;
use UserAdmin\Factory\Service\PersonRoleManagementServiceFactory;
use UserAdmin\Factory\UserAdminSessionManagerFactory;
use UserAdmin\Service\DateOfBirthFilterService;
use UserAdmin\Service\HelpdeskAccountAdminService;
use UserAdmin\Service\IsEmailDuplicateService;
use UserAdmin\Service\UserAdminSessionManager;
use UserAdmin\Service\PersonRoleManagementService;
use UserAdmin\Service\UserAdminSessionService;
use UserAdmin\Factory\Service\UserAdminSessionServiceFactory;

/**
 * Class Module.
 */
class Module
{
    public function getConfig()
    {
        return include __DIR__.'/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
    }

    public function getServiceConfig()
    {
        return [
            'factories' => [
                UserAdminSessionManager::class => UserAdminSessionManagerFactory::class,
                HelpdeskAccountAdminService::class => HelpdeskAccountAdminServiceFactory::class,
                PersonRoleManagementService::class => PersonRoleManagementServiceFactory::class,
                UserAdminSessionService::class => UserAdminSessionServiceFactory::class,
                IsEmailDuplicateService::class => IsEmailDuplicateServiceFactory::class,
                DateOfBirthFilterService::class => DateOfBirthFilterServiceFactory::class,
            ],
        ];
    }
}
