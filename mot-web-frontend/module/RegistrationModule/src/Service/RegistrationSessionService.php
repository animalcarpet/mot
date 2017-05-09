<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\RegistrationModule\Service;

use Core\Service\SessionService;

/**
 * Class RegistrationSessionService.
 */
class RegistrationSessionService extends SessionService
{
    const UNIQUE_KEY = 'registration';

    public function checkQuestionsAvailable()
    {
        $groupA = 'securityQuestionsGroupA';
        $groupB = 'securityQuestionsGroupB';

        if (!($this->sessionContainer->offsetExists($groupA) && ($this->sessionContainer->offsetExists($groupB)))) {
            $questions = $this->getSecurityQuestions();
            $this->save($groupA, $questions['groupA']);
            $this->save($groupB, $questions['groupB']);
        }
    }

    /**
     * @return array
     */
    public function getSecurityQuestions()
    {
        $questionSet = $this->mapper->SecurityQuestion->fetchAllGroupedAndOrdered();

        return [
            'groupA' => $this->getQuestionForGroup($questionSet->getGroupOne()),
            'groupB' => $this->getQuestionForGroup($questionSet->getGroupTwo()),
        ];
    }

    /**
     * @param $questions
     *
     * @return array
     */
    private function getQuestionForGroup($questions)
    {
        $result = [];
        /** @var \DvsaCommon\Dto\Security\SecurityQuestionDto $question */
        foreach ($questions as $question) {
            $result[$question->getId()] = $question->getText();
        }

        return $result;
    }
}
