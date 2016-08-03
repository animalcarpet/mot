<?php

namespace DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate;

use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class MotTestingAnnualCertificateApiResource extends AbstractApiResource implements AutoWireableInterface
{
    /**
     * @param $personId
     * @param $group
     * @param $certificateId
     * @return MotTestingAnnualCertificateDto
     */
    public function get($personId, $group, $certificateId)
    {
        $url = sprintf('person/%s/mot-testing-annual-certificate/%s/%s', $personId, $group, $certificateId);

        return $this->getSingle(MotTestingAnnualCertificateDto::class, $url);
    }

    /**
     * @param $personId
     * @param $group
     * @return MotTestingAnnualCertificateDto []
     */
    public function getList($personId, $group)
    {
        $resourcePath = sprintf('person/%s/mot-testing-annual-certificate/%s', $personId, $group);

        return $this->getSingle(MotTestingAnnualCertificateDto::class, $resourcePath);
    }

    public function update($personId, $group, $certificateId, MotTestingAnnualCertificateDto $dto)
    {
        $resourcePath = sprintf('person/%s/mot-testing-annual-certificate/%s/%s', $personId, $group, $certificateId);
        $responseBody = $this->httpClient->put($resourcePath, $this->serializer->serialize($dto));
        $data = $responseBody["data"];

        return $this->deserializer->deserialize($data, MotTestingAnnualCertificateDto::class);
    }

    public function remove($personId, $group, $certificateId)
    {
        $resourcePath = sprintf('person/%s/mot-testing-annual-certificate/%s/%s', $personId, $group, $certificateId);
        $this->httpClient->delete($resourcePath);
    }


}
