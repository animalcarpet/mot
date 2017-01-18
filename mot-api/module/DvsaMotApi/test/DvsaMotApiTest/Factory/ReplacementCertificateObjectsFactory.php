<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApiTest\Factory;

use DvsaCommon\Constants\OdometerReadingResultType;
use DvsaCommon\Constants\OdometerUnit;
use DvsaCommon\Date\DateUtils;
use DvsaEntities\Entity\CertificateChangeDifferentTesterReason;
use DvsaEntities\Entity\CertificateReplacementDraft;
use DvsaMotApi\Dto\ReplacementCertificateDraftChangeDTO;

/**
 * Class ReplacementCertificateObjectsFactory
 */
class ReplacementCertificateObjectsFactory
{
    /**
     * @return CertificateReplacementDraft
     */
    public static function replacementCertificateDraft()
    {
        $motTest = MotTestObjectsFactory::motTest();

        return (new CertificateReplacementDraft())
            ->setCountryOfRegistration(VehicleObjectsFactory::countryOfRegistration(1, "cor"))
            ->setPrimaryColour(VehicleObjectsFactory::colour(1, "R", "Red"))
            ->setSecondaryColour(VehicleObjectsFactory::colour(2, "G", "Green"))
            ->setExpiryDate(DateUtils::toDate("2014-05-01"))
            ->setMake(VehicleObjectsFactory::make(2, 'BMW', "BMW"))
            ->setModel(VehicleObjectsFactory::model(3, "M3", "M3"))
            ->setOdometerValue(666)
            ->setOdometerUnit(OdometerUnit::MILES)
            ->setOdometerResultType(OdometerReadingResultType::OK)
            ->setVin("vin")
            ->setMotTest($motTest)
            ->setVrm("vrm")
            ->setVehicleTestingStation(MotTestObjectsFactory::vts(1))
            ->setId(123)
            ->setMotTestVersion($motTest->getVersion());
    }

    /**
     * @param $idSeed
     *
     * @return ReplacementCertificateDraftChangeDTO
     */
    public static function fullReplacementCertificateDraftChange($idSeed)
    {
        return self::partialReplacementCertificateDraftChange($idSeed)
            ->setMake(999 + $idSeed)
            ->setModel(999 + $idSeed)
            ->setCountryOfRegistration(999 + $idSeed)
            ->setVin("NEW_VIN")
            ->setVrm("NEW_VRM")
            ->setVtsSiteNumber("NEW_SITE_NUMBER")
            ->setExpiryDate("2014-05-01");
    }

    /**
     * @param $idSeed
     *
     * @return ReplacementCertificateDraftChangeDTO
     */
    public static function partialReplacementCertificateDraftChange($idSeed)
    {
        return ReplacementCertificateDraftChangeDTO::create()

            ->setPrimaryColour("C" + $idSeed)
            ->setSecondaryColour("D" + $idSeed)
            ->setOdometerReading(
                    999 + $idSeed,
                    OdometerUnit::KILOMETERS,
                    OdometerReadingResultType::OK
            );
    }

    /**
     * @param $code
     *
     * @return CertificateChangeDifferentTesterReason
     */
    public static function reasonForDifferentTester($code)
    {
        return (new CertificateChangeDifferentTesterReason())->setCode($code);
    }
}
