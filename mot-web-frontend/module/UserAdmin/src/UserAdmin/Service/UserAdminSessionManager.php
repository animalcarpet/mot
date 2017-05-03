<?php

namespace UserAdmin\Service;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Zend\Session\AbstractContainer;
use Zend\Session\Container;

/**
 * Class UserAdminSessionManager.
 */
class UserAdminSessionManager
{
    const FIRST_QUESTION = 1;
    const SECOND_QUESTION = 2;
    const MAX_NUMBER_ATTEMPT = 3;
    const USER_ADMIN_SESSION_NAME = 'userAdminSession';
    const USER_KEY = 'user';
    const USER_NAME_KEY = 'username';
    const SEARCH_PARAM_KEY = 'searchParams';
    const EMAIL_SENT = 'email-sent';
    const EMAIL_ADDRESS = 'emailAddress';

    /**
     * @var AbstractContainer
     */
    private $userAdminSession;

    /**
     * @var \Core\Service\MotFrontendAuthorisationServiceInterface
     */
    protected $authorisationService;

    /**
     * Initialization of the User Admin Session Manager.
     *
     * @param \Zend\Session\Container                                $container
     * @param \Core\Service\MotFrontendAuthorisationServiceInterface $authorisationService
     */
    public function __construct(Container $container, MotFrontendAuthorisationServiceInterface $authorisationService)
    {
        $this->authorisationService = $authorisationService;
        $this->userAdminSession = $container;
    }

    /**
     * This function initialise the user admin session to the default state.
     *
     * @param int   $personId
     * @param array $searchParams
     */
    public function createUserAdminSession($personId, $searchParams)
    {
        $this->userAdminSession->offsetSet(self::USER_KEY, $personId);
        $this->userAdminSession->offsetSet(self::getSuccessKey(self::FIRST_QUESTION), false);
        $this->userAdminSession->offsetSet(self::getAttemptKey(self::FIRST_QUESTION), self::MAX_NUMBER_ATTEMPT);
        $this->userAdminSession->offsetSet(self::getSuccessKey(self::SECOND_QUESTION), false);
        $this->userAdminSession->offsetSet(self::getAttemptKey(self::SECOND_QUESTION), self::MAX_NUMBER_ATTEMPT);
        $this->userAdminSession->offsetSet(self::SEARCH_PARAM_KEY, http_build_query($searchParams));
    }

    public function isUserAuthenticated($personId)
    {
        return $this->getElementOfUserAdminSession(self::USER_KEY) == $personId
                && $this->getElementOfUserAdminSession(self::getSuccessKey(self::FIRST_QUESTION)) === true
                && $this->getElementOfUserAdminSession(self::getSuccessKey(self::SECOND_QUESTION)) === true;
    }

    /**
     * This function delete the user admin session.
     */
    public function deleteUserAdminSession()
    {
        $this->userAdminSession->getManager()->getStorage()->clear(self::USER_ADMIN_SESSION_NAME);
    }

    /**
     * This function set the value for one element of the session.
     *
     * @param $key
     * @param $value
     */
    public function updateUserAdminSession($key, $value)
    {
        $this->userAdminSession->offsetSet($key, $value);
    }

    /**
     * This function return the value for one element of the session.
     *
     * @param $key
     *
     * @return mixed
     */
    public function getElementOfUserAdminSession($key)
    {
        return $this->userAdminSession->offsetGet($key);
    }

    /**
     * This function return true if the key exist in the session.
     *
     * @param $key
     *
     * @return bool
     */
    public function checkElementOfUserAdminSession($key)
    {
        return $this->userAdminSession->offsetExists($key);
    }

    public static function getSuccessKey($questionNr)
    {
        return implode('', ['question', $questionNr, '-success']);
    }

    public static function getAttemptKey($questionNumber)
    {
        return implode('', ['question', $questionNumber, '-attempt']);
    }
}
