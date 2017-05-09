<?php

namespace Organisation\UpdateAeProperty\Process\Form;

use Core\Catalog\Authorisation\AuthForAuthorisedExaminerStatusCatalog;
use DvsaCommon\Model\AuthorisationForAuthorisedExaminerStatus;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\Form\Element\Select;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;

class StatusPropertyForm extends Form
{
    const FIELD_STATUS = UpdateAePropertyAction::AE_STATUS_PROPERTY;
    const STATUS_EMPTY_MSG = 'you must choose a status';

    /**
     * @var AuthForAuthorisedExaminerStatusCatalog
     */
    private $authForAuthorisedExaminerStatusCatalog;

    private $statusElement;

    public function __construct(AuthForAuthorisedExaminerStatusCatalog $authForAuthorisedExaminerStatusCatalog)
    {
        parent::__construct(self::FIELD_STATUS);
        $this->authForAuthorisedExaminerStatusCatalog = $authForAuthorisedExaminerStatusCatalog;

        $options = [];
        foreach (AuthorisationForAuthorisedExaminerStatus::getPossibleAuthForAuthorisedExaminerStatuses() as $statusCode) {
            $status = $authForAuthorisedExaminerStatusCatalog->getByCode($statusCode);
            if ($status) {
                $options[$status->getCode()] = $status->getName();
            }
        }

        $this->statusElement = new Select();
        $this
            ->statusElement
            ->setDisableInArrayValidator(true)
            ->setAttribute('type', 'select')
            ->setValueOptions($options)
            ->setName(self::FIELD_STATUS)
            ->setLabel('Status')
            ->setAttribute('id', 'aeStatusSelectSet')
            ->setAttribute('group', true);

        $this->add($this->statusElement);

        $statusEmptyValidator = (new NotEmpty())->setMessage(self::STATUS_EMPTY_MSG, NotEmpty::IS_EMPTY);
        $statusInArrayValidator = (new InArray())
            ->setHaystack(AuthorisationForAuthorisedExaminerStatus::getPossibleAuthForAuthorisedExaminerStatuses()
            );
        $statusInArrayValidator->setMessage(' you must choose a status');

        $statusInput = new Input(self::FIELD_STATUS);
        $statusInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($statusEmptyValidator)
            ->attach($statusInArrayValidator);

        $filter = new InputFilter();
        $filter->add($statusInput);

        $this->setInputFilter($filter);
    }

    public function getStatusElement()
    {
        return $this->statusElement;
    }
}
