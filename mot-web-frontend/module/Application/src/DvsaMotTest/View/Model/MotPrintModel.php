<?php

namespace DvsaMotTest\View\Model;

use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Dto\Vehicle\VehicleDto;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\UrlBuilder\MotTestUrlBuilderWeb;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Zend\View\Model\ViewModel;

/**
 * Class MotPrintModel
 *
 * @package DvsaMotTest\View\Model
 */
class MotPrintModel extends ViewModel
{
    const DUPLICATE_DOCUMENT_AVAILABLE = 'Duplicate document available';

    public function __construct($variables = null, $options = null)
    {
        $code = $prsMotTestNumber = null;

        /** @var MotTestDto $motDetails */
        $motDetails  = $variables['motDetails'];
        $motTestNumber = $motDetails->getMotTestNumber();

        if ($motDetails !== null) {
            /** @var MotTestTypeDto $motTestType */
            $motTestType        = $motDetails->getTestType();
            $status             = $motDetails->getStatus();
            $prsMotTestNumber   = $motDetails->getPrsMotTestNumber();
            $code               = $motTestType->getCode();
        }

        /** @var VehicleDto $vehicle */
        $vehicle = $motDetails === null ? new VehicleDto() : $motDetails->getVehicle();

        $isReinspection = MotTestType::isReinspection($code);
        $isNonMotTest = ($code === MotTestTypeCode::NON_MOT_TEST);
        $isAppeal = (
            $code === MotTestTypeCode::INVERTED_APPEAL
            || $code === MotTestTypeCode::STATUTORY_APPEAL
        );
        $isDuplicate = (isset($variables['isDuplicate']) && (bool) $variables['isDuplicate']);
        $isDemoMotTest = ($code === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING);

        $passedMotTestNumber = null;
        $failedMotTestNumber = null;
        $abandonedMotTestNumber = null;
        $abortedMotTestNumber = null;

        if (isset($status)) {
            if ($status === MotTestStatusName::PASSED) {
                $passedMotTestNumber = $motDetails->getMotTestNumber();
                if ($prsMotTestNumber) {
                    $failedMotTestNumber = $prsMotTestNumber;
                }
            } elseif ($status === MotTestStatusName::FAILED) {
                $failedMotTestNumber = $motDetails->getMotTestNumber();
                if ($prsMotTestNumber) {
                    $passedMotTestNumber = $prsMotTestNumber;
                }
            } elseif ($status === MotTestStatusName::ABANDONED) {
                $abandonedMotTestNumber = $motDetails->getMotTestNumber();
            }
        }

        // Title and header
        if ($isDuplicate) {
            $title = self::DUPLICATE_DOCUMENT_AVAILABLE;
        } else if ($code === MotTestTypeCode::NON_MOT_TEST) {
            $title = AbstractDvsaMotTestController::getTestName($code) . ' finished successfully';
        } else {
            $title = AbstractDvsaMotTestController::getTestName($code) . ' complete';
        }

        if ($isDuplicate) {
            $printUrl =  MotTestUrlBuilderWeb::printCertificateDuplicate($motTestNumber);
        } else {
            $printUrl =  MotTestUrlBuilderWeb::printCertificate($motTestNumber);
        }

        $vtsId =  $motDetails->getVehicleTestingStation() ? $motDetails->getVehicleTestingStation()['id'] : null;

        $extraVariables = [
            'passedMotTestId'     => $passedMotTestNumber,
            'failedMotTestId'     => $failedMotTestNumber,
            'abandonedMotTestNumber'  => $abandonedMotTestNumber,
            'isNonMotTest'        => $isNonMotTest,
            'isReinspection'      => $isReinspection,
            'isAppeal'            => $isAppeal,
            'isDuplicate'         => $isDuplicate,
            'isDemoMotTest'       => $isDemoMotTest,
            'title'               => $title,
            'vehicleRegistration' => $vehicle->getRegistration(),
            'printRoute'          => $printUrl->toString(),
            'vtsId'               => $vtsId
        ];

        $variables = array_merge($variables, $extraVariables);

        parent::__construct($variables, $options);
    }
}
