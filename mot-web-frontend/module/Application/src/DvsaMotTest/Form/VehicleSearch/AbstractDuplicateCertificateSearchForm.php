<?php

namespace DvsaMotTest\Form\VehicleSearch;

use Zend\Filter\StringTrim;
use Zend\Form\Element\Text;
use Zend\Form\Form;
use Zend\InputFilter\InputFilter;
use Zend\Validator\NotEmpty;
use Zend\Validator\StringLength;

abstract class AbstractDuplicateCertificateSearchForm extends Form
{
    const FIELD_SUBMIT = 'submit';

    protected $messageTooLong = '';
    protected $messageEmpty = '';
    protected $searchFieldName = '';
    protected $searchFieldMaxLength = 0;

    /**
     * @var Text
     */
    protected $searchElement;

    public function __construct($formName)
    {
        parent::__construct($formName);
        $this->setAttribute('method', 'get');
        $this->setAttribute('action', '');

        $this->searchElement = $this->createSearchElement();
        $this->add($this->searchElement);

        $this->setInputFilter($this->createInputFilter());
    }

    /**
     * @return Text
     */
    public function getSearchInputElement()
    {
        return $this->searchElement;
    }

    /**
     * @return Text
     */
    abstract protected function createSearchElement();

    /**
     * @return InputFilter
     */
    protected function createInputFilter()
    {
        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => $this->searchFieldName,
            'required' => true,
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'messages' => [
                            NotEmpty::IS_EMPTY => $this->messageEmpty,
                        ],
                    ],
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => $this->searchFieldMaxLength,
                        'messages' => [
                            StringLength::TOO_LONG => sprintf($this->messageTooLong, $this->searchFieldMaxLength),
                        ],
                    ],
                ],
            ],
            'filters' => [
                [
                    'name' => StringTrim::class,
                ],
            ],
        ]);

        return $inputFilter;
    }
}
