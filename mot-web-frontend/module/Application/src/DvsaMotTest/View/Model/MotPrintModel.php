<?php

namespace DvsaMotTest\View\Model;

use Dvsa\Mot\ApiClient\Resource\Item\DvsaVehicle;
use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Domain\MotTestType;
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

        /** @var MotTest $motDetails */
        $motDetails  = $variables['motDetails'];
        /** @var DvsaVehicle $vehicle */
        $vehicle     = $variables['vehicle'];
        $motTestNumber = $motDetails->getMotTestNumber();

        if ($motDetails !== null) {
            /** @var MotTest $motTestType */
            $motTestType        = $motDetails->getTestTypeCode();
            $prsMotTestNumber   = $motDetails->getPrsMotTestNumber();
            $code               = $motTestType;
        }

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

        if ($motDetails->getStatus() === MotTestStatusName::PASSED) {
            $passedMotTestNumber = $motDetails->getMotTestNumber();
            if ($prsMotTestNumber) {
                $failedMotTestNumber = $prsMotTestNumber;
            }
        } elseif ($motDetails->getStatus() === MotTestStatusName::FAILED) {
            $failedMotTestNumber = $motDetails->getMotTestNumber();
            if ($prsMotTestNumber) {
                $passedMotTestNumber = $prsMotTestNumber;
            }
        } elseif ($motDetails->getStatus() === MotTestStatusName::ABANDONED) {
            $abandonedMotTestNumber = $motDetails->getMotTestNumber();
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

        $vtsId =  $motDetails->getSiteId();

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
