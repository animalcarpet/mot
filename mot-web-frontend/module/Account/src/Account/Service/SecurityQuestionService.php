<?php

namespace Account\Service;

use DvsaClient\MapperFactory;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;
use DvsaClient\Entity\Person;
use Account\Validator\ClaimValidator;

/**
 * Class SecurityQuestionService.
 */
class SecurityQuestionService
{
    const NUMBER_ATTEMPT_SEVERAL = 'Your answer is not correct, you have %s more tries';
    const NUMBER_ATTEMPT_ONE = 'Your answer is not correct, you have %s more try';

    const NO_VALUE_ENTER = 'You must enter an answer';

    /** @var MapperFactory $mapper */
    private $mapper;
    /** @var UserAdminSessionManager $sessionManager */
    private $session;
    /** @var SecurityQuestionDto */
    private $question;
    /** @var Person */
    private $person;
    private $userId;
    private $questionNumber;

    /**
     * @param MapperFactory           $mapper
     * @param UserAdminSessionManager $session
     */
    public function __construct(
        MapperFactory $mapper,
        UserAdminSessionManager $session
    ) {
        $this->mapper = $mapper;
        $this->session = $session;
    }

    /**
     * @param int $userId
     * @param int $questionNumber
     *
     * @return $this
     */
    public function setUserAndQuestion($userId, $questionNumber)
    {
        $this->userId = $userId;
        $this->questionNumber = (int) $questionNumber;

        return $this;
    }

    /**
     * This function initialize the session to the default value.
     *
     * @param array $searchParams
     */
    public function initializeSession($searchParams)
    {
        if ($this->isBeginningOfTheJourney()) {
            $this->session->createUserAdminSession($this->userId, $searchParams);
        }
    }

    /**
     * This function check if the user is starting the security check.
     *
     * @return bool
     */
    public function isBeginningOfTheJourney()
    {
        $countAttempts = $this->session->getElementOfUserAdminSession(
            UserAdminSessionManager::getAttemptKey(UserAdminSessionManager::FIRST_QUESTION)
        );

        return
            ($this->session->getElementOfUserAdminSession(UserAdminSessionManager::USER_KEY) != $this->userId)
            || (
                $this->questionNumber === UserAdminSessionManager::FIRST_QUESTION
                && $countAttempts === UserAdminSessionManager::MAX_NUMBER_ATTEMPT
            )
        ;
    }

    /**
     * This function return true if the user needs to go on the next page.
     *
     * @return bool
     */
    public function isRedirectionIsNeeded()
    {
        return
            $this->isUserAuthenticated()
            || $this->isAnswerCorrect()
            || $this->getNumberOfAttempt() <= 0
        ;
    }

    /**
     * This function return if the answer to a question is valid.
     *
     * @return bool
     */
    private function isAnswerCorrect()
    {
        return $this->session->getElementOfUserAdminSession(
            UserAdminSessionManager::getSuccessKey($this->questionNumber)
        );
    }

    /**
     * This function return true if the user is successfully authenticate.
     *
     * @return bool
     */
    public function isUserAuthenticated()
    {
        return $this->session->isUserAuthenticated($this->userId);
    }

    /**
     * This function return the number of attempt of a question.
     *
     * @return int
     */
    public function getNumberOfAttempt()
    {
        return $this->session->getElementOfUserAdminSession(
            UserAdminSessionManager::getAttemptKey($this->questionNumber)
        );
    }

    /**
     * This function return the information about a person from the helper desk.
     *
     * @return Person
     *
     * @throws \Exception
     */
    public function getPerson()
    {
        if ($this->person == null) {
            $this->person = $this->mapper->Person->getById($this->userId);
        }

        return $this->person;
    }

    /**
     * This function return the information about a question for a person.
     *
     * @return SecurityQuestionDto
     *
     * @throws \Exception
     */
    public function getQuestion()
    {
        if ($this->question == null) {
            $this->question = $this->mapper->UserAdmin->getSecurityQuestion($this->questionNumber - 1, $this->userId);
        }

        return $this->question;
    }

