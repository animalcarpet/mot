<?php

namespace DvsaCommon\UrlBuilder;

/**
 * Url Builder for api for the User Admin pages
 */
class UserAdminUrlBuilder extends AbstractUrlBuilder
{
    const PERSON_CONTACT            = 'person/:personId/contact';
    const SECURITY_QUESTION         = 'security-question';
    const SECURITY_QUESTION_CHECK   = '/check/:questionId/:personId';
    const SECURITY_QUESTION_GET     = '/get/:questionId/:personId';

    protected $routesStructure
        = [
            self::SECURITY_QUESTION =>
                [
                    self::SECURITY_QUESTION_CHECK => '',
                    self::SECURITY_QUESTION_GET => '',
                ],
            self::PERSON_CONTACT => ''
        ];

    public function __construct()
    {
        return $this;
    }

    public static function of()
    {
        return new static();
    }

    /**
     * @param $personId
     * @return $this
     */
    public static function personContact($personId)
    {
        return self::of()->appendRoutesAndParams(self::PERSON_CONTACT)->routeParam('personId', $personId);
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

}
