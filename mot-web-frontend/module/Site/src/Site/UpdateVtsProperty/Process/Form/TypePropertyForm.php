<?php

namespace Site\UpdateVtsProperty\Process\Form;

use Core\Catalog\Vts\VtsTypeCatalog;
use DvsaCommon\Model\TypeOfVts;
use Site\UpdateVtsProperty\UpdateVtsPropertyAction;
use Zend\Form\Element\Radio;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;

class TypePropertyForm extends Form
{
    const FIELD_TYPE = UpdateVtsPropertyAction::VTS_TYPE_PROPERTY;

    const TYPE_EMPTY_MSG = 'you must choose a site type';

    private $typeElement;

    /**
     * @var VtsTypeCatalog
     */
    private $vtsTypeCatalog;

    public function __construct($vtsTypeCatalog)
    {
        parent::__construct(self::FIELD_TYPE);
        $this->vtsTypeCatalog = $vtsTypeCatalog;

        $options = [];
        foreach (TypeOfVts::getPossibleVtsTypes() as $typeCode) {
            $siteType = $vtsTypeCatalog->getByCode($typeCode);
            $options[] = [
                'label' => $siteType->getName(),
                'value' => $siteType->getCode(),
                'key' => $siteType->getName(),
                'inputName' => self::FIELD_TYPE,
            ];
        }

        $this->typeElement = new Radio();
        $this
            ->typeElement
            ->setDisableInArrayValidator(true)
            ->setValueOptions($options)
            ->setName(self::FIELD_TYPE)
            ->setLabel('Site type')
            ->setAttribute('id', 'vtsTypeSelectSet')
            ->setAttribute('required', true)
            ->setAttribute('group', true);

        $this->add($this->typeElement);

        $typeEmptyValidator = (new NotEmpty())->setMessage(self::TYPE_EMPTY_MSG, NotEmpty::IS_EMPTY);
        $typeInput = new Input(self::FIELD_TYPE);
        $typeInput
            ->setRequired(true)
            ->setErrorMessage(self::TYPE_EMPTY_MSG)
            ->getValidatorChain()
            ->attach($typeEmptyValidator);

        $filter = new InputFilter();
        $filter->add($typeInput);

        $this->setInputFilter($filter);
    }

    public function getTypeElement()
    {
        return $this->typeElement;
    }
}
