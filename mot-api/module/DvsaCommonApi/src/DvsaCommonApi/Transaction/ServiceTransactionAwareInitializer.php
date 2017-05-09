<?php

namespace DvsaCommonApi\Transaction;

use Doctrine\ORM\EntityManager;
use Zend\ServiceManager\InitializerInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Zend 2 component used to inject implementation of TransactionAwareInterface
 * into components implementing it.
 *
 * Class ServiceTransactionAwareInitializer
 */
class ServiceTransactionAwareInitializer implements InitializerInterface
{
    public function initialize($instance, ServiceLocatorInterface $serviceLocator)
    {
        if ($instance instanceof TransactionAwareInterface) {
            $em = $serviceLocator->get(EntityManager::class);
            $transactionExecutor = new DoctrineTransactionExecutor($em);
            $instance->setTransactionExecutor($transactionExecutor);
        }
    }
}
