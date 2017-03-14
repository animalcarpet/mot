<?php

namespace Vehicle\TestingAdvice\Controller;

use Core\Controller\AbstractAuthActionController;
use Core\Routing\MotTestRoutes;
use Vehicle\TestingAdvice\Action\DisplayAdviceAction;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Obfuscate\ParamObfuscator;

class AdviceController extends AbstractAuthActionController implements AutoWireableInterface
{
    private $displayAdviceAction;
    private $paramObfuscator;

    public function __construct(DisplayAdviceAction $displayAdviceAction, ParamObfuscator $paramObfuscator)
    {
        $this->displayAdviceAction = $displayAdviceAction;
        $this->paramObfuscator = $paramObfuscator;
    }

    public function displayAction() {
        $vehicleId = $this->params()->fromRoute("id");
        $noRegistration = $this->params()->fromQuery("noRegistration");
        $source = $this->params()->fromQuery("source");
        $motTestNumber = $this->params()->fromQuery("motTestNumber");

        if ($motTestNumber) {
            $backLinkUrl = MotTestRoutes::of($this->url())->motTest($motTestNumber);
            $backLinkLbel = "Back to MOT test results";
        } else {
            $backLinkUrl = MotTestRoutes::of($this->url())->vehicleMotTestStartTest($vehicleId, $noRegistration, $source);
            $backLinkLbel = "Back to confirm vehicle";
            $vehicleId = $this->paramObfuscator->deobfuscateEntry(ParamObfuscator::ENTRY_VEHICLE_ID, $vehicleId);
        }

        $result = $this->displayAdviceAction->execute((int) $vehicleId, $backLinkUrl, $backLinkLbel);

        return $this->applyActionResult($result);
    }
}
