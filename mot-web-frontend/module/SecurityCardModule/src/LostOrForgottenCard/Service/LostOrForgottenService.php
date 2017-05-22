<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Service;

use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\LostOrForgottenCardController;
use DvsaClient\Mapper\UserAdminMapper;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;

class LostOrForgottenService
{
    /**
     * @var UserAdminMapper
     */
    private $userAdminMapper;

    /**
     * @var LostOrForgottenSessionService
     */
    private $sessionService;

    public function __construct(UserAdminMapper $userAdminMapper, LostOrForgottenSessionService $sessionService)
    {
        $this->userAdminMapper = $userAdminMapper;
        $this->sessionService = $sessionService;
    }
    
    /**
     * @param $personId
     * @return \DvsaCommon\Dto\Security\SecurityQuestionDto[]
     * @throws \Exception
     */
    public function getQuestionsForPerson($personId)
    {
        try {
            return $this->userAdminMapper->getSecurityQuestionsForPerson($personId);
        } catch (NotFoundException $e) {
            throw new \Exception('Security Questions not found for user '.$personId);
        }
    }


    /**
     * @param int    $questionId
     * @param int    $userId
     * @param string $answer
     *
     * @return bool
     */
    public function getAnswerForQuestion($questionId, $userId, $answer)
    {
        return $this->userAdminMapper->checkSecurityQuestion($questionId, $userId, ['answer' => $answer]);
    }

    /**
     * @param $personId
     * @param array $answers
     *
     * @return array [questionId => validationResultBoolean, ...]
     */
    public function verifyAnswersForPerson($personId, array $answers)
    {
        return $this->userAdminMapper->checkSecurityQuestions($personId, $answers);
    }

    /**
     * @param array $steps
     */
    public function saveSteps(array $steps)
    {
        $this->sessionService->save(LostOrForgottenSessionService::UNIQUE_KEY, $steps);
    }

    /**
     * @param $step
     *
     * @return bool
     */
    public function isAllowedOnStep($step)
    {
        $steps = $this->sessionService->load(LostOrForgottenSessionService::UNIQUE_KEY);

        // If steps are not loaded return false
        if (is_null($steps) || !is_array($steps)) {
            return false;
        }

        if (!isset($steps[$step])) {
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
     * @param $step
     * @param $status
     *
     * @throws \Exception
     */
    public function updateStepStatus($step, $status)
    {
        $steps = $this->sessionService->load(LostOrForgottenSessionService::UNIQUE_KEY);

        if (empty($steps)) {
            throw new \Exception('Steps are not stored in session');
        }

        if (!isset($steps[$step])) {
            throw new \Exception('Step: '.$step.' is not a valid step');
        }

        if (!is_bool($status)) {
            throw new \Exception('Step status must be a boolean');
        }

        $steps[$step] = $status;
        $this->saveSteps($steps);
    }

    public function clearSession()
    {
        $this->sessionService->clear();
    }

    public function isEnteringThroughAlreadyOrdered()
    {
        $steps = $this->sessionService->load(LostOrForgottenSessionService::UNIQUE_KEY);

        return key_exists(LostOrForgottenCardController::START_ALREADY_ORDERED_ROUTE, $steps);
    }

    public function isEnteringThroughSecurityQuestionOne()
    {
        $steps = $this->sessionService->load(LostOrForgottenSessionService::UNIQUE_KEY);

        return key_exists(LostOrForgottenCardController::LOGIN_SESSION_ROUTE, $steps);
    }
}
