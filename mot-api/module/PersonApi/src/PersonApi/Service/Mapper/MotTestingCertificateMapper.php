<?php

namespace PersonApi\Service\Mapper;

use DvsaCommon\ApiClient\Person\MotTestingCertificate\Dto\MotTestingCertificateDto;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaEntities\Entity\QualificationAward;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class MotTestingCertificateMapper extends AbstractApiMapper implements AutoWireableInterface
{
    /**
     * @param $objects QualificationAward[]
     *
     * @return MotTestingCertificateDto[]
     */
    public function manyToDto($objects)
    {
        return parent::manyToDto($objects);
    }

    /**
     * @param QualificationAward $object
     *
     * @return MotTestingCertificateDto
     */
    public function toDto($object)
    {
        $dto = new MotTestingCertificateDto();

        $dto
            ->setId($object->getId())
            ->setVehicleClassGroupCode($object->getVehicleClassGroup()->getCode())
            ->setCertificateNumber($object->getCertificateNumber())
            ->setDateOfQualification($object->getDateOfQualification()->format(DateTimeApiFormat::FORMAT_ISO_8601_DATE_ONLY))
            ;

        if ($object->getSite() !== null) {
            $dto->setSiteNumber($object->getSite()->getSiteNumber());
        }

        return $dto;
    }
}
