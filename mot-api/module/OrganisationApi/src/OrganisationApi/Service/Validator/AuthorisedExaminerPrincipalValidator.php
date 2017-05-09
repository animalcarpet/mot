<?php

namespace OrganisationApi\Service\Validator;

use DvsaCommon\Input\AuthorisedExaminerPrincipal\AddressLine1Input;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\DateOfBirthInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FamilyNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\FirstNameInput;
use DvsaCommon\Input\AuthorisedExaminerPrincipal\TownInput;
use DvsaCommon\InputFilter\AuthorisedExaminerPrincipal\CreateAepInputFilter;
use DvsaCommonApi\Service\Validator\AbstractValidator;

class AuthorisedExaminerPrincipalValidator extends AbstractValidator
{
    private $requiredFields
        = [
            FirstNameInput::FIELD,
            FamilyNameInput::FIELD,
            DateOfBirthInput::FIELD,
            AddressLine1Input::FIELD,
            TownInput::FIELD,
        ];

    public function validate(array $data)
    {
        $this->checkRequiredFields($this->requiredFields, $data);

        $inputFilter = new CreateAepInputFilter();
        $inputFilter->init();
        $inputFilter->setData($data);

        if (!$inputFilter->isValid()) {
            $messages = $inputFilter->getMessages();
            foreach ($messages as $fieldName => $errors) {
                $this->errors->add($errors, $fieldName);
            }
        }

        $this->errors->throwIfAnyField();
    }
}
