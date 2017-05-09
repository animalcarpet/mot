<?php

namespace Site\UpdateVtsProperty\Process\Form;

use DvsaCommon\Model\VtsStatus;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class StatusPropertyForm extends Form
{
    const FIELD_STATUS = UpdateVtsPropertyAction::VTS_STATUS_PROPERTY;
    const STATUS_EMPTY_MSG = 'you must choose a site status';

    private $statusElement;

    public function __construct()
    {
        parent::__construct(self::FIELD_STATUS);

        $statuses = VtsStatus::getStatuses();
        $options = $statuses;

        $this->statusElement = new Select();
        $this
            ->statusElement
            ->setDisableInArrayValidator(true)
            ->setAttribute('type', 'select')
            ->setValueOptions($options)
            ->setName(self::FIELD_STATUS)
            ->setLabel('Site status')
            ->setAttribute('id', 'vtsStatusSelectSet')
            ->setAttribute('required', true)
            ->setAttribute('group', true);

        $this->add($this->statusElement);

        $statusEmptyValidator = (new NotEmpty())->setMessage(self::STATUS_EMPTY_MSG, NotEmpty::IS_EMPTY);
        $statusInput = new Input(self::FIELD_STATUS);
        $statusInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($statusEmptyValidator);

        $filter = new InputFilter();
        $filter->add($statusInput);

        $this->setInputFilter($filter);
    }

    public function getStatusElement()
    {
        return $this->statusElement;
    }
}
