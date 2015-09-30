<?php

namespace DvsaMotTest\View\Model;

use DvsaCommon\Dto\Common\MotTestTypeDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\Dto\Common\MotTestDto;

class MotTestTitleModel
{

    const MOT_TESTING_TITLE = 'MOT testing';
    const MOT_TRAINING_TEST_TITLE = 'Training test';

    /**
     * @param null|MotTestDto $motTestDto
     * @return string
     */
    public function getTitle($motTestDto)
    {
        if (!is_null($motTestDto) && $motTestDto instanceof MotTestDto) {
            /** @var $motTestDto MotTestDto */
            /** @var $testType MotTestTypeDto */
            $testType = $motTestDto->getTestType();

            if ($testType->getCode() == MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
                return self::MOT_TRAINING_TEST_TITLE;
            }
        }

        return self::MOT_TESTING_TITLE;
    }

}
