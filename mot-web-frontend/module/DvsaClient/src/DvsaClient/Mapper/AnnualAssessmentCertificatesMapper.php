<?php
namespace DvsaClient\Mapper;

use Dvsa\Mot\Frontend\PersonModule\Form\AnnualAssessmentCertificatesForm;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\DtoSerialization\DtoReflectiveDeserializer;
use DvsaCommon\DtoSerialization\DtoReflectiveSerializer;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\Client;
use DvsaCommon\UrlBuilder\PersonUrlBuilder;

class AnnualAssessmentCertificatesMapper extends DtoMapper implements AutoWireableInterface
{
    private $deserializer;
    private $serializer;

    public function __construct(
        Client $client,
        DtoReflectiveDeserializer $deserializer,
        DtoReflectiveSerializer $serializer
    )
    {
        parent::__construct($client);
        $this->deserializer = $deserializer;
        $this->serializer = $serializer;
    }

    public function getAnnualAssessmentCertificates($personId, $group)
    {
        $result = $this->get(PersonUrlBuilder::annualAssessmentCertificates($personId, $group));

        return $this->deserializer->deserializeArray($result, MotTestingAnnualCertificateDto::class);
    }

    public function createAnnualAssessmentCertificate($personId, $group, MotTestingAnnualCertificateDto $dto)
    {
        $data = $this->serializer->serialize($dto);

        return $this->post(PersonUrlBuilder::annualAssessmentCertificates($personId, $group), $data);
    }

    public function mapFormDataToDto(array $formData)
    {
        $motTestingCertificateDto = (new MotTestingAnnualCertificateDto())
            ->setScore($formData[AnnualAssessmentCertificatesForm::FIELD_SCORE])
            ->setCertificateNumber($formData[AnnualAssessmentCertificatesForm::FIELD_CERT_NUMBER])
            ->setExamDate(new \DateTime(
                sprintf("%d-%d-%d",
                    $formData[AnnualAssessmentCertificatesForm::FIELD_DATE_YEAR],
                    $formData[AnnualAssessmentCertificatesForm::FIELD_DATE_MONTH],
                    $formData[AnnualAssessmentCertificatesForm::FIELD_DATE_DAY]
                )));

        return $motTestingCertificateDto;
    }
}