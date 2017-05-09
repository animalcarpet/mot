<?php

namespace PersonApi\Service\Validator;

use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Date\DateTimeHolder;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Repository\SiteRepository;
use PersonApi\Input\MotTestingCertificate\CertificateNumberInput;
use PersonApi\Input\MotTestingCertificate\DateOfQualificationInput;
use PersonApi\Input\MotTestingCertificate\SiteNumberInput;
use PersonApi\Input\MotTestingCertificate\VehicleClassGroupCodeInput;
use Zend\InputFilter\InputFilter;

class MotTestingCertificateValidator extends AbstractValidator implements AutoWireableInterface
{
    private $inputFilter;

    public function __construct(SiteRepository $siteRepository)
    {
        parent::__construct();

        $this->inputFilter = new InputFilter();
        $this->inputFilter
            ->add(new CertificateNumberInput())
            ->add(new DateOfQualificationInput(new DateTimeHolder()))
            ->add(new SiteNumberInput($siteRepository))
            ->add(new VehicleClassGroupCodeInput())
        ;
    }

    public function validate(MotTestingCertificateDto $dto)
    {
        $data = [
            CertificateNumberInput::FIELD => $dto->getCertificateNumber(),
            DateOfQualificationInput::FIELD => $dto->getDateOfQualification(),
            SiteNumberInput::FIELD => $dto->getSiteNumber(),
            VehicleClassGroupCodeInput::FIELD => $dto->getVehicleClassGroupCode(),
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
