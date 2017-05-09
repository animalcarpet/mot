<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service\Helper;

use DvsaEntities\Entity\MotTest;

/**
 * Class MotTestCloneHelper.
 */
class MotTestCloneHelper
{
    public static function motTestDeepCloneNoCollections(MotTest $motTest)
    {
        $clonedMotTest = clone $motTest;
        $clonedMotTest->setId(null);
        $clonedMotTest->getMotTestReasonForRejections()->clear();
        if ($clonedMotTest->getBrakeTestResultClass12()) {
            $brakeTestBikesClone = clone $clonedMotTest->getBrakeTestResultClass12();
            $brakeTestBikesClone
                ->setId(null)
                ->setMotTest($clonedMotTest);
            $clonedMotTest->setBrakeTestResultClass12($brakeTestBikesClone);
        }
        if ($clonedMotTest->getBrakeTestResultClass3AndAbove()) {
            $brakeTestCarsClone = clone $clonedMotTest->getBrakeTestResultClass3AndAbove();
            $brakeTestCarsClone
                ->setId(null)
                ->setMotTest($clonedMotTest);
            $clonedMotTest->setBrakeTestResultClass3AndAbove($brakeTestCarsClone);
            if ($brakeTestCarsClone->getServiceBrake1Data()) {
                $brakeTestServiceBrakeData1Clone = clone $brakeTestCarsClone->getServiceBrake1Data();
                $brakeTestServiceBrakeData1Clone->setId(null);
                $brakeTestCarsClone->setServiceBrake1Data($brakeTestServiceBrakeData1Clone);
            }
            if ($brakeTestCarsClone->getServiceBrake2Data()) {
                $brakeTestServiceBrakeData2Clone = clone $brakeTestCarsClone->getServiceBrake2Data();
                $brakeTestServiceBrakeData2Clone->setId(null);
                $brakeTestCarsClone->setServiceBrake2Data($brakeTestServiceBrakeData2Clone);
            }
        }

        $clonedMotTest->setNumber(null);

        return $clonedMotTest;
    }
}
