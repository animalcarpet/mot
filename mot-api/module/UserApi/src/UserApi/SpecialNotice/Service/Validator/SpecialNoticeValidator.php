<?php

namespace UserApi\SpecialNotice\Service\Validator;

use DvsaCommon\Date\DateUtils;
use DvsaCommon\Date\Exception\DateException;
use DvsaCommonApi\Service\Exception\InvalidFieldValueException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use UserApi\SpecialNotice\Data\SpecialNoticeAudienceMapper;

/**
 * SpecialNoticeValidator.
 */
class SpecialNoticeValidator
{
    public function validate(array $data)
    {
        RequiredFieldException::CheckIfRequiredFieldsNotEmpty(
            [
                'noticeTitle',
                'internalPublishDate',
                'externalPublishDate',
                'acknowledgementPeriod',
                'noticeText',
                'targetRoles',
            ],
            $data
        );

        if (!is_array($data['targetRoles'])) {
            throw new InvalidFieldValueException();
        }

        foreach ($data['targetRoles'] as $role) {
            if (!SpecialNoticeAudienceMapper::hasAudience($role)) {
                throw new InvalidFieldValueException();
            }
        }

        try {
            DateUtils::toDate($data['internalPublishDate']);
            DateUtils::toDate($data['externalPublishDate']);
        } catch (DateException $e) {
            throw new InvalidFieldValueException();
        }

        if (!is_numeric($data['acknowledgementPeriod'])) {
            throw new InvalidFieldValueException();
        }

        if (stristr($data['noticeText'], '<script>')
        || stristr($data['noticeText'], 'javascript:')
        ) {
            throw new InvalidFieldValueException('Notice Text Markdown may not contain Javascript');
        }

        $this->checkIfDateNotInPast($data['internalPublishDate']);
        $this->checkIfDateNotInPast($data['externalPublishDate']);
    }

    protected function checkIfDateNotInPast($dateString)
    {
        $today = new \DateTime();
        $date = new \DateTime($dateString);

        if (DateUtils::compareDates($today, $date) > 0) {
            throw new InvalidFieldValueException('Publish date cannot be in past');
        }
    }
}
