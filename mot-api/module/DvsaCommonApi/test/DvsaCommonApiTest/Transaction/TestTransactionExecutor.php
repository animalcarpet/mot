<?php

namespace DvsaCommonApiTest\Transaction;

use DvsaCommonApi\Transaction\TransactionAwareInterface;
use DvsaCommonApi\Transaction\TransactionExecutorInterface;

/**
 * Class TestTransactionExecutor.
 */
class TestTransactionExecutor implements TransactionExecutorInterface
{
    private static $entityManager;

    private $flushed = false;

    private static function assertImplementsInterface($instance)
    {
        if (!($instance instanceof TransactionAwareInterface)) {
            throw new \InvalidArgumentException(
                "Instance $instance does not implement: "
                .TransactionAwareInterface::class
            );
        }
    }

    public static function inject($instance, $entityManger = null)
    {
        self::assertImplementsInterface($instance);
        /* @var TransactionAwareInterface $instance */
        $instance->setTransactionExecutor(new self());

        static::$entityManager = $entityManger;

        return $instance;
    }

    public function inTransaction(callable $block)
    {
        return $block(static::$entityManager);
    }

    public function flush()
    {
        $this->flushed = true;
    }

    public static function isFlushed($instance)
    {
        self::assertImplementsInterface($instance);
        /** @var TransactionAwareInterface $instance */
        /** @var TestTransactionExecutor $executor */
        $executor = $instance->getTransactionExecutor();

        return $executor->flushed;
    }

    public function beginTransaction()
    {
    }
}
