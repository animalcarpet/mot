<?php
/**
 * This file is part of the DVSA MOT Common project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace DvsaCommon\UrlBuilder;

/**
 * Url Builder for api for the User Admin pages
 */
class UserAdminUrlBuilder extends AbstractUrlBuilder
{
    const PERSON_CONTACT            = 'person/:personId/contact';
    const PERSON_DAY_OF_BIRTH       = 'person/:personId/date-of-birth';
    const LICENCE_DETAILS           = 'person/:personId/driving-licence';
    const PERSON_NAME               = 'person/:personId/name';
    const PERSON_ADDRESS            = 'person/:personId/address';
    const PERSON_PHONE              = 'person/:personId/phone-number';
    const PERSON_FORGOTTEN_PASSWORD = 'person/:personId';
    const PERSON_GET_SECURITY_QUESTIONS = '/security-questions';
    const PERSON_VERIFY_SECURITY_ANSWERS = '/verify';
    const SECURITY_QUESTION         = 'security-question';
    const SECURITY_QUESTION_CHECK   = '/check/:questionId/:personId';
    const SECURITY_QUESTION_GET     = '/get/:questionId/:personId';

    protected $routesStructure = [
        self::SECURITY_QUESTION => [
            self::SECURITY_QUESTION_CHECK => '',
            self::SECURITY_QUESTION_GET => '',
        ],
        self::PERSON_FORGOTTEN_PASSWORD => [
            self::PERSON_GET_SECURITY_QUESTIONS => [
                self::PERSON_VERIFY_SECURITY_ANSWERS => '',
            ],
        ],
        self::PERSON_VERIFY_SECURITY_ANSWERS => '',
        self::PERSON_CONTACT => '',
        self::LICENCE_DETAILS => '',
        self::PERSON_NAME => '',
        self::PERSON_ADDRESS => '',
        self::PERSON_DAY_OF_BIRTH => '',
        self::LICENCE_DETAILS => '',
        self::PERSON_PHONE => '',
    ];

    public function __construct()
    {
        return $this;
    }

    public static function of()
    {
        return new static();
    }

    public static function licenceDetails($personId)
    {
        return self::of()->appendRoutesAndParams(self::LICENCE_DETAILS)->routeParam('personId', $personId);
    }

    public static function personName($personId)
    {
        return self::of()->appendRoutesAndParams(self::PERSON_NAME)->routeParam('personId', $personId);
    }

    public static function personAddress($personId)
    {
        return self::of()->appendRoutesAndParams(self::PERSON_ADDRESS)->routeParam('personId', $personId);
    }

    public static function personTelephone($personId)
    {
        return self::of()->appendRoutesAndParams(self::PERSON_PHONE)->routeParam('personId', $personId);
    }

    /**
     * @param $personId
     * @return $this
     */
    public static function personContact($personId)
    {
        return self::of()->appendRoutesAndParams(self::PERSON_CONTACT)->routeParam('personId', $personId);
    }

    public static function personDayOfBirth($personId)
    {
        return self::of()->appendRoutesAndParams(self::PERSON_DAY_OF_BIRTH)->routeParam('personId', $personId);
    }

    public static function securityQuestion()
    {
        return self::of()->appendRoutesAndParams(self::SECURITY_QUESTION);
    }

    public static function securityQuestionGet($questionId, $personId)
    {
        return self::securityQuestion()
            ->appendRoutesAndParams(self::SECURITY_QUESTION_GET)
            ->routeParam('questionId', $questionId)
            ->routeParam('personId', $personId);
    }

    public static function securityQuestionCheck($questionId, $personId)
    {
        return self::securityQuestion()
            ->appendRoutesAndParams(self::SECURITY_QUESTION_CHECK)
            ->routeParam('questionId', $questionId)
            ->routeParam('personId', $personId);
    }

    public static function personForgottenPassword($personId)
    {
        return self::of()->appendRoutesAndParams(self::PERSON_FORGOTTEN_PASSWORD)->routeParam('personId', $personId);
    }

    public static function getSecurityQuestions($personId)
    {
        return self::personForgottenPassword($personId)->appendRoutesAndParams(self::PERSON_GET_SECURITY_QUESTIONS);
    }

    /**
     * @param integer $personId
     * @return $this
     */
    public static function securityQuestionVerifyAnswers($personId)
    {
        return self::getSecurityQuestions($personId)->appendRoutesAndParams(self::PERSON_VERIFY_SECURITY_ANSWERS);
    }

}
