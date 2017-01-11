<?php

namespace DvsaCommon\UrlBuilder;

/**
 * Class AccountUrlBuilderWeb
 * @package DvsaCommon\UrlBuilder
 */
class AccountUrlBuilderWeb extends AbstractUrlBuilder
{
    const FORGOTTEN_PASSWORD = '/forgotten-password';
    const AUTHENTICATED = '/authenticated';
    const NOT_AUTHENTICATED = '/not-authenticated';
    const CONFIRMATION = '/confirmation-email';
    const SECURITY_QUESTION = '/security-question/:personId/:questionNumber';
    const RESET_PASSWORD = '/reset/:resetToken';
    const EMAIL_NOT_FOUND = '/email-not-found';

    const ACCOUNT = '/account';
    const CLAIM = '/claim';
    const CLAIM_EMAIL_AND_PASSWORD = '/confirm-email-and-password';
    const CLAIM_SECURITY_QUESTIONS = '/set-security-question';
    const CLAIM_REVIEW = '/review';
    const CLAIM_DISPLAY_PIN = '/display-pin';
    const CLAIM_RESET = '/reset';

    protected $routesStructure
        = [
            self::FORGOTTEN_PASSWORD => [
                self::AUTHENTICATED => '',
                self::NOT_AUTHENTICATED => '',
                self::CONFIRMATION => '',
                self::SECURITY_QUESTION => '',
                self::EMAIL_NOT_FOUND => '',
                self::RESET_PASSWORD => '',
            ],
            self::ACCOUNT => [
                self::CLAIM => [
                    self::CLAIM_EMAIL_AND_PASSWORD => '',
                    self::CLAIM_SECURITY_QUESTIONS => '',
                    self::CLAIM_DISPLAY_PIN => '',
                    self::CLAIM_REVIEW => '',
                    self::CLAIM_RESET => '',
                ],
            ],
        ];

    public function __construct()
    {
        return $this;
    }

    public static function of()
    {
        return new static();
    }

    public static function forgottenPassword()
    {
        return self::of()->appendRoutesAndParams(self::FORGOTTEN_PASSWORD);
    }

    public static function forgottenPasswordAuthenticated()
    {
        return self::forgottenPassword()
            ->appendRoutesAndParams(self::AUTHENTICATED);
    }

    public static function forgottenPasswordNotAuthenticated()
    {
        return self::forgottenPassword()
            ->appendRoutesAndParams(self::NOT_AUTHENTICATED);
    }

    public static function forgottenPasswordConfirmation()
    {
        return self::forgottenPassword()
            ->appendRoutesAndParams(self::CONFIRMATION);
    }

    public static function forgottenPasswordEmailNotFound()
    {
        return self::forgottenPassword()
            ->appendRoutesAndParams(self::EMAIL_NOT_FOUND);
    }

    public static function forgottenPasswordSecurityQuestion($personId, $questionNumber)
    {
        return self::forgottenPassword()
            ->appendRoutesAndParams(self::SECURITY_QUESTION)
            ->routeParam('personId', $personId)
            ->routeParam('questionNumber', $questionNumber);
    }

    public static function resetPasswordByToken($token)
    {
        return self::forgottenPassword()
            ->appendRoutesAndParams(self::RESET_PASSWORD)
            ->routeParam('resetToken', $token);
    }

    public static function account()
    {
        return self::of()->appendRoutesAndParams(self::ACCOUNT);
    }

    private static function claim()
    {
        return self::account()->appendRoutesAndParams(self::CLAIM);
    }

    public static function claimEmailAndPassword()
    {
        return self::claim()->appendRoutesAndParams(self::CLAIM_EMAIL_AND_PASSWORD);
    }

    public static function claimSecurityQuestions()
    {
        return self::claim()->appendRoutesAndParams(self::CLAIM_SECURITY_QUESTIONS);
    }

    public static function claimReview()
    {
        return self::claim()->appendRoutesAndParams(self::CLAIM_REVIEW);
    }

    public static function claimDisplayPin()
    {
        return self::claim()->appendRoutesAndParams(self::CLAIM_DISPLAY_PIN);
    }

    public static function claimReset()
    {
        return self::claim()->appendRoutesAndParams(self::CLAIM_RESET);
    }
}
