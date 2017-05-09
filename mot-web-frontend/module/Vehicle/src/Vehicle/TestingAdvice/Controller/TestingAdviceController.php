<?php

namespace Vehicle\TestingAdvice\Controller;

use Core\Controller\AbstractAuthActionController;
use Core\Routing\MotTestRoutes;
use Vehicle\TestingAdvice\Action\DisplayAdviceAction;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;

class TestingAdviceController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $displayAdviceAction;
    private $paramObfuscator;

    public function __construct(DisplayAdviceAction $displayAdviceAction, ParamObfuscator $paramObfuscator)
    {
        $this->displayAdviceAction = $displayAdviceAction;
        $this->paramObfuscator = $paramObfuscator;
    }

    public function displayAction()
    {
        $vehicleId = $this->params()->fromRoute('id');
        $noRegistration = $this->params()->fromQuery('noRegistration');
        $source = $this->params()->fromQuery('source');
        $motTestNumber = $this->params()->fromQuery('motTestNumber');
        $navigateFrom = $this->params()->fromQuery('navigateFrom');

        if ($navigateFrom == 'home-page') {
            // navigated from home page

            $backLinkUrl = '/';
            $backLinkLabel = 'Return home';
            $motTestResultsUrl = MotTestRoutes::of($this->url())->motTest($motTestNumber);
            $breadcrumbs = ['MOT testing' => $motTestResultsUrl, 'Testing advice for this vehicle' => ''];

            $vehicleId = $this->paramObfuscator->deobfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $vehicleId);
        } elseif ($motTestNumber) {
            // navigated from a running mot test

            $backLinkUrl = MotTestRoutes::of($this->url())->motTest($motTestNumber);
            $backLinkLabel = 'Back to MOT test results';
            $breadcrumbs = ['MOT test results' => $backLinkUrl, 'Testing advice for this vehicle' => ''];
        } else {
            // navigated from vehicle page, right before starting mot test

            $backLinkUrl = MotTestRoutes::of($this->url())->vehicleMotTestStartTest($vehicleId, $noRegistration, $source);
            $backLinkLabel = 'Back to confirm vehicle';
            $breadcrumbs = ['MOT testing' => $backLinkUrl, 'Testing advice for this vehicle' => ''];
            $vehicleId = $this->paramObfuscator->deobfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $vehicleId);
        }

        $result = $this->displayAdviceAction->execute((int) $vehicleId, $backLinkUrl, $backLinkLabel, $breadcrumbs);

        return $this->applyActionResult($result);
    }
}
