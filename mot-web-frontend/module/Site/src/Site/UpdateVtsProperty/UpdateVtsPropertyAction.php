<?php

namespace Site\UpdateVtsProperty;

use Core\TwoStepForm\EditStepAction;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class UpdateVtsPropertyAction implements AutoWireableInterface
{
    const VTS_NAME_PROPERTY = 'name';
    const VTS_CLASSES_PROPERTY = 'classes';
    const VTS_STATUS_PROPERTY = 'status';
    const VTS_TYPE_PROPERTY = 'type';
    const VTS_EMAIL_PROPERTY = 'email';
    const VTS_ADDRESS_PROPERTY = 'address';
    const VTS_PHONE_PROPERTY = 'phone';
    const VTS_COUNTRY_PROPERTY = 'country';

    private $processBuilder;

    private $editStepProcess;

    public function __construct(
        UpdateVtsPropertyProcessBuilder $processBuilder,
        EditStepAction $editStepProcess
    ) {
        $this->processBuilder = $processBuilder;
        $this->editStepProcess = $editStepProcess;
    }

    public function execute($isPost, SingleStepProcessInterface $process, FormContextInterface $context, $formUuid, array $formData = [])
    {
        return $this->editStepProcess->execute($isPost, $process, $context, $formUuid, $formData);
    }
}
