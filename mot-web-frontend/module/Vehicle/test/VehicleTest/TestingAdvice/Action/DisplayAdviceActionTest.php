<?php

namespace VehicleTest\TestingAdvice\Action;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\VehicleTestingData\TestingAdvice;
use Dvsa\Mot\ApiClient\Resource\Item\VehicleTestingData\TestingAdviceCategory;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use DvsaCommonTest\Builder\DvsaVehicleBuilder;
use DvsaCommonTest\TestUtils\XMock;
use Vehicle\TestingAdvice\Action\DisplayAdviceAction;
use Vehicle\TestingAdvice\ViewModel\DisplayAdviseViewModel;
use Core\Action\ViewActionResult;
use DvsaCommon\Configuration\MotConfig;

class DisplayAdviceActionTest extends \PHPUnit_Framework_TestCase
{
    const BACK_LINK_URL = 'www.back-link.com';
    const BACK_LINK_LABEL = 'Back to home';
    const FEEDBACK_LINK = 'www.survey.com';

    /* @var DisplayAdviceAction */
    private $displayAction;
    private $testingAdvice;

    public function setUp()
    {
        $dvsaVehicleBuilder = new DvsaVehicleBuilder();

        $vehicleStd = $dvsaVehicleBuilder->getEmptyVehicleStdClass();
        $vehicleStd->id = 15;
        $vehicleStd->vehicleClass = new \stdClass();

        $category = new TestingAdviceCategory();
        $category->setName('The Automotive Engine');
        $category->setContents(['Most common engines have 4, 6, or 8 pistons', 'The crankshaft is connected to the pistons']);

        $advice = new TestingAdvice();
        $advice->setCategories([$category]);
        $this->testingAdvice = $advice;

        $vehicleService = XMock::of(VehicleService::class);
        $vehicleService->method('getDvsaVehicleById')->willReturn(new DvsaVehicle($vehicleStd));
        $vehicleService->method('getTestingAdvice')->willReturn($advice);

        $motConfig = new MotConfig(['testing_advice_survey_link' => self::FEEDBACK_LINK]);

        $this->displayAction = new DisplayAdviceAction(
            $vehicleService,
            XMock::of(MotTestService::class),
            $motConfig
        );
    }

    public function test_execute_returnsActionResult()
    {
        $breadcrumbs = ['bread' => '', 'crumbs' => ''];

        $actionResult = $this->displayAction->execute(1, self::BACK_LINK_URL, self::BACK_LINK_LABEL, $breadcrumbs);

        $this->assertInstanceOf(ViewActionResult::class, $actionResult);
        /** @var DisplayAdviseViewModel $viewModel */
        $viewModel = $actionResult->getViewModel();
        $this->assertEquals(self::BACK_LINK_LABEL, $viewModel->getBackLinkLabel());
        $this->assertEquals(self::BACK_LINK_URL, $viewModel->getBackLinkUrl());
        $this->assertEquals(self::FEEDBACK_LINK, $viewModel->getFeedbackLink());
        $this->assertEquals($this->testingAdvice, $viewModel->getTestingAdvice());
        $this->assertEquals($breadcrumbs, $actionResult->layout()->getBreadcrumbs());
    }
}
