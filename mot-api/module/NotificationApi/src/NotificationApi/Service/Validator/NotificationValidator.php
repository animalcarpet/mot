<?php

namespace NotificationApi\Service\Validator;

use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\RequiredFieldException;
use DvsaCommonApi\Service\Validator\AbstractValidator;

/**
 * Class NotificationValidator.
 */
class NotificationValidator extends AbstractValidator
{
    const MESSAGE_INVALID_DATA_STRUCTURE = 'Invalid data type. Expected array, %s given';

    /**
     * @param array $data
     *
     * @throws BadRequestException | RequiredFieldException
     */
    public function validate($data)
    {
        $this->validateArray($data, ['template', 'recipient', 'fields']);
    }

    /**
     * @param array $data
     *
     * @throws BadRequestException | RequiredFieldException
     */
    public function validateActionData($data)
    {
        $this->validateArray($data, ['action']);
    }

    private function validateArray($data, $fields)
    {
        if (false === is_array($data)) {
            throw new BadRequestException(
                sprintf(self::MESSAGE_INVALID_DATA_STRUCTURE, gettype($data)),
                BadRequestException::ERROR_CODE_INVALID_DATA
            );
        }

        RequiredFieldException::CheckIfRequiredFieldsNotEmpty($fields, $data);
    }
}
