<?php

namespace TestSupport\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\SecurityAnswerHash;
use TestSupport\Helper\TestDataResponseHelper;
use TestSupport\Service\SecurityQuestionsService;
use Zend\Mvc\Controller\AbstractRestfulController;

/**
 * Vehicle related methods
 *
 * Should not be deployed in production.
 */
class SecurityQuestionDataController extends BaseTestSupportRestfulController
{
    const PERSON_ID = 'person';
    const QUESTION  = 'question';
    const ANSWER    = 'answer';

    public function create($data)
    {
        $securityQuestionsService = $this->getServiceLocator()->get(SecurityQuestionsService::class);

        $person     = ArrayUtils::tryGet($data, self::PERSON_ID, 1);
        $questionGroup   = ArrayUtils::tryGet($data, self::QUESTION, 1);
        $answer     = ArrayUtils::tryGet($data, self::ANSWER, 'Blah');

        return $securityQuestionsService->create($person, $questionGroup, $answer);
    }
}
