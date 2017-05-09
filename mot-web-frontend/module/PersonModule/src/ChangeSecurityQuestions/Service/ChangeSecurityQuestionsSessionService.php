<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service;

use Core\Service\SessionService;

class ChangeSecurityQuestionsSessionService extends SessionService
{
    const UNIQUE_KEY = 'change_security_questions';
    const SECURITY_QUESTIONS_SESSION_STORE = 'QUESTION_STORE';
    const STEP_SESSION_STORE = 'steps';
    const SUBMITTED_VALUES = 'submittedValues';
}
