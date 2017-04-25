<?php

namespace DvsaCommon\UrlBuilder;

/**
 * Class RegistrationUrlBuilderWeb.
 *
 * @deprecated Use the route name directly instead, while using the URL generator helper.
 */
class RegistrationUrlBuilderWeb extends AbstractUrlBuilder
{
    const MAIN = '/account/register';
    const CONTACT_DETAILS = '/contact-details';
    const CREATE = '/create-an-account';
    const EMAIL = '/email';
    const DETAILS = '/details';
    const SUMMARY = '/summary';
    const PASSWORD = '/password';
    const SECURITY_QUESTIONS = '/security-questions';

    /**
     * Keys define the route, we need to set the value of each because of the way AbstractUrlBuilder checks
     * the routing when converting back to a string.
     *
     * @see AbstractUrlBuilder::verifyOrderOfRoute
     * @var array
     */
    protected $routesStructure = [
        self::MAIN => [
            self::CREATE => '',
            self::EMAIL => '',
            self::CONTACT_DETAILS => '',
            self::DETAILS => '',
            self::SUMMARY => '',
            self::PASSWORD => '',
            self::SECURITY_QUESTIONS => '',
        ]
    ];

    /**
     * @return $this
     */
    public function register()
    {
        $this->appendRoutesAndParams(self::MAIN);
        return $this;
    }

    /**
     * @return $this
     */
    public function contactDetailsStep()
    {
        return $this->register()
            ->appendRoutesAndParams(self::CONTACT_DETAILS);
    }

    /**
     * @return $this
     */
    public function passwordStep()
    {
        return $this->register()
            ->appendRoutesAndParams(self::PASSWORD);
    }

    /**
     * @return $this
     */
    public function emailStep()
    {
        return $this->register()
            ->appendRoutesAndParams(self::EMAIL);
    }

    /**
     * @return $this
     */
    public function detailsStep()
    {
        return $this->register()
            ->appendRoutesAndParams(self::DETAILS);
    }

    /**
     * @return $this
     */
    public function summaryStep()
    {
        return $this->register()
            ->appendRoutesAndParams(self::SUMMARY);
    }

    /**
     * @return $this
     */
    public function createStep()
    {
        return $this->register()
            ->appendRoutesAndParams(self::CREATE);
    }

    public function securityQuestionsStep()
    {
        return $this->register()
            ->appendRoutesAndParams(self::SECURITY_QUESTIONS);
    }
}