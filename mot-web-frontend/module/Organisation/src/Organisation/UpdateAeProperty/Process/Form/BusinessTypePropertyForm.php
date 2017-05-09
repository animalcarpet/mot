<?php

namespace Organisation\UpdateAeProperty\Process\Form;

use Core\Catalog\Organisation\OrganisationCompanyTypeCatalog;
use Core\Catalog\Vts\VtsTypeCatalog;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Model\CompanyType;
use Organisation\UpdateAeProperty\UpdateAePropertyAction;
use Zend\Form\Element\Radio;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;
use Zend\Validator\InArray;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

class BusinessTypePropertyForm extends Form
{
    const FIELD_TYPE = UpdateAePropertyAction::AE_BUSINESS_TYPE_PROPERTY;
    const FIELD_COMPANY_NUMBER = UpdateAePropertyAction::AE_COMPANY_NUMBER_PROPERTY;

    const COMPANY_TYPE_TOO_LONG_MSG = 'must be %max% characters or less';
    const FIELD_NAME_MAX_LENGTH = 20;

    const TYPE_EMPTY_MSG = 'you must choose a business type';
    const COMPANY_NUMBER_EMPTY_MSG = 'you must enter a company number';
    protected $companyNumberElement;

    private $typeElement;

    /**
     * @var VtsTypeCatalog
     */
    private $organisationCompanyTypeCatalog;

    public function __construct(OrganisationCompanyTypeCatalog $organisationCompanyTypeCatalog)
    {
        parent::__construct(self::FIELD_TYPE);
        $this->organisationCompanyTypeCatalog = $organisationCompanyTypeCatalog;

        $options = [];
        foreach (CompanyType::getPossibleCompanyTypes() as $typeCode) {
            $companyType = $organisationCompanyTypeCatalog->getByCode($typeCode);
            if ($companyType) {
                $option = [
                    'label' => $companyType->getName(),
                    'value' => $companyType->getCode(),
                    'key' => $companyType->getName(),
                    'inputName' => self::FIELD_TYPE,
                ];
                if ($typeCode == CompanyTypeCode::COMPANY) {
                    $option['dataTarget'] = static::FIELD_COMPANY_NUMBER;
                }
                $options[] = $option;
            }
        }

        $this->typeElement = new Radio();
        $this
            ->typeElement
            ->setValueOptions($options)
            ->setDisableInArrayValidator(true)
            ->setName(self::FIELD_TYPE)
            ->setLabel('Business type')
            ->setAttribute('id', 'aeBusinessTypeSelect')
            ->setAttribute('required', true)
            ->setAttribute('group', true);

        $this->companyNumberElement = new Text();
        $this
            ->companyNumberElement
            ->setName(self::FIELD_COMPANY_NUMBER)
            ->setLabel('Company number')
            ->setAttribute('id', 'aeBusinessTypeCompanyNumber')
            ->setAttribute('group', true);

        $this->add($this->typeElement);
        $this->add($this->companyNumberElement);

        $typeInArrayValidator = (new InArray())
            ->setHaystack(CompanyType::getPossibleCompanyTypes())
            ->setMessage(self::TYPE_EMPTY_MSG, InArray::NOT_IN_ARRAY);

        $typeEmptyValidator = (new NotEmpty())
            ->setMessage(self::TYPE_EMPTY_MSG, NotEmpty::IS_EMPTY);

        $typeInput = new Input(self::FIELD_TYPE);
        $typeInput
            ->setRequired(true)
            ->getValidatorChain()
            ->attach($typeEmptyValidator)
            ->attach($typeInArrayValidator);

        $companyNumberInput = new Input(self::FIELD_COMPANY_NUMBER);
        $companyNumberInput
            ->setRequired(false)
            ->setAllowEmpty(true)
            ->setErrorMessage(static::COMPANY_NUMBER_EMPTY_MSG);

        $filter = new InputFilter();
        $filter->add($companyNumberInput);
        $filter->add($typeInput);

        $this->setInputFilter($filter);
    }

    public function getTypeElement()
    {
        return $this->typeElement;
    }

    public function getCompanyNumberElement()
    {
        return $this->companyNumberElement;
    }

    public function isValid()
    {
        $valid = parent::isValid();
        if (!$valid) {
            return false;
        }

        $companyNumber = $this->getTypeElement()->getValue();
        if ($companyNumber == CompanyTypeCode::COMPANY) {
            $companyNumberInput = new Input(self::FIELD_COMPANY_NUMBER);
            $companyNumberEmptyValidator = (new NotEmpty())->setMessage(self::COMPANY_NUMBER_EMPTY_MSG, NotEmpty::IS_EMPTY);
            $stringValidator = (new StringLength())
                ->setMax(static::FIELD_NAME_MAX_LENGTH)
                ->setMessage(static::COMPANY_TYPE_TOO_LONG_MSG, StringLength::TOO_LONG);

            $companyNumberInput
                ->getValidatorChain()
                ->attach($stringValidator)
                ->attach($companyNumberEmptyValidator);

            $filter = new InputFilter();
            $filter->add($companyNumberInput);
            $filter->setData($this->getData());
            $valid = $filter->isValid();
            $this->setMessages($filter->getMessages());
        }

        return $valid;
    }
}
