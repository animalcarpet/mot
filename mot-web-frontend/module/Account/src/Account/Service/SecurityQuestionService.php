<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace Account\Service;

use Account\Exception\LimitReachedException;
use Account\Validator\ClaimValidator;
use DvsaClient\Entity\Person;
use DvsaClient\Mapper\AccountMapper;
use DvsaClient\Mapper\PersonMapper;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaCommon\Dto\Account\MessageDto;
use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Dto\Contact\EmailDto;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\InputFilter\Account\SecurityQuestionAnswersInputFilter;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\View\Model\ViewModel;

/**
 * Class SecurityQuestionService.
 */
class SecurityQuestionService
{
    const NUMBER_ATTEMPT_SEVERAL = 'Your answer is not correct, you have %s more tries';
    const NUMBER_ATTEMPT_ONE = 'Your answer is not correct, you have %s more try';

    const NO_VALUE_ENTER = 'You must enter an answer';

    const EXCEPTION_RESET_PASS = 'Failed to extract "%s" from the API response';

    private $personMapper;

    /** @var AccountMapper */
    private $accountMapper;

    /** @var UserAdminMapper */
    private $userAdminMapper;

    /** @var UserAdminSessionManager $sessionManager */
    private $session;

    /** @var SecurityQuestionDto */
    private $question;

    /** @var Person */
    private $person;

    private $personId;

    private $questionNumber;

    private $incorrectAnswerQuestionIds;

    /**
     * @var boolean
     */
    private $verified;

    /**
     * SecurityQuestionService constructor.
     * @param PersonMapper $personMapper
     * @param UserAdminMapper $userAdminMapper
     * @param AccountMapper $accountMapper
     * @param UserAdminSessionManager $session
     */
    public function __construct(
        PersonMapper $personMapper,
        UserAdminMapper $userAdminMapper,
        AccountMapper $accountMapper,
        UserAdminSessionManager $session
    )
    {
        $this->personMapper = $personMapper;
        $this->userAdminMapper = $userAdminMapper;
        $this->accountMapper = $accountMapper;
        $this->session = $session;

        $this->incorrectAnswerQuestionIds = [];
    }

    /**
     * @param int $personId
     * @param array $answers
     * @return bool
     */
    public function areBothAnswersCorrectForPerson($personId, array $answers)
    {
        $this->initialiseRemainingAttemptsSession($personId);

        if ($this->hasRemainingAttempts()) {
            $answerResponse = $this->userAdminMapper->checkSecurityQuestions($personId, $answers);
            $areBothAnswersCorrect = $this->mapAnswerResponse($answerResponse);

            if (!$areBothAnswersCorrect) {
                $this->decrementRemainingAttempts();
            }
        } else {
            $areBothAnswersCorrect = false;
        }

        return $areBothAnswersCorrect;
    }

    /**
     * @param integer $personId
     * @param array $questionsAndAnswersMap
     * @return array
     * @throws \Exception
     */
    public function verifyAnswers($personId, array $questionsAndAnswersMap)
    {
        $this->initialiseRemainingAttemptsSession($personId);

        if (!$this->hasRemainingAttempts()) {
            throw new LimitReachedException();
        }

        $response = $this->userAdminMapper->checkSecurityQuestions($personId, $questionsAndAnswersMap);

        $this->verified = array_product($response);

        $this->decrementRemainingAttempts();

        return $this->mapVerificationResponse($response);
    }

    /**
     * @param integer $personId
     * @return string the person email address in event of successful API call
     * @throws \RuntimeException
     */
    public function resetPersonPassword($personId)
    {
        /** @var MessageDto $response */
        $response = $this->accountMapper->resetPassword($personId);

        if (!$response instanceof MessageDto || !$response->hasPerson()) {
            throw new \RuntimeException(
                'Can\'t confirm if the reset password email has been sent. ' .
                sprintf(self::EXCEPTION_RESET_PASS, MessageDto::class)
            );
        }

        if ($contactDetails = $response->getPerson()->getContactDetails()) {

            $contactDetail = reset($contactDetails);

            if (!$contactDetail instanceof ContactDto) {
                throw new \RuntimeException(sprintf(self::EXCEPTION_RESET_PASS, ContactDto::class));
            }

            if ($emails = $contactDetail->getEmails()) {

                $email = reset($emails);

                if (!$email instanceof EmailDto) {
                    throw new \RuntimeException(sprintf(self::EXCEPTION_RESET_PASS, EmailDto::class));
                }

                return $email->getEmail();
            }
        }

        throw new \RuntimeException('Failed to retrieve email address');
    }

    /**
     * @param array $response
     * @return array
     */
    private function mapVerificationResponse($response)
    {
        return array_filter(
            array_map(
                function ($verified) {
                    if (!$verified) {
                        return SecurityQuestionAnswersInputFilter::MSG_FAILED_VERIFICATION;
                    }
                }, $response),
            function ($element) {
                return (!empty($element));
            }
        );
    }

    /**
     * @return boolean
     * @throws \RuntimeException
     */
    public function isVerified()
    {
        if (is_null($this->verified)) {
            throw new \RuntimeException('Answers are not verified yet');
        }

        return $this->verified;
    }

    /**
     * @param array $answerResponse
     * @return bool
     */
    private function mapAnswerResponse(array $answerResponse)
    {
        $areBothAnswersCorrect = true;
        $incorrectAnswerQuestionIds = [];

        foreach ($answerResponse as $questionId => $isAnswerCorrect) {
            if (!$isAnswerCorrect) {
                $areBothAnswersCorrect = false;
                $incorrectAnswerQuestionIds[] = $questionId;
            }
        }
        $this->incorrectAnswerQuestionIds = $incorrectAnswerQuestionIds;

        return $areBothAnswersCorrect;
    }

    /**
     * @return bool
     */
    public function getRemainingAttempts()
    {
        $remainingAttempts = $this->session->getElementOfUserAdminSession(
            UserAdminSessionManager::FORGOTTEN_PASSWORD_REMAINING_ATTEMPTS_KEY
        );

        if ($remainingAttempts === null) {
            return UserAdminSessionManager::MAX_NUMBER_ATTEMPT;
        }

        return $remainingAttempts;
    }

    /**
     * @return bool
     */
    public function hasRemainingAttempts()
    {
        return $this->getRemainingAttempts() > 0;
    }

    /**
     * @param $personId
     */
    private function initialiseRemainingAttemptsSession($personId)
    {
        if ($this->session->getElementOfUserAdminSession(UserAdminSessionManager::USER_KEY) != $personId) {
            $this->session->createForgottenPasswordSession($personId);
        }
    }

    /**
     */
    private function decrementRemainingAttempts()
    {
        $this->session->updateUserAdminSession(
            UserAdminSessionManager::FORGOTTEN_PASSWORD_REMAINING_ATTEMPTS_KEY,
            $this->getRemainingAttempts() - 1
        );
    }

    /**
     * @return array
     */
    public function getIncorrectAnswerQuestionIds()
    {
        return $this->incorrectAnswerQuestionIds;
    }

    /**
     * @param $personId
     * @return SecurityQuestionDto[]
     */
    public function getQuestionsForPerson($personId)
    {
        return $this->userAdminMapper->getSecurityQuestionsForPerson($personId);
    }

    /**
     * @param int $personId
     * @param int $questionNumber
     *
     * @return $this
     */
    public function setUserAndQuestion($personId, $questionNumber)
    {
        $this->personId = $personId;
        $this->questionNumber = (int)$questionNumber;
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
            $this->session->createUserAdminSession($this->personId, $searchParams);
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
            ($this->session->getElementOfUserAdminSession(UserAdminSessionManager::USER_KEY) != $this->personId)
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
        return $this->session->isUserAuthenticated($this->personId);
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
            $this->person = $this->personMapper->getById($this->personId);
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
            $this->question = $this->userAdminMapper->getSecurityQuestion($this->questionNumber - 1, $this->personId);
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
        $result = $this->userAdminMapper->checkSecurityQuestion(
            $this->getQuestion()->getId(),
            $this->personId,
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
        return $this->personId;
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
