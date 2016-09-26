<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service;

use Dvsa\Mot\ApiClient\Request\Validator\Exception;
use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service\ChangeSecurityQuestionsSessionService;

class ChangeSecurityQuestionsStepService
{
    const START_STEP = 'start';
    const QUESTION_ONE_STEP = 'question-one';
    const QUESTION_TWO_STEP = 'question-two';
    const REVIEW_STEP = 'review';
    const CONFIRMATION_STEP = 'confirmation';

    /**
     * @var ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService
     */
    private $changeSecurityQuestionsSessionService;

    /**
     * ChangeSecurityQuestionsStepService constructor.
     * @param ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService
     */
    public function __construct(ChangeSecurityQuestionsSessionService $changeSecurityQuestionsSessionService)
    {
        $this->changeSecurityQuestionsSessionService = $changeSecurityQuestionsSessionService;
    }

    /**
     * @param $step
     * @param $status
     * @throws \Exception
     */
    public function updateStepStatus($step, $status)
    {
        $sessionStore = $this->changeSecurityQuestionsSessionService->load(ChangeSecurityQuestionsSessionService::UNIQUE_KEY);
        $steps = $sessionStore[ChangeSecurityQuestionsSessionService::STEP_SESSION_STORE];

        if (empty($steps)) {
            throw new \Exception('Steps are not stored in session');
        }

        if (!isset($steps[$step])) {
            throw new \Exception('Step: ' .$step. ' is not a valid step');
        }

        if (!is_bool($status)) {
            throw new \Exception('Step status must be a boolean');
        }

        $steps[$step] = $status;

        $sessionStore[ChangeSecurityQuestionsSessionService::STEP_SESSION_STORE] = $steps;

        $this->changeSecurityQuestionsSessionService->save(ChangeSecurityQuestionsSessionService::UNIQUE_KEY, $sessionStore);
    }

    /**
     * @param $step
     * @return bool
     */
    public function isAllowedOnStep($step)
    {
        $sessionStore = $this->changeSecurityQuestionsSessionService->load(ChangeSecurityQuestionsSessionService::UNIQUE_KEY);
        $steps = $sessionStore[ChangeSecurityQuestionsSessionService::STEP_SESSION_STORE];

        // If steps are not loaded or step does not exist return false
        if (is_null($steps) || !is_array($steps) || !isset($steps[$step])) {
            return false;
        }

        $previousValue = null;

        foreach ($steps as $key => $value) {
            if ($step == $key) {
                return $previousValue;
            }
            $previousValue = $value;
        }
        return false;
    }

    /**
     * @param $questionNumber
     * @param $questionId
     * @param $questionChosen
     * @param $answer
     * @throws \Exception
     */
    public function updateQuestion($questionNumber, $questionId, $questionChosen, $answer)
    {
        $sesionStore = $this->changeSecurityQuestionsSessionService->load(ChangeSecurityQuestionsSessionService::UNIQUE_KEY);
        $values = $sesionStore[ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES];

        if ($questionNumber == self::QUESTION_ONE_STEP) {
            $values['questionOneId'] = $questionId;
            $values['questionOneText'] = $questionChosen;
            $values['questionOneAnswer'] = $answer;

            $sesionStore[ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES] = $values;
            $this->changeSecurityQuestionsSessionService->save(ChangeSecurityQuestionsSessionService::UNIQUE_KEY, $sesionStore);
        } else if ($questionNumber == self::QUESTION_TWO_STEP) {
            $values['questionTwoId'] = $questionId;
            $values['questionTwoText'] = $questionChosen;
            $values['questionTwoAnswer'] = $answer;

            $sesionStore[ChangeSecurityQuestionsSessionService::SUBMITTED_VALUES] = $values;
            $this->changeSecurityQuestionsSessionService->save(ChangeSecurityQuestionsSessionService::UNIQUE_KEY, $sesionStore);
        } else {
            throw new \Exception("question number not valid ");
        }
    }

    public function getSessionData()
    {
        return $this->changeSecurityQuestionsSessionService->load(ChangeSecurityQuestionsSessionService::UNIQUE_KEY);
    }


    /**
     * Returns a list of the steps
     * @return array
     */
    public function getSteps()
    {
        return [
            self::START_STEP,
            self::QUESTION_ONE_STEP,
            self::QUESTION_TWO_STEP,
            self::REVIEW_STEP,
            self::CONFIRMATION_STEP,
        ];
    }
}