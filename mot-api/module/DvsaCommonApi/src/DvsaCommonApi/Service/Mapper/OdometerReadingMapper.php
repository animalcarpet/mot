<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaCommonApi\Service\Mapper;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaEntities\Entity\MotTest;

class OdometerReadingMapper extends AbstractApiMapper
{
    /**
     * @param MotTest $motTestOrDraft
     * @return OdometerReadingDto
     * @throws \Exception After deprecation of OdometerReading
     *                      we have MotTest or CertificateReplacementDraft as the container
     */
    public function toDto($motTestOrDraft)
    {
        if (!$motTestOrDraft instanceof MotTest && !$motTestOrDraft instanceof CertificateReplacementDraft) {
            throw new \InvalidArgumentException(sprintf(
                'Expected object of type "%s" or "%s", received "%s"',
                'DvsaEntities\Entity\MotTest',
                'DvsaEntities\Entity\CertificateReplacementDraft',
                get_class($motTestOrDraft)
            ));
        }

        $dto = new OdometerReadingDto();

        $dto
            ->setResultType($motTestOrDraft->getOdometerResultType())
            ->setUnit($motTestOrDraft->getOdometerUnit())
            ->setValue($motTestOrDraft->getOdometerValue());

        return $dto;
    }

    /**
     * @param array $readings
     * @return array
     */
    public function manyToDtoFromArray($readings)
    {
        TypeCheck::assertArray($readings);

        $result = [];

        foreach ($readings as $data) {
            $result[] = $this->toDtoFromArray($data);
        }

        return $result;
    }

    /**
     * @param $reading
     * @return OdometerReadingDto
     */
    public function toDtoFromArray($reading)
    {
        $dto = new OdometerReadingDto();

        $dto->setResultType(ArrayUtils::tryGet($reading, 'resultType', null))
            ->setUnit(ArrayUtils::tryGet($reading, 'unit', null))
            ->setValue(ArrayUtils::tryGet($reading, 'value', null))
            ->setIssuedDate(ArrayUtils::tryGet($reading, 'issuedDate', null));

        return $dto;
    }
}
