<?php

namespace Dvsa\Mot\Frontend\MotTestModuleTest\View;

use Dvsa\Mot\Frontend\MotTestModule\View\DefectsContentBreadcrumbsBuilder;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ComponentCategoryCollection;
use Zend\Mvc\Router\Http\TreeRouteStack;
use Zend\Mvc\Router\RouteStackInterface;

class DefectsContentBreadcrumbsBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RouteStackInterface
     */
    private $routerMock;

    public function setUp()
    {
        $this->routerMock = new TreeRouteStack();
        $this->routerMock->setRoutes(require __DIR__.'/Fixtures/routes.php');
    }

    public function testBreadcrumbs()
    {
        $testBreadcrumbs = new DefectsContentBreadcrumbsBuilder($this->routerMock);

        $testBreadcrumbs->getContentBreadcrumbs(
            $this->getComponentCategoryCollection(),
            1
        );

        $this->assertInstanceOf(DefectsContentBreadcrumbsBuilder::class, $testBreadcrumbs);
    }

    /**
     * @return ComponentCategoryCollection
     */
    private function getComponentCategoryCollection()
    {
        return ComponentCategoryCollection::fromDataFromApi(
            $this->getTestItemSelectors(),
            true
        );
    }

    /**
     * @return array
     */
    private function getTestItemSelectors()
    {
        return [
            [
                'testItemSelector' => [
                    'sectionTestItemSelectorId' => 1,
                    'parentTestItemSelectorId' => 0,
                    'id' => 0,
                    'vehicleClasses' => [
                        '3', '4', '5',
                    ],
                    'descriptions' => [
                        'Description 1',
                        'Description 2',
                    ],
                    'name' => 'RFR name',
                    'description' => 'Cool description',
                ],
                'parentTestItemSelectors' => [

                ],
                'testItemSelectors' => [
                    1 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                    2 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name not tested',
                        'description' => 'Cool description2',
                    ],
                    3 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                ],
                'reasonsForRejection' => [
                    1 => [
                        'rfrId' => 1,
                        'testItemSelectorId' => 1,
                        'testItemSelectorName' => 'asd',
                        'description' => 'asd',
                        'advisoryText' => 'asd',
                        'inspectionManualReference' => '2.1.2',
                        'isAdvisory' => true,
                        'isPrsFail' => false,
                        'canBeDangerous' => true,
                    ],
                ],
            ],
            [
                'testItemSelector' => [
                    'sectionTestItemSelectorId' => 1,
                    'parentTestItemSelectorId' => 0,
                    'id' => 1,
                    'vehicleClasses' => [
                        '3', '4', '5',
                    ],
                    'descriptions' => [
                        'Description 1',
                        'Description 2',
                    ],
                    'name' => 'RFR name',
                    'description' => 'Cool description',
                ],
                'parentTestItemSelectors' => [

                ],
                'testItemSelectors' => [
                    1 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                    2 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name not tested',
                        'description' => 'Cool description2',
                    ],
                    3 => [
                        'sectionTestItemSelectorId' => 10,
                        'parentTestItemSelectorId' => 20,
                        'id' => 30,
                        'vehicleClasses' => [
                            '3', '4', '5',
                        ],
                        'descriptions' => [
                            'Description 1',
                            'Description 2',
                        ],
                        'name' => 'RFR name',
                        'description' => 'Cool description2',
                    ],
                ],
                'reasonsForRejection' => [
                    1 => [
                        'rfrId' => 1,
                        'testItemSelectorId' => 1,
                        'testItemSelectorName' => 'asd',
                        'description' => 'asd',
                        'advisoryText' => 'asd',
                        'inspectionManualReference' => '2.1.2',
                        'isAdvisory' => true,
                        'isPrsFail' => false,
                        'canBeDangerous' => true,
                    ],
                ],
            ],
        ];
    }
}
