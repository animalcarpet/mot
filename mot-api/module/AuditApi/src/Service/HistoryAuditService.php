<?php

namespace Dvsa\Mot\AuditApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaApplicationLogger\Log\Logger;
use DvsaEntities\Entity\Person;
use Zend\Log\LoggerInterface;

/**
 * Class HistoryAuditService.
 */
class HistoryAuditService
{
    const AUDIT_USER_VAR = '@app_user_id';

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Person
     */
    private $user;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param EntityManager        $entityManager
     * @param Person|null          $user
     * @param LoggerInterface|null $logger
     */
    public function __construct(EntityManager $entityManager, Person $user = null, LoggerInterface $logger = null)
    {
        $this->em = $entityManager;
        if ($logger !== null) {
            $this->setLogger($logger);
        }
        if ($user !== null) {
            $this->setUser($user);
        }
    }

    /**
     * Sets the variables into the db session.
     */
    public function execute()
    {
        if ($this->user === null) {
            throw new \LogicException('User dependency not found. Have you supplied the required Person dependency?');
        }

        $connectionVars = $this->getSessionVariables();
        $query = $this->createQuery($connectionVars);
        $this->executeQuery($query);

        if ($this->logger) {
            $this->logger->info(get_class($this).' : KDD069 compliance set '.$query);
        }
    }

    /**
     * Produces an SQL query from a key=>value array of sql connection env variables.
     *
     * @param array $connectionVars
     *
     * @return string
     */
    protected function createQuery(array $connectionVars)
    {
        $vars = [];
        foreach ($connectionVars as $option => $value) {
            $vars[] = $option." = '".$value."',";
        }
        $trimmedImploded = substr(implode(' ', $vars), 0, -1);
        $sql = 'SET '.$trimmedImploded;

        return $sql;
    }

    /**
     * @param Person $user
     *
     * @return $this
     */
    public function setUser(Person $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param LoggerInterface $logger
     *
     * @return $this
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return array
     */
    private function getSessionVariables()
    {
        $connectionVars = [
            self::AUDIT_USER_VAR => $this->user->getId(),
        ];

        return $connectionVars;
    }

    /**
     * @param string $sql
     *
     * @return \Doctrine\DBAL\Driver\Statement
     */
    private function executeQuery($sql)
    {
        /** @var Connection $connection */
        $connection = $this->em->getConnection();

        return $connection->executeQuery($sql);
    }
}
