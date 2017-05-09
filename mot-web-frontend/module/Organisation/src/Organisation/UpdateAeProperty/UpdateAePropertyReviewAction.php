<?php

namespace Organisation\UpdateAeProperty;

use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\ReviewStepAction;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class UpdateAePropertyReviewAction implements AutoWireableInterface
{
    const AE_NAME_PROPERTY = 'name';
    const AE_CLASSES_PROPERTY = 'classes';

    /**
     * @var UpdateAePropertyProcessBuilder
     */
    private $processBuilder;

    private $reviewStepProcess;

    public function __construct(
        UpdateAePropertyProcessBuilder $processBuilder,
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
