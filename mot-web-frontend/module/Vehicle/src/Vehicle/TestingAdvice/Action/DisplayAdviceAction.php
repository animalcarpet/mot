<?php

namespace Vehicle\TestingAdvice\Action;

use Core\ViewModel\Header\HeaderTertiaryList;
use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Service\VehicleService;
use Dvsa\Mot\ApiClient\Service\MotTestService;
use DvsaCommon\Date\DateTimeDisplayFormat;
use Vehicle\TestingAdvice\ViewModel\DisplayAdviseViewModel;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Configuration\MotConfig;
use Core\Action\ViewActionResult;

class DisplayAdviceAction implements AutoWireableInterface
{
    const PAGE_TITLE = 'Testing advice for this vehicle';

    private $vehicleService;
    private $motTestService;
    private $motConfig;

    public function __construct(
        VehicleService $vehicleService,
        MotTestService $motTestService,
        MotConfig $motConfig
    ) {
        $this->vehicleService = $vehicleService;
        $this->motTestService = $motTestService;
        $this->motConfig = $motConfig;
    }

    public function execute($vehicleId, $backLinkUrl, $backLinkLabel, array $breadcrumbs)
    {
        $vehicle = $this->vehicleService->getDvsaVehicleById($vehicleId);
        $testingAdvice = $this->vehicleService->getTestingAdvice($vehicleId);

        $viewModel = new DisplayAdviseViewModel($testingAdvice, $backLinkUrl, $backLinkLabel, $this->motConfig->get('testing_advice_survey_link'));

        $actionResult = new ViewActionResult();
        $actionResult->setViewModel($viewModel);
        $actionResult->setTemplate('vehicle/testing-advice/display.twig');
        $actionResult->layout()->setBreadcrumbs($breadcrumbs);
        $actionResult->layout()->setTemplate('layout/layout-govuk.phtml');
        $actionResult->layout()->setPageTitle(self::PAGE_TITLE);
        $actionResult->layout()->setPageTertiaryTitle($this->getTertiaryTitle($vehicle));

        return $actionResult;
    }

    private function getTertiaryTitle(DvsaVehicle $vehicle)
    {
        $tertiaryList = new HeaderTertiaryList();
        $tertiaryList->addElement($vehicle->getMakeAndModel())->bold();
        $tertiaryList->addElement($vehicle->getRegistration());
        $tertiaryList->addElement(sprintf('First used %s', DateTimeDisplayFormat::dateShort(new \DateTime($vehicle->getFirstUsedDate()))));

        return $tertiaryList;
    }
}
