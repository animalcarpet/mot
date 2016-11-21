<?php

namespace DvsaCommon\ApiClient\MotTest\DuplicateCertificate;

use DvsaCommon\ApiClient\MotTest\DuplicateCertificate\Dto\MotTestDuplicateCertificateEditAllowedDto;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaCommon\HttpRestJson\AbstractApiResource;

class MotTestDuplicateCertificateApiResource extends AbstractApiResource implements AutoWireableInterface
{
    const MOT_TEST_EDIT_ALLOWED = 'mot-test/%s/edit-allowed-check/%s';

    /**
     * @param $motTestNumber
     * @param $vehicleId
     * @return MotTestDuplicateCertificateEditAllowedDto
     */
    public function getEditAllowed($motTestNumber, $vehicleId)
    {
        $url = sprintf(self::MOT_TEST_EDIT_ALLOWED, $motTestNumber, $vehicleId);

        return $this->getSingle(MotTestDuplicateCertificateEditAllowedDto::class, $url);
    }
}