<?php

namespace Vehicle\UpdateVehicleProperty\Form\Wizard\Step;

use Core\FormWizard\LayoutData;
use Zend\View\Helper\Url;
use Core\FormWizard\AbstractStep as AbstractWizardStep;
use Core\FormWizard\StepResult;
use Core\FormWizard\WizardContextInterface;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use Vehicle\UpdateVehicleProperty\Form\Wizard\Context;
use Zend\Form\Form;

abstract class AbstractStep extends AbstractWizardStep
{
    protected $url;
    protected $formUuid;

    /**
     * @var Context
     */
    protected $context;

    public function __construct(Url $url)
    {
        parent::__construct();

        $this->url = $url;
    }

    /**
     * @param array $formData
     *
     * @return Form
     */
    abstract protected function createForm(array $formData = []);

    /**
     * @return LayoutData
     */
    abstract protected function getLayoutData();

    abstract protected function createViewModel(Form $form, $formUuid);

    /**
     * @return array
     */
    abstract protected function getPrePopulatedData();

    abstract protected function dataExists($formUuid);

    public function executeGet($formUuid = null)
    {
        $this->formUuid = $formUuid;
        if ($this->dataExists($formUuid)) {
            $formData = $this->getStoredData($formUuid);
        } else {
            $formData = $this->getPrePopulatedData();
        }

        $form = $this->createForm($formData);

        return $this->buildResult($form, $formUuid);
    }

    public function executePost(array $fromData, $formUuid = null)
    {
        $this->formUuid = $formUuid;
        $form = $this->createForm($fromData);

        $errors = [];
        if ($form->isValid()) {
            try {
                $formUuid = $this->saveData($form, $formUuid);

                return $this->getNextRoute($formUuid);
            } catch (ValidationException $exception) {
                $errors = $exception->getDisplayMessages();
            }
        }

        return $this->buildResult($form, $formUuid, $errors);
    }

    /**
     * @param Form $formData
     * @param $formUuid
     *
     * @return string
     */
    protected function saveData(Form $form, $formUuid)
    {
        return $this->formContainer->store($this->getSessionStoreKey(), $form->getData(), $formUuid);
    }

    protected function buildResult(Form $form, $formUuid, array $errors = [])
    {
        $layoutData = $this->getLayoutData();
        $viewModel = $this->createViewModel($form, $formUuid);

        return new StepResult($layoutData, $viewModel, $errors, 'vehicle/update-vehicle-property/edit');
    }

    public function setContext(WizardContextInterface $context)
    {
        TypeCheck::assertInstance($context, Context::class);

        return parent::setContext($context);
    }

    public function getStoredData($formUuid)
    {
        $data = $this->getAllStoredData($formUuid);

        if (array_key_exists($this->getName(), $data)) {
            return $data[$this->getName()];
        } else {
            return [];
        }
    }

    protected function getAllStoredData($formUuid)
    {
        $data = $this->formContainer->get($formUuid, $this->getSessionStoreKey());
        if ($data === null) {
            $data = [];
        }

        return $data;
    }

    public function isValid($formUuid)
    {
        $this->formUuid = $formUuid;

        $data = $this->getStoredData($formUuid);
        $form = $this->createForm($data);

        return $form->isValid();
    }

    protected function getBackRoute($formUuid = null)
    {
        return $this->getPrevStep()->getRoute(['formUuid' => $formUuid]);
    }

    protected function getBackUrl($formUuid = null)
    {
        return $this->getBackRoute($formUuid)->toString($this->url);
    }

    protected function getNextRoute($formUuid = null)
    {
        return $this->getNextStep()->getRoute(['formUuid' => $formUuid]);
    }

    protected function getNextUrl($formUuid = null)
    {
        return $this->getNextRoute($formUuid)->toString($this->url);
    }
}
