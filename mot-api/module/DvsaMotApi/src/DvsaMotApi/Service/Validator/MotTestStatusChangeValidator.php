<?php

namespace DvsaMotApi\Service\Validator;

use DvsaCommon\Domain\MotTestType;
use DvsaCommon\Enum\MotTestStatusName;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaEntities\Entity\MotTest;
use DvsaMotApi\Service\MotTestStatusChangeService;
use DvsaMotApi\Service\MotTestStatusService;

/**
 * Validates the data when changing the status of an mot test.
 */
class MotTestStatusChangeValidator extends AbstractValidator
{
    private $motTestStatusService;

    const MESSAGE_REQUIRED_STATUS = 'No status was provided';
    const MESSAGE_REQUIRED_REASON_FOR_CANCEL = 'Reason for cancelling the test is required';
    const MESSAGE_REQUIRED_REASON_DESCRIPTION = 'Description is required';
    const MESSAGE_REQUIRED_PASS_CODE = 'Pass-code is required';

    public function __construct(
        MotTestStatusService $motTestStatusService
    ) {
        parent::__construct();
        $this->motTestStatusService = $motTestStatusService;
    }

    public function validateDataForNewStatus($data)
    {
        $this->validateRequiredField('status', $data, self::MESSAGE_REQUIRED_STATUS);

        $status = ArrayUtils::tryGet($data, 'status');
        if ($status === MotTestStatusName::ABORTED) {
            $this->validateRequiredField(
                MotTestStatusChangeService::FIELD_REASON_FOR_CANCEL,
                $data,
                self::MESSAGE_REQUIRED_REASON_FOR_CANCEL
            );
        }

        $this->errors->throwIfAny();

        return true;
    }

    public function validateDataForAbandonedMotTest($data)
    {
        $this->validateRequiredField(
            MotTestStatusChangeService::FIELD_CANCEL_COMMENT,
            $data,
            self::MESSAGE_REQUIRED_REASON_DESCRIPTION
        );

        $this->errors->throwIfAny();

        return true;
    }

    public function verifyThatStatusTransitionIsPossible($motTest, $newStatus)
    {
        $this->checkMotTestStatusHasChanged($motTest, $newStatus);
        $this->checkMotTestStatusIsValid($newStatus);
        $this->checkStatusTransitionIsValid($motTest, $newStatus);

        return true;
    }

    /**
     * @param MotTest $motTest
     *
     * @return bool
     *
     * @throws BadRequestException
     */
    public function hasSiteIdBeenEntered(MotTest $motTest)
    {
        if ($motTest->getVehicleTestingStation() == null) {
            throw new BadRequestException(
                'Site ID - enter the site ID',
                BadRequestException::ERROR_CODE_INVALID_DATA,
                'enter the site ID'
            );
        }

        return true;
    }

    public function checkMotTestCanBeCancelled(MotTest $motTest)
    {
        if (!$motTest->isActive()) {
            throw new BadRequestException(
                'The MOT Test status is not active so cannot be cancelled.',
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }
    }

    public function checkMotTestCanBeAbortedByVe(MotTest $motTest)
    {
        if (!$motTest->isActive()) {
            throw new BadRequestException(
                'The MOT Test status is not active so cannot be aborted.',
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }
    }

    private function checkMotTestStatusHasChanged(MotTest $motTest, $newStatus)
    {
        $currentStatus = $motTest->getStatus();

        if ($newStatus === $currentStatus) {
            throw new BadRequestException(
                'The MOT Test status has not changed from '.(string) $currentStatus.', updates are not being saved.',
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }
    }

    private function checkMotTestStatusIsValid($status)
    {
        $validStatusValues = [
            MotTestStatusName::ACTIVE,
            MotTestStatusName::PASSED,
            MotTestStatusName::FAILED,
            MotTestStatusName::ABORTED,
            MotTestStatusName::ABORTED_VE,
            MotTestStatusName::ABANDONED,
        ];

        if (!in_array($status, $validStatusValues)) {
            throw new BadRequestException(
                'MOT Test status must be PASSED, FAILED, ACTIVE, ABORTED, ABORTED_VE or ABANDONED',
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }
    }

    private function checkStatusTransitionIsValid(MotTest $motTest, $newStatus)
    {
        $currentStatus = $motTest->getStatus();
        $abortedOrAbandonedDemo = MotTestType::isDemo($motTest->getMotTestType()->getCode())
            && in_array($newStatus, [MotTestStatusName::ABANDONED, MotTestStatusName::ABORTED]);

        if ($abortedOrAbandonedDemo) {
            throw new BadRequestException(
                'A demo test cannot be aborted/abandoned',
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }

        if (!$motTest->isActive()) {
            throw new BadRequestException(
                "The MOT Test status $currentStatus is final and can not be changed to $newStatus.",
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }

        if ($newStatus === MotTestStatusName::PASSED
            || $newStatus === MotTestStatusName::FAILED
        ) {
            if ($this->motTestStatusService->isIncomplete($motTest)) {
                throw new BadRequestException(
                    "The MOT Test is incomplete, unable to change status to $newStatus",
                    BadRequestException::ERROR_CODE_INVALID_DATA
                );
            }

            $testHasFailures = $motTest->hasFailures();

            if ($newStatus === MotTestStatusName::PASSED && $testHasFailures) {
                throw new BadRequestException(
                    'The MOT Test contains failures and can not be passed',
                    BadRequestException::ERROR_CODE_INVALID_DATA
                );
            }

            if ($newStatus === MotTestStatusName::FAILED && !$testHasFailures) {
                throw new BadRequestException(
                    'The MOT Test does not contain failures and can not be failed',
                    BadRequestException::ERROR_CODE_INVALID_DATA
                );
            }
        }
    }
}
