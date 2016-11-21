<?php

namespace DvsaMotTest\Form\VehicleSearch;

use Zend\Form\Element\Text;

class DuplicateCertificateRegistrationSearchForm extends AbstractDuplicateCertificateSearchForm
{
    protected $messageTooLong = 'must be %s characters or less';
    protected $messageEmpty = 'you must enter registration mark';
    protected $searchFieldName = 'vrm';
    protected $searchFieldMaxLength = 13;

    public function __construct()
    {
        parent::__construct('search-for-duplicate-by-registration');
    }

    protected function createSearchElement()
    {
        $registration = new Text();
        $registration
            ->setName($this->searchFieldName)
            ->setLabel('Registration mark')
            ->setAttribute('id', $this->searchFieldName)
            ->setAttribute('required', true)
            ->setAttribute('maxLength', $this->searchFieldMaxLength)
            ->setAttribute('group', true);

        return $registration;
    }
}