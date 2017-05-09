<?php

namespace DvsaCommonApi\Transaction;

/**
 * Provides default functionality to handle transactional code in the implementing class.
 * Should be used along with TransactionAwareInterface.
 *
 * Class TransactionAwareTrait
 */
trait TransactionAwareTrait
{
    /** @var TransactionExecutorInterface $transactionExecutor */
    private $transactionExecutor;

    public function setTransactionExecutor(TransactionExecutorInterface $transactionExecutor)
    {
        $this->transactionExecutor = $transactionExecutor;
    }

    public function inTransaction(callable $block)
    {
        return $this->transactionExecutor->inTransaction($block);
    }

    public function getTransactionExecutor()
    {
        return $this->transactionExecutor;
    }
}
