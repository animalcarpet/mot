<?php

namespace Core\FormWizard;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class Wizard implements AutoWireableInterface
{
    private $stepList;

    public function __construct(StepList $stepList)
    {
        $this->stepList = $stepList;
    }

    public function setContext(WizardContextInterface $context)
    {
        $this->stepList = $this->stepList->map(function (AbstractStep $step) use ($context) {
            $step->setContext($context);

            return $step;
        });

        return $this;
    }

    public function setSessionStoreKey($sessionStoreKey)
    {
        $this->stepList = $this->stepList->map(function (AbstractStep $step) use ($sessionStoreKey) {
            $step->setSessionStoreKey($sessionStoreKey);

            return $step;
        });

        return $this;
    }

    public function process($stepName, $isPost, $formUuid = null, array $formData = [])
    {
        $step = $this->getStep($stepName);

        if ($this->hasValidAllPrevSteps($step, $formUuid) === false) {
            $validStep = $this->getPrevValidStep($step->getPrevStep(), $formUuid);

            return $validStep->getRoute(['formUuid' => $formUuid]);
        }

        if ($isPost) {
            $stepResult = $step->executePost($formData, $formUuid);
        } else {
            $stepResult = $step->executeGet($formUuid);
        }

        return $stepResult;
    }

    /**
     * @param AbstractStep $step
     * @param $formUuid
     *
     * @return AbstractStep
     */
    private function getPrevValidStep(AbstractStep $step, $formUuid)
    {
        while ($step->hasPrevStep()) {
            $step = $step->getPrevStep();

            if ($step->isValid($formUuid)) {
                return $step;
            }
        }

        return $step;
    }

    private function hasValidAllPrevSteps(AbstractStep $step, $formUuid)
    {
        while ($step->hasPrevStep()) {
            $step = $step->getPrevStep();
            if ($step->isValid($formUuid) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $stepName
     *
     * @return AbstractStep
     */
    private function getStep($stepName)
    {
        $stepCollection = $this->stepList->filter(function (AbstractStep $step) use ($stepName) {
            return $step->getName() === $stepName;
        });

        if (count($stepCollection) !== 1) {
            throw new \InvalidArgumentException(sprintf("Step with name '%s' not found", $stepName));
        }

        return $stepCollection->first();
    }
}
