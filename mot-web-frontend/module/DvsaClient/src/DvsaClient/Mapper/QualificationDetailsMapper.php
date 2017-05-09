<?php

namespace DvsaClient\Mapper;

use DvsaCommon\Dto\MotTesting\DemoTestRequestsListDto;
use Dvsa\Mot\Frontend\PersonModule\Form\QualificationDetailsForm;
use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;
use DvsaCommon\UrlBuilder\QualificationDetailsUrlBuilder;
use DvsaCommon\Utility\DtoHydrator;

class QualificationDetailsMapper extends DtoMapper implements AutoWireableInterface
{
    private $deserializer;
    private $serializer;

    public function __construct(
        Client $client,
        DtoReflectiveDeserializer $deserializer,
        DtoReflectiveSerializer $serializer
    ) {
        parent::__construct($client);
        $this->deserializer = $deserializer;
        $this->serializer = $serializer;
    }

    public function getQualificationDetails($personId, $group)
    {
        $result = $this->get(PersonUrlBuilder::qualificationDetails($personId, strtolower($group)));

        return $this->deserializer->deserialize($result, MotTestingCertificateDto::class);
    }

    public function validateQualificationDetails($personId, MotTestingCertificateDto $dto)
    {
        $data = $this->serializer->serialize($dto);

        return $this->post(PersonUrlBuilder::validateQualificationDetails($personId), $data);
    }

    public function createQualificationDetails($personId, $group, MotTestingCertificateDto $dto)
    {
        $data = $this->serializer->serialize($dto);

        return $this->post(PersonUrlBuilder::qualificationDetails($personId, $group), $data);
    }

    public function updateQualificationDetails($personId, $group, MotTestingCertificateDto $dto)
    {
        $data = $this->serializer->serialize($dto);

        return $this->put(PersonUrlBuilder::qualificationDetails($personId, $group), $data);
    }

    /**
     * @param $sortParams
     *
     * @return DemoTestRequestsListDto
     */
    public function getDemoTestRequests($sortParams)
    {
        return $this->post(QualificationDetailsUrlBuilder::demoTestRequests(), DtoHydrator::dtoToJson($sortParams));
    }

    public function removeQualificationDetails($personId, $group)
    {
        return $this->delete(PersonUrlBuilder::qualificationDetails($personId, $group));
    }

    public static function mapFormDataToDto(array $formData, $group)
    {
        $motTestingCertificateDto = (new MotTestingCertificateDto())
            ->setId(1)
            ->setVehicleClassGroupCode(strtoupper($group))
            ->setSiteNumber($formData[QualificationDetailsForm::FIELD_VTS_ID])
            ->setCertificateNumber($formData[QualificationDetailsForm::FIELD_CERT_NUMBER])
            ->setDateOfQualification(sprintf('%d-%d-%d',
                $formData[QualificationDetailsForm::FIELD_DATE_YEAR],
                $formData[QualificationDetailsForm::FIELD_DATE_MONTH],
                $formData[QualificationDetailsForm::FIELD_DATE_DAY]
            ));

        return $motTestingCertificateDto;
    }

    public static function mapDtoToFormData(MotTestingCertificateDto $motTestingCertificateDto)
    {
        $date = new \DateTime($motTestingCertificateDto->getDateOfQualification());

        return [
            QualificationDetailsForm::FIELD_VTS_ID => $motTestingCertificateDto->getSiteNumber(),
            QualificationDetailsForm::FIELD_CERT_NUMBER => $motTestingCertificateDto->getCertificateNumber(),
            QualificationDetailsForm::FIELD_DATE_DAY => $date->format('d'),
            QualificationDetailsForm::FIELD_DATE_MONTH => $date->format('m'),
            QualificationDetailsForm::FIELD_DATE_YEAR => $date->format('Y'),
        ];
    }
}
