<?php

namespace DvsaEntities\Repository;

use DvsaEntities\DqlBuilder\TransactionSearchParamDqlBuilder;

/**
 * Repository for the TestSlotTransaction entity.
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @codeCoverageIgnore
 */
class TestSlotTransactionRepository extends AbstractMutableRepository
{
    use SearchRepositoryTrait;

    public function getAll()
    {
        return $this->findAll();
    }

    public function get($id)
    {
        return $this->find($id);
    }

    /**
     * Build the correct SQL Builder object for searching Transactions.
     *
     * @param $params
     *
     * @return TransactionSearchParamDqlBuilder
     */
    public function getSqlBuilder($params)
    {
        return new TransactionSearchParamDqlBuilder(
            $this->getEntityManager(),
            $params
        );
    }
}
