<?php
namespace PersonApi\Service\Validator;

use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use PersonApi\Input\MotTestingCertificate\CertificateNumberInput;
use PersonApi\Input\MotTestingCertificate\ScoreInput;
use PersonApi\Input\MotTestingCertificate\DateOfQualificationInput;
use Zend\InputFilter\Input;
use Zend\InputFilter\InputFilter;

class MotTestingAnnualCertificateValidator extends AbstractValidator implements AutoWireableInterface
{
    private $inputFilter;

    public function __construct()
    {
        parent::__construct();
        $this->inputFilter = new InputFilter();
        $this->inputFilter
            ->add(new CertificateNumberInput())
            ->add(new ScoreInput())
            ->add(new DateOfQualificationInput(new DateTimeHolder()));

    }

    public function validate(MotTestingAnnualCertificateDto $dto)
    {
        TypeCheck::assertInstance($dto->getExamDate(), \DateTime::class);

        $data = [
            CertificateNumberInput::FIELD => $dto->getCertificateNumber(),
            DateOfQualificationInput::FIELD => $dto->getExamDate()->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY),
            ScoreInput::FIELD => $dto->getScore(),
        ];

        $this->inputFilter->setData($data);

        if (!$this->inputFilter->isValid()) {
            $messages = $this->inputFilter->getMessages();
            foreach ($messages as $fieldName => $errors) {
                $this->errors->add($errors, $fieldName);
            }
        }

        $this->errors->throwIfAnyField();
    }
}