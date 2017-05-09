<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */
use Event\Controller\EventController;
use Event\Controller\EventCompletedController;
use Event\Controller\EventOutcomeController;
use Event\Controller\EventSummaryController;
use Event\Controller\EventRecordController;
use Event\Factory\Controllers\EventCompletedControllerFactory;
use Event\Factory\Controllers\EventControllerFactory;
use Event\Factory\Controllers\EventOutcomeControllerFactory;
use Event\Factory\Controllers\EventSummaryControllerFactory;
use Event\Factory\Controllers\EventRecordControllerFactory;

return [
    'factories' => [
        EventController::class => EventControllerFactory::class,
        EventCompletedController::class => EventCompletedControllerFactory::class,
        EventOutcomeController::class => EventOutcomeControllerFactory::class,
        EventSummaryController::class => EventSummaryControllerFactory::class,
        EventRecordController::class => EventRecordControllerFactory::class,
    ],
];
