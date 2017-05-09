<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Event\Service\EventStepService;
use Event\Service\EventSessionService;
use Event\Service\ManualEventService;
use Event\Factory\Service\EventSessionServiceFactory;
use Event\Factory\Service\EventStepServiceFactory;
use Event\Factory\Service\ManualEventServiceFactory;

return [
    'factories' => [
        EventStepService::class => EventStepServiceFactory::class,
        EventSessionService::class => EventSessionServiceFactory::class,
        ManualEventService::class => ManualEventServiceFactory::class,
    ],
];
