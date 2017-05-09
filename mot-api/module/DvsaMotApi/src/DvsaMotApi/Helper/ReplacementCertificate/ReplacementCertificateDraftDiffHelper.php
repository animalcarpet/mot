<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Helper\ReplacementCertificate;

use DvsaCommon\Date\DateTimeApiFormat;
use DvsaEntities\Entity\CertificateReplacementDraft;

/**
 * Class ReplacementCertificateDraftDiffHelper.
 */
class ReplacementCertificateDraftDiffHelper
{
    /**
     * Returns a list of properties on a draft that are different
     * from these on a MOT test associaciated with it.
     *
     * @param CertificateReplacementDraft $draft
     *
     * @return array
     */
    public static function getDiff(CertificateReplacementDraft $draft)
    {
        $data = [];
        $motTest = $draft->getMotTest();
        // colour
        if ($draft->getPrimaryColour()->getId() !== $motTest->getPrimaryColour()->getId()
        ) {
            $data [] = 'primaryColour';
        }
        $draftSecondary = $draft->getSecondaryColour() !== null ? $draft->getSecondaryColour()->getId() : null;
        $testSecondary = $motTest->getSecondaryColour() !== null ? $motTest->getSecondaryColour()->getId() : null;
        if ($draftSecondary !== $testSecondary) {
            $data [] = 'secondaryColour';
        }
        if (
            $draft->getOdometerUnit() !== $motTest->getOdometerUnit() ||
            $draft->getOdometerResultType() !== $motTest->getOdometerResultType() ||
            $draft->getOdometerValue() !== $motTest->getOdometerValue()
        ) {
            $data [] = 'odometerReading';
        }

        if ($draft->getVin() !== $motTest->getVin()) {
            $data [] = 'vin';
        }

        if ($draft->getVrm() !== $motTest->getRegistration()) {
            $data [] = 'registration';
        }

        if ($motTest->getMake() && $motTest->getModel()) {
            $testMakeId = $motTest->getMake() ? $motTest->getMake()->getCode() : null;
            $draftMakeId = $draft->getMake() ? $draft->getMake()->getCode() : null;
            if ($draftMakeId !== $testMakeId) {
                $data [] = 'make';
            }
            $testModelId = $motTest->getModel() ? $motTest->getModel()->getId() : null;
            $draftModelId = $draft->getModel() ? $draft->getModel()->getId() : null;
            if ($draftModelId !== $testModelId) {
                $data [] = 'model';
            }
        } else {
            $testMake = $motTest->getMakeName();
            $draftMake = $draft->getMakeName();
            $testModel = $motTest->getModelName();
            $draftModel = $draft->getModelName();

            if ($draftMake !== $testMake) {
                $data[] = 'make';
            }

            if ($draftModel !== $testModel) {
                $data[] = 'model';
            }
        }

        $testCorId = $motTest->getCountryOfRegistration() ? $motTest->getCountryOfRegistration()->getId() : null;
        $draftCorId = $draft->getCountryOfRegistration() ? $draft->getCountryOfRegistration()->getId() : null;

        if ($draftCorId !== $testCorId) {
            $data [] = 'countryOfRegistration';
        }

        if ($draft->getExpiryDate() && DateTimeApiFormat::date($draft->getExpiryDate())
            !== DateTimeApiFormat::date($motTest->getExpiryDate())
        ) {
            $data [] = 'expiryDate';
        }

        if ($draft->getVehicleTestingStation()
            && ($draft->getVehicleTestingStation()->getId()
                !== $motTest->getVehicleTestingStation()->getId()
            )
        ) {
            $data [] = 'vehicleTestingStation';
        }

        return $data;
    }
}
