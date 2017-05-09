<?php

namespace Organisation\UpdateAeProperty;

use Core\TwoStepForm\EditStepAction;
use Core\TwoStepForm\FormContextInterface;
use Core\TwoStepForm\SingleStepProcessInterface;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class UpdateAePropertyAction implements AutoWireableInterface
{
    const AE_NAME_PROPERTY = 'name';
    const AE_TRADING_NAME_PROPERTY = 'trading-name';
    const AE_BUSINESS_TYPE_PROPERTY = 'business-type';
    const AE_STATUS_PROPERTY = 'status';
    const AE_DVSA_AREA_OFFICE_STATUS_PROPERTY = 'areaoffice';
    const AE_CREATE_AEP_PROPERTY = 'add-principal';
    const AE_REGISTERED_ADDRESS_PROPERTY = 'registered-address';
    const AE_REGISTERED_EMAIL_PROPERTY = 'registered-email';
    const AE_REGISTERED_TELEPHONE_PROPERTY = 'registered-telephone';
    const AE_CORRESPONDENCE_ADDRESS_PROPERTY = 'correspondence-address';
    const AE_CORRESPONDENCE_EMAIL_PROPERTY = 'correspondence-email';
    const AE_CORRESPONDENCE_TELEPHONE_PROPERTY = 'correspondence-telephone';
    const AE_COMPANY_NUMBER_PROPERTY = 'company-number';

    private $processBuilder;

    private $editStepProcess;

    public function __construct(
        UpdateAePropertyProcessBuilder $processBuilder,
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
