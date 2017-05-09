<?php

namespace DvsaMotApi\Service\Model;

use DvsaCommon\Enum\ReasonForRejectionTypeName;
use DvsaMotApi\Service\MotTestReasonForRejectionService;

/**
 * Class BrakeTestResultSubmissionSummary.
 */
class BrakeTestResultSubmissionSummary
{
    public $brakeTestResultClass3AndAbove = null;
    public $brakeTestResultClass1And2 = null;
    public $reasonsForRejectionList = [];

    public function addReasonForRejection(
        $rfrId,
        $type = ReasonForRejectionTypeName::FAIL,
        $longitudinalLocation = null,
        $comment = null
    ) {
        $this->reasonsForRejectionList[] = [
            MotTestReasonForRejectionService::RFR_ID_FIELD => $rfrId,
            MotTestReasonForRejectionService::TYPE_FIELD => $type,
            MotTestReasonForRejectionService::LONGITUDINAL_LOCATION_FIELD => $longitudinalLocation,
            MotTestReasonForRejectionService::COMMENT_FIELD => $comment,
        ];

        return $this;
    }
}
