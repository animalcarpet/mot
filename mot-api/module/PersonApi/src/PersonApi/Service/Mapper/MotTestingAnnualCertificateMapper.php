<?php
namespace PersonApi\Service\Mapper;

use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use DvsaEntities\Entity\QualificationAnnualCertificate;
use DvsaCommon\ApiClient\Person\MotTestingAnnualCertificate\Dto\MotTestingAnnualCertificateDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;

class MotTestingAnnualCertificateMapper extends AbstractApiMapper implements AutoWireableInterface
{
    /**
     * @param QualificationAnnualCertificate $entity
     * @return MotTestingAnnualCertificateDto
     */
    public function toDto($entity)
    {
        $dto = new MotTestingAnnualCertificateDto();
        $dto
            ->setId($entity->getId())
            ->setExamDate($entity->getDateAwarded())
            ->setCertificateNumber($entity->getCertificateNumber())
            ->setScore($entity->getScore());

        return $dto;
    }
}