<?php

namespace DvsaCommonApi\Transaction;

use Doctrine\ORM\EntityManagerInterface;

/**
 * Transaction executor for Doctrine.
 *
 * Class DoctrineTransactionExecutor
 */
class DoctrineTransactionExecutor implements TransactionExecutorInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function inTransaction(callable $block)
    {
        return $this->entityManager->transactional($block);
    }

    public function flush()
    {
        $this->entityManager->flush();
    }

    public function beginTransaction()
    {
        $this->entityManager->beginTransaction();
    }
}
