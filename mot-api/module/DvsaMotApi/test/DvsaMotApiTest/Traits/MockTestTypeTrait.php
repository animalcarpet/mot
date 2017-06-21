<?php

namespace DvsaMotApiTest\Traits;

use DvsaCommon\Enum\MotTestTypeCode;
use DvsaEntities\Entity\MotTestType;

trait MockTestTypeTrait
{
    public function getMotTestTypeMock($testTypeCode = MotTestTypeCode::NORMAL_TEST)
    {
        $motTestTypeMock = $this->getMockBuilder(MotTestType::class)->disableOriginalConstructor()->getMock();
        $isDemo = false;
        $isSlotConsuming = false;
        $isReinspection = false;
        switch ($testTypeCode) {
            case MotTestTypeCode::TARGETED_REINSPECTION:
            case MotTestTypeCode::MOT_COMPLIANCE_SURVEY:
            case MotTestTypeCode::INVERTED_APPEAL:
            case MotTestTypeCode::STATUTORY_APPEAL:
            case MotTestTypeCode::OTHER:
                $isReinspection = true;
                break;
            case MotTestTypeCode::ROUTINE_DEMONSTRATION_TEST:
            case MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING:
                $isDemo = true;
                break;
            default:
                $isSlotConsuming = true;
                break;
        }
        $motTestTypeMock->expects($this->any())
            ->method('getIsDemo')
            ->will($this->returnValue($isDemo));
        $motTestTypeMock->expects($this->any())
            ->method('getIsReinspection')
            ->will($this->returnValue($isReinspection));
        $motTestTypeMock->expects($this->any())
            ->method('getIsSlotConsuming')
            ->will($this->returnValue($isSlotConsuming));

        return $motTestTypeMock;
    }
}
