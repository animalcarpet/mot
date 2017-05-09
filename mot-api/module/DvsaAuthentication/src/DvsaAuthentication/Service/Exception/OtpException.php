<?php

namespace DvsaAuthentication\Service\Exception;

use DvsaCommonApi\Service\Exception\ServiceException;

/**
 * Class OtpException.
 */
class OtpException extends ServiceException
{
    const ERROR_CODE_UNAUTHORIZED = 401;
    const ERROR_CODE_FORBIDDEN = 403;
    const OTP_TOKEN_INVALID_ERROR_CODE = 140;

    const MESSAGE_INVALID = 'The PIN you have entered is incorrect';
    const MESSAGE_RESET = 'The PIN you have entered does not match the PIN for this user account, you can reset your PIN in Your profile';
    const MESSAGE_OTP_REQUIRED = 'PIN required';

    /**
     * @description Acceptance criteria: VM-5189
     *
     * @param string $attemptsTotal
     * @param int    $attemptsLeft
     */
    public function __construct($attemptsTotal, $attemptsLeft)
    {
        $message = null;
        $shortMessage = null;

        parent::__construct($message, self::ERROR_CODE_UNAUTHORIZED);

        if ($attemptsLeft === $attemptsTotal) {
            $message = self::MESSAGE_OTP_REQUIRED;
        } elseif ($attemptsLeft == 0) {
            $message = self::MESSAGE_RESET;
            $shortMessage = self::MESSAGE_INVALID;
        } else {
            $message = self::MESSAGE_INVALID;
        }

        $this->_errors[] = self::createError($message, self::OTP_TOKEN_INVALID_ERROR_CODE, $message);
        $this->_errorData = [
            'message' => $message,
            'shortMessage' => $shortMessage,
            'attempts' => [
                'total' => $attemptsTotal,
                'left' => $attemptsLeft,
            ],
        ];
    }
}
