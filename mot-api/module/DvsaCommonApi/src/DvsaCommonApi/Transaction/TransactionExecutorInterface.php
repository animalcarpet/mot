<?php

namespace DvsaCommonApi\Transaction;

/**
 * Provides access to transactional behaviour to implementing classes.
 *
 * Interface TransactionExecutorInterface
 */
interface TransactionExecutorInterface
{
    public function inTransaction(callable $block);

    public function flush();
}
