<?php

namespace DvsaCommonApi\Transaction;

/**
 * Interface exposing transactional capabilities to components implementing
 * through TransactionExecutorInterface.
 *
 * Interface TransactionAwareInterface
 */
interface TransactionAwareInterface
{
    public function setTransactionExecutor(TransactionExecutorInterface $transactionExecutor);
    public function getTransactionExecutor();
}
