<?php

use Dvsa\Mot\Frontend\MotTestModule\Controller\AddDefectController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\AddManualAdvisoryController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\ContingencyTestController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\DefectCategoriesController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\EditDefectController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\OdometerController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\RemoveDefectController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\SearchDefectsController;
use Dvsa\Mot\Frontend\MotTestModule\Controller\SurveyPageController;
use Dvsa\Mot\Frontend\MotTestModule\Module;
use DvsaCommon\Constants\MotTestNumberConstraint;

return [
    'contingency' => [
        'type' => 'segment',
        'options' => [
            'route' => '/contingency',
            'defaults' => [
                'controller' => ContingencyTestController::class,
                'action' => 'index',
            ],
        ],
    ],
    'contingency-error' => [
        'type' => 'segment',
        'options' => [
            'route' => '/contingency-error',
            'defaults' => [
                'controller' => ContingencyTestController::class,
                'action' => 'error',
            ],
        ],
    ],
    Module::TOP_LEVEL_ROUTE => [
        'type' => 'segment',
        'options' => [
            'route' => '/mot-test/:motTestNumber/defects',
            'constraints' => [
                'motTestNumber' => MotTestNumberConstraint::FORMAT_REGEX,
            ],
            'defaults' => [
                'controller' => DefectCategoriesController::class,
                'action' => 'redirectToCategoriesIndex',
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'edit-defect' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:defectItemId/edit',
                    'constraints' => [
                        'defectItemId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => EditDefectController::class,
                        'action' => 'edit',
                    ],
                ],
            ],
            'remove-defect' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/:defectItemId/remove',
                    'constraints' => [
                        'defectItemId' => '[0-9]+',
                    ],
                    'defaults' => [
                        'controller' => RemoveDefectController::class,
                        'action' => 'remove',
                    ],
                ],
            ],
            'categories' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/categories',
                    'defaults' => [
                        'controller' => DefectCategoriesController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'category' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:categoryId',
                            'constraints' => [
                                'categoryId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => DefectCategoriesController::class,
                                'action' => 'category',
                            ],
                        ],
                        'may_terminate' => true,
                        'child_routes' => [
                            'add-manual-advisory' => [
                                'type' => 'literal',
                                'priority' => 1000,
                                'options' => [
                                    'route' => '/add/0/advisory',
                                    'defaults' => [
                                        'controller' => AddManualAdvisoryController::class,
                                        'action' => 'add',
                                    ],
                                ],
                            ],
                            'add-defect' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/add/:defectId/:type',
                                    'constraints' => [
                                        'defectId' => '[0-9]+',
                                        'type' => '[a-zA-Z]+',
                                    ],
                                    'defaults' => [
                                        'controller' => AddDefectController::class,
                                        'action' => 'add',
                                    ],
                                ],
                            ],
                            'edit-defect' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:defectItemId/edit',
                                    'constraints' => [
                                        'defectItemId' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'controller' => EditDefectController::class,
                                        'action' => 'edit',
                                    ],
                                ],
                            ],
                            'remove-defect' => [
                                'type' => 'segment',
                                'options' => [
                                    'route' => '/:defectItemId/remove',
                                    'constraints' => [
                                        'defectItemId' => '[0-9]+',
                                    ],
                                    'defaults' => [
                                        'controller' => RemoveDefectController::class,
                                        'action' => 'remove',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'search' => [
                'type' => 'literal',
                'options' => [
                    'route' => '/search',
                    'defaults' => [
                        'controller' => SearchDefectsController::class,
                        'action' => 'index',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'add-manual-advisory' => [
                        'type' => 'literal',
                        'priority' => 1000,
                        'options' => [
                            'route' => '/add/0/advisory',
                            'defaults' => [
                                'controller' => AddManualAdvisoryController::class,
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'add-defect' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/add/:defectId/:type',
                            'constraints' => [
                                'defectId' => '[0-9]+',
                                'type' => '[a-zA-Z]+',
                            ],
                            'defaults' => [
                                'controller' => AddDefectController::class,
                                'action' => 'add',
                            ],
                        ],
                    ],
                    'edit-defect' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:defectItemId/edit',
                            'constraints' => [
                                'defectItemId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => EditDefectController::class,
                                'action' => 'edit',
                            ],
                        ],
                    ],
                    'remove-defect' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/:defectItemId/remove',
                            'constraints' => [
                                'defectItemId' => '[0-9]+',
                            ],
                            'defaults' => [
                                'controller' => RemoveDefectController::class,
                                'action' => 'remove',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'survey' => [
        'type' => 'segment',
        'options' => [
            'route' => '/survey',
            'defaults' => [
                'controller' => SurveyPageController::class,
                'action' => 'index',
            ],
        ],
        'may_terminate' => true,
        'child_routes' => [
            'thanks' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/thanks',
                    'defaults' => [
                        'controller' => SurveyPageController::class,
                        'action' => 'thanks',
                    ],
                ],
            ],
            'reports' => [
                'type' => 'segment',
                'options' => [
                    'route' => '/reports',
                    'defaults' => [
                        'controller' => SurveyPageController::class,
                        'action' => 'reports',
                    ],
                ],
                'may_terminate' => true,
                'child_routes' => [
                    'downloadCsv' => [
                        'type' => 'segment',
                        'options' => [
                            'route' => '/download/:month',
                            'defaults' => [
                                'controller' => SurveyPageController::class,
                                'action' => 'downloadReportCsv',
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    'odometer' => [
        'type' => 'segment',
        'options' => [
            'route' => '/mot-test/:tID/odometer/update',
            'constraints' => [
                'tID' => MotTestNumberConstraint::FORMAT_REGEX,
            ],
            'defaults' => [
                'controller' => OdometerController::class,
                'action' => 'index',
            ],
        ],
    ],
];