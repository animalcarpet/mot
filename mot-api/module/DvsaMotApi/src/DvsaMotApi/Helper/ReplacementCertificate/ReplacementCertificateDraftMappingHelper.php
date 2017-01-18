<?php

namespace DvsaMotApi\Helper\ReplacementCertificate;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Utility\Hydrator;
use DvsaEntities\Entity\CertificateReplacementDraft;

/**
 * Class ReplacementCertificateMappingHelper
 *
 * @package DvsaMotApi\Helper\ReplacementCertificate
 */
class ReplacementCertificateDraftMappingHelper
{

    const MODEL_UNKNOWN = 'Unknown';

    /**
     * @param CertificateReplacementDraft $draft
     * @param                             $isFullRights
     *
     * @return array
     */
    public static function toJsonArray(CertificateReplacementDraft $draft, $isFullRights, $isLatestPassedMotTest = false)
    {
        $vts = $draft->getVehicleTestingStation();

        $json = [
            'primaryColour'   => [
                "code"   => $draft->getPrimaryColour()->getCode(),
                "name" => $draft->getPrimaryColour()->getName()
            ],
            'motTestNumber'       => $draft->getMotTest()->getNumber(),
        ];

        $json['isLatestPassedMotTest'] = $isLatestPassedMotTest;

        $json['odometerReading'] = [
                'value'      => $draft->getOdometerValue(),
                'unit'       => $draft->getOdometerUnit(),
                'resultType' => $draft->getOdometerResultType(),
            ];


        $json['secondaryColour'] = $draft->getSecondaryColour()
            ? [
                "code" => $draft->getSecondaryColour()->getCode(),
                "name" => $draft->getSecondaryColour()->getName(),
            ]
            : NULL;

        $json['expiryDate'] = DateTimeApiFormat::date($draft->getExpiryDate());

        if ($isFullRights) {
            $hydrator = new Hydrator();
            $address = $hydrator->extract($vts->getAddress());

            $json = array_merge(
                $json, [
                    'make'                  => [
                    "id"   => $draft->getMake() ? $draft->getMake()->getId() : "",
                    "code" => $draft->getMake() ? $draft->getMake()->getCode() : "",
                    "name" => self::getMakeName($draft),
                    ],
                    'model'                 => [
                        "id"   => $draft->getModel() ? $draft->getModel()->getId() : "",
                        "code" => $draft->getModel() ? $draft->getModel()->getCode() : "",
                        "name" => self::getModelName($draft),
                    ],
                    'customMake' => $draft->getMakeName(),
                    'customModel' => $draft->getModelName(),
                    'countryOfRegistration' => [
                        "id"   => $draft->getCountryOfRegistration()->getId(),
                        "name" => $draft->getCountryOfRegistration()->getName()
                    ],
                    'reasonForReplacement'  => $draft->getReasonForReplacement(),
                    'vin'                   => $draft->getVin(),
                    'vrm'                   => $draft->getVrm(),
                    'vts'                   => [
                        "id"         => $vts->getId(),
                        "siteNumber" => $vts->getSiteNumber(),
                        "name"       => $vts->getName(),
                        "address"    => $address,
                    ],
                    'isVinVrmExpiryChanged' => $draft->isVinVrmExpiryChanged(),
                    'includeInMismatchFile' => $draft->isIncludeInMismatchFile(),
                    'includeInPassFile'     => $draft->isIncludeInPassFile()
                ]
            );
        }

        return $json;
    }

    /**
     * @param CertificateReplacementDraft $draft
     *
     * @return string
     */
    private static function getMakeName($draft)
    {
        if ($draft->getMake()) {
            return $draft->getMake()->getName();
        }

        if (!empty($draft->getMakeName())) {
            return $draft->getMakeName();
        }

        $motTest = $draft->getMotTest();

        if (!empty($motTest->getMakeName())) {
            return $motTest->getMakeName();
        }

        return $motTest->getVehicle()->getMakeName();
    }

    /**
     * @param CertificateReplacementDraft $draft
     *
     * @return string
     */
    private static function getModelName($draft)
    {
        if ($draft->getModel()) {
            return $draft->getModel()->getName();
        }

        if (!empty($draft->getModelName())) {
            return $draft->getModelName();
        }

        if ($draft->getMake() && !$draft->getModel()) {
            return self::MODEL_UNKNOWN;
        }

        $motTest = $draft->getMotTest();

        if (!empty($motTest->getModelName())) {
            return $motTest->getModelName();
        }

        return $draft->getMotTest()->getVehicle()->getModelName();
    }

}
