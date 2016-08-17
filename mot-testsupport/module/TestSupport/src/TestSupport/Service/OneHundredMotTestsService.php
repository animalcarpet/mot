<?php

namespace TestSupport\Service;

use Doctrine\ORM\EntityManager;

/**
 * Creates one hundred Mot tests for a user for use by satisfaction survey tests.
 *
 * Should not be deployed in production.
 */
class OneHundredMotTestsService
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * OneHundredMotTestsService constructor.
     *
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param int $userId
     *
     * @return bool
     */
    public function create($userId)
    {
        $query = 'SET @app_user_id=' . $userId .'; CALL generate_mot_tests_for_survey();';
        $statement = $this->entityManager->getConnection()->prepare($query);

        return $statement->execute();
    }

}
