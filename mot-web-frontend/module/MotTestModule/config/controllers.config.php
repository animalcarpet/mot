<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

use Dvsa\Mot\Frontend\MotTestModule\Controller\AddDefectController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\AddManualAdvisoryController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\ContingencyTestController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\DefectCategoriesController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\EditDefectController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\MotTestResultsController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\RemoveDefectController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\RepairDefectController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\SearchDefectsController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\SurveyPageController;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\AddDefectControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\AddManualAdvisoryControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Controller\OdometerController;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\ContingencyTestControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\DefectCategoriesControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\EditDefectControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\MotTestResultsControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\RemoveDefectControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\RepairDefectControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\SearchDefectsControllerFactory;
use Dvsa\Mot\Frontend\MotTestModule\Factory\Controller\SurveyPageControllerFactory;

return [
    'invokables' => [
        OdometerController::class => OdometerController::class,
    ],
    'factories' => [
        AddDefectController::class => AddDefectControllerFactory::class,
        AddManualAdvisoryController::class => AddManualAdvisoryControllerFactory::class,
        ContingencyTestController::class => ContingencyTestControllerFactory::class,
        DefectCategoriesController::class => DefectCategoriesControllerFactory::class,
        EditDefectController::class => EditDefectControllerFactory::class,
        MotTestResultsController::class => MotTestResultsControllerFactory::class,
        RemoveDefectController::class => RemoveDefectControllerFactory::class,
        RepairDefectController::class => RepairDefectControllerFactory::class,
        SearchDefectsController::class => SearchDefectsControllerFactory::class,
        SurveyPageController::class => SurveyPageControllerFactory::class,
    ],
];