    /**
     * This function validate the information about a question for a person.
     *
     * @param string $answer
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function validateQuestion($answer)
    {
        $result = $this->mapper->UserAdmin->checkSecurityQuestion(
            $this->getQuestion()->getId(),
            $this->userId,
            ['answer' => $answer]
        );

        return $this->checkResultOfTheAnswer($result);
    }

    /**
     * This function is validating is answer and return the view or the next page in function of it.
     *
     * @param Request                                    $request
     * @param \Zend\Mvc\Controller\Plugin\FlashMessenger $flashMessenger
     *
     * @return ViewModel|Response
     */
    public function manageSessionQuestion($request, $flashMessenger)
    {
        if ($request->isPost()) {
            $answer = $request->getPost('question'.$this->questionNumber);
            if (strlen(trim($answer)) <= 0) {
                $flashMessenger->addErrorMessage(self::NO_VALUE_ENTER);

                return $this->isRedirectionIsNeeded();
            }

            if (strlen($answer) > ClaimValidator::MAX_ANSWER) {
                $flashMessenger->addErrorMessage(
                    sprintf(
                        ClaimValidator::ERR_MSG_ANSWER_MAX,
                        ClaimValidator::MAX_ANSWER
                    )
                );

                return $this->isRedirectionIsNeeded();
            }

            $success = $this->validateQuestion($answer);
            $this->addMessage($flashMessenger, $success);

            return $this->isRedirectionIsNeeded();
        }

        $this->initializeSession($request->getQuery()->toArray());

        return $this->isRedirectionIsNeeded();
    }

    /**
     * This function get the error message depending on success or not.
     *
     * @param \Zend\Mvc\Controller\Plugin\FlashMessenger $flashMessenger
     * @param $success
     */
    private function addMessage($flashMessenger, $success)
    {
        if ($success == true) {
            $flashMessenger->addSuccessMessage($this->getSuccessMessage());
        } else {
            $flashMessenger->addErrorMessage($this->getErrorMessage());
        }
    }

    /**
     * This function check the result of the answer.
     *
     * @param bool $result
     *
     * @return bool
     */
    private function checkResultOfTheAnswer($result)
    {
        $sessionSuccessKey = UserAdminSessionManager::getSuccessKey($this->questionNumber);
        $sessionAttemptKey = UserAdminSessionManager::getAttemptKey($this->questionNumber);

        if ($result === true) {
            $this->session->updateUserAdminSession($sessionSuccessKey, true);

            return true;
        }

        $nbOfAttempt = $this->session->getElementOfUserAdminSession($sessionAttemptKey);
        $this->session->updateUserAdminSession($sessionAttemptKey, $nbOfAttempt - 1);

        if ($nbOfAttempt <= 0) {
            $this->session->updateUserAdminSession($sessionSuccessKey, false);
        }

        return false;
    }

    /**
     * GETTER.
     */

    /**
     * This function return the good message for success.
     *
     * @return array
     */
    public function getSuccessMessage()
    {
        return ['First security question - your answer was ', 'correct'];
    }

    /**
     * This function return the good message for error.
     *
     * @return array
     */
    public function getErrorMessage()
    {
        if ($this->questionNumber == 1) {
            return ['First security question - your answer is ', 'not correct'];
        }

        return ['Second security question - your answer is ', 'not correct'];
    }

    /**
     * This function return the correct error message for the security question.
     *
     * @return string
     */
    public function getNumberOfAttemptMessage()
    {
        if ($this->getNumberOfAttempt() < 3 && $this->getNumberOfAttempt() > 1) {
            return sprintf(self::NUMBER_ATTEMPT_SEVERAL, $this->getNumberOfAttempt());
        } elseif ($this->getNumberOfAttempt() === 1) {
            return sprintf(self::NUMBER_ATTEMPT_ONE, $this->getNumberOfAttempt());
        }

        return '';
    }

    /**
     * @return bool
     */
    public function getQuestionSuccess()
    {
        return $this->session->getElementOfUserAdminSession(
            UserAdminSessionManager::getSuccessKey($this->questionNumber)
        );
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return int
     */
    public function getQuestionNumber()
    {
        return $this->questionNumber;
    }

    /**
     * @return string
     */
    public function getSearchParams()
    {
        return $this->session->getElementOfUserAdminSession(UserAdminSessionManager::SEARCH_PARAM_KEY);
    }
}
