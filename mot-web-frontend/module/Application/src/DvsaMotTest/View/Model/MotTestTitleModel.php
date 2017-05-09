<?php

namespace DvsaMotTest\View\Model;

use Dvsa\Mot\ApiClient\Resource\Item\MotTest;
use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Enum\MotTestTypeCode;

class MotTestTitleModel
{
    const MOT_TESTING_TITLE = 'MOT testing';
    const MOT_TRAINING_TEST_TITLE = 'Training test';
    const DUPLICATE_CERTIFICATE_TITLE = 'Duplicate or replacement certificate';

    /**
     * @param null|MotTest $motTest
     * @param bool         $isDuplicateCertificate
     *
     * @return string
     */
    public function getTitle($motTest,  $isDuplicateCertificate = false)
    {
        if ($isDuplicateCertificate) {
            return self::DUPLICATE_CERTIFICATE_TITLE;
        }

        if (!is_null($motTest) && $motTest instanceof MotTest) {
            /** @var $testType MotTestTypeDto */
            $testType = $motTest->getTestTypeCode();

            if ($testType == MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
                return self::MOT_TRAINING_TEST_TITLE;
            }
        }

        return self::MOT_TESTING_TITLE;
    }
}
