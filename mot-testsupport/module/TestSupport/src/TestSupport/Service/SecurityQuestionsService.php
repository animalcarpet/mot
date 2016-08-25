<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;
use TestSupport\Helper\SecurityAnswerHash;
use TestSupport\Helper\TestDataResponseHelper;

class SecurityQuestionsService
{
    const PERSON_ID = 'person';
    const QUESTION  = 'question';
    const ANSWER    = 'answer';

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create($personId = 1, $questionGroup = 1, $answer = 'Blah')
    {
        $answer = (new SecurityAnswerHash())->hash($answer);

        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();

        $questionId = $this->entityManager->getConnection()->executeQuery(
            'SELECT id FROM security_question WHERE question_group = :question_group LIMIT 1',
            [
                'question_group' => $questionGroup,
            ]
        )->fetch()['id'];

        $connection->transactional(
            function () use ($personId, $answer, $questionId, $connection) {
                $connection->executeQuery(
                    'INSERT INTO person_security_question_map(person_id, security_question_id, answer, created_by)
                      VALUE(:person_id, :security_question_id, :answer, :created_by)',
                    [
                        'person_id'             => $personId,
                        'security_question_id'  => $questionId,
                        'answer'                => $answer,
                        'created_by'            => 1,
                    ]
                );
            }
        );

        return TestDataResponseHelper::jsonOk('Success');
    }
}