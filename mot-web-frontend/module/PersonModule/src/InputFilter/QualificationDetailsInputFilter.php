<?php

namespace Dvsa\Mot\Frontend\PersonModule\InputFilter;

use DvsaClient\Mapper\QualificationDetailsMapper;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use Zend\InputFilter\InputFilter;

class QualificationDetailsInputFilter extends InputFilter implements AutoWireableInterface
{
    private $qualificationDetailsMapper;
    private $personId;
    private $group;

    private $validationMessages = [];

    public function __construct(QualificationDetailsMapper $qualificationDetailsMapper, $personId, $group)
    {
        $this->qualificationDetailsMapper = $qualificationDetailsMapper;
        $this->personId = $personId;
        $this->group = $group;
    }

    public function isValid()
    {
        try {
            $data = QualificationDetailsMapper::mapFormDataToDto($this->data, $this->group);
            $this->qualificationDetailsMapper->validateQualificationDetails($this->personId, $data);
        } catch (ValidationException $e) {
            $this->validationMessages = $e->getErrors();

            return false;
        }

        return true;
    }

    public function getMessages()
    {
        return $this->validationMessages;
    }
}
