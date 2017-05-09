<?php

namespace Vehicle\UpdateVehicleProperty\Form\Wizard;

use Core\FormWizard\StepList;
use Core\FormWizard\Wizard;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\ReviewMakeAndModelStep;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateMakeStep;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Step\UpdateModelStep;

class UpdateMakeAndModelWizard extends Wizard implements AutoWireableInterface
{
    public function __construct(
        UpdateMakeStep $updateMakeStep,
        UpdateModelStep $updateModelStep,
        ReviewMakeAndModelStep $reviewMakeAndModelStep
    ) {
        $updateMakeStep->setNextStep($updateModelStep);

        $updateModelStep
            ->setPrevStep($updateMakeStep)
            ->setNextStep($reviewMakeAndModelStep);

        $reviewMakeAndModelStep->setPrevStep($updateModelStep);

        $stepList = new StepList([
            $updateMakeStep->getName() => $updateMakeStep,
            $updateModelStep->getName() => $updateModelStep,
            $reviewMakeAndModelStep->getName() => $reviewMakeAndModelStep,
        ]);

        parent::__construct($stepList);
    }
}
