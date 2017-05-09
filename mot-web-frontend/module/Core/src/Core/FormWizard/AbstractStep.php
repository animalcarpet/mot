<?php

namespace Core\FormWizard;

use Core\Action\RedirectToRoute;

abstract class AbstractStep
{
    /**
     * @var AbstractStep
     */
    protected $prevStep;

    /**
     * @var AbstractStep
     */
    protected $nextStep;

    /**
     * @var FormContainer
     */
    protected $formContainer;

    /**
     * @var string
     */
    protected $sessionStoreKey;

    /**
     * @var WizardContextInterface
     */
    protected $context;

    public function __construct()
    {
        $this->formContainer = new FormContainer();
    }

    abstract public function getName();

    abstract public function executeGet($formUuid = null);

    abstract public function executePost(array $formData, $formUuid = null);

    abstract public function isValid($formUuid);

    abstract public function getStoredData($formUuid);

    /**
     * @param array $queryParams
     *
     * @return RedirectToRoute
     */
    abstract public function getRoute(array $queryParams = []);

    public function getSessionStoreKey()
    {
        return $this->sessionStoreKey;
    }

    public function setSessionStoreKey($sessionStoreKey)
    {
        $this->sessionStoreKey = $sessionStoreKey;

        return $this;
    }

    public function setContext(WizardContextInterface $context)
    {
        $this->context = $context;

        return $this;
    }

    public function hasPrevStep()
    {
        return $this->prevStep !== null;
    }

    public function getPrevStep()
    {
        return $this->prevStep;
    }

    public function setPrevStep(AbstractStep $step)
    {
        $this->prevStep = $step;

        return $this;
    }

    public function hasNextStep()
    {
        return $this->nextStep !== null;
    }

    public function getNextStep()
    {
        return $this->nextStep;
    }

    public function setNextStep(AbstractStep $step)
    {
        $this->nextStep = $step;

        return $this;
    }

    protected function getPrevStepWithName($name)
    {
        $step = $this;
        while ($step->hasPrevStep()) {
            if ($step->getPrevStep()->getName() === $name) {
                return $step->getPrevStep();
            }

            $step = $step->getPrevStep();
        }

        throw new \InvalidArgumentException(sprintf("Step with name '%s' not found", $name));
    }
}
