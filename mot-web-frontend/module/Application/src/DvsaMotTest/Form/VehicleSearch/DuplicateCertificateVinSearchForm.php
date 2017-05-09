<?php

namespace DvsaMotTest\Form\VehicleSearch;

use Zend\Form\Element\Text;

class DuplicateCertificateVinSearchForm extends AbstractDuplicateCertificateSearchForm
{
    protected $messageTooLong = 'must be %s characters or less';
    protected $messageEmpty = 'you must enter VIN number';
    protected $searchFieldName = 'vin';
    protected $searchFieldMaxLength = 20;

    public function __construct()
    {
        parent::__construct('search-for-duplicate-by-vin');
    }

    protected function createSearchElement()
    {
        $vin = new Text();
        $vin
            ->setName($this->searchFieldName)
            ->setLabel('VIN')
            ->setAttribute('id', $this->searchFieldName)
            ->setAttribute('required', true)
            ->setAttribute('maxLength', $this->searchFieldMaxLength)
            ->setAttribute('help', 'Enter the full VIN')
            ->setAttribute('group', true);

        return $vin;
    }
}
