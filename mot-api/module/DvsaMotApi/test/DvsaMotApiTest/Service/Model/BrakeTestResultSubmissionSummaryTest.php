<?php

namespace DvsaMotApiTest\Service\Model;

use DvsaMotApi\Service\Model\BrakeTestResultSubmissionSummary;
use DvsaMotApi\Service\MotTestReasonForRejectionService;
use PHPUnit_Framework_TestCase;

/**
 * Class BrakeTestResultSubmissionSummaryTest.
 */
class BrakeTestResultSubmissionSummaryTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $brakeTestResultSubmissionSummary = new BrakeTestResultSubmissionSummary();
        $this->assertNull($brakeTestResultSubmissionSummary->brakeTestResultClass3AndAbove);
        $this->assertEmpty($brakeTestResultSubmissionSummary->reasonsForRejectionList);
    }

    public function testAddReasonForRejection()
    {
        $brakeTestResultSubmissionSummary = new BrakeTestResultSubmissionSummary();
        $brakeTestResultSubmissionSummary->addReasonForRejection(3, 'test_type', 'front', 'comment');

        $this->assertEquals($this->getTestRfrSummary(), $brakeTestResultSubmissionSummary->reasonsForRejectionList[0]);
        $brakeTestResultSubmissionSummary->addReasonForRejection(1, 'test_type2');
        $this->assertEquals(2, count($brakeTestResultSubmissionSummary->reasonsForRejectionList));
    }

    protected function getTestRfrSummary()
    {
        return [
            MotTestReasonForRejectionService::RFR_ID_FIELD => 3,
            MotTestReasonForRejectionService::TYPE_FIELD => 'test_type',
            MotTestReasonForRejectionService::LONGITUDINAL_LOCATION_FIELD => 'front',
            MotTestReasonForRejectionService::COMMENT_FIELD => 'comment',
        ];
    }
}
