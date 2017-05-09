<?php

namespace Site\UpdateVtsProperty;

use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\ReviewStepAction;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class UpdateVtsPropertyReviewAction implements AutoWireableInterface
{
    const VTS_NAME_PROPERTY = 'name';
    const VTS_CLASSES_PROPERTY = 'classes';

    private $processBuilder;

    private $reviewStepProcess;

    public function __construct(
        UpdateVtsPropertyProcessBuilder $processBuilder,
        ReviewStepAction $reviewStepProcess
    ) {
        $this->processBuilder = $processBuilder;
        $this->reviewStepProcess = $reviewStepProcess;
    }

    public function execute($isPost, SingleStepProcessInterface $process, FormContextInterface $context, $formUuid)
    {
        return $this->reviewStepProcess->execute($isPost, $process, $context, $formUuid);
    }
}
