<?php

namespace DvsaEntities\DqlBuilder;

use DvsaEntities\DqlBuilder\SearchParam\TransactionSearchParam;

/**
 * Class TransactionSearchParamDqlBuilder.
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TransactionSearchParamDqlBuilder extends SearchParamDqlBuilder
{
    /**
     * Provides an opportunity to initialize and values before processing.
     *
     * @return $this
     */
    public function initialize()
    {
        return $this;
    }

    /**
     * Build the Dql from the params.
     *
     * @param bool $totalCount
     *
     * @return mixed|void
     */
    protected function buildDql($totalCount = false)
    {
        $dql = [];
        $filters = [];

        $dql[] = $this->generateDqlHeader($totalCount);

        if ($this->params->getOrganisationId() != null && $this->params->getOrganisationId() != null) {
            $this->addFiltersByValues(
                $filters,
                [$this->params->getOrganisationId()], 'transaction.organisation = :ORGANISATION_ID', '%s'
            );
        }

        if ($this->params->getStatus() != null) {
            $this->addFiltersByValues($filters, [$this->params->getStatus()], 'transaction.status = :STATUS', '%s');
        }

        if ($this->params->getDateFrom() != null) {
            $this->addFiltersByValues(
                $filters,
                [$this->params->getDateFrom()], 'transaction.completedOn >= :DATE_FROM', '%s'
            );
        }

        if ($this->params->getDateTo() != null) {
            $this->addFiltersByValues(
                $filters,
                [$this->params->getDateTo()], 'transaction.completedOn <= :DATE_TO', '%s'
            );
        }

        $dql[] = count($filters) ? implode(' AND ', $filters) : '1';

        $this->generateDqlFooter($totalCount, $dql);
    }

    /**
     * Build the Query and add any parameters.
     *
     * @param bool $totalCount
     *
     * @return mixed|void
     */
    protected function buildQuery($totalCount = false)
    {
        $query = $this->createQuery($totalCount);

        if ($this->params->getOrganisationId() > 0) {
            $this->addParametersByValues($query, [$this->params->getOrganisationId()], 'ORGANISATION_ID', '%d');
        }

        if ($this->params->getStatus() != null) {
            $query->setParameter('STATUS', $this->params->getStatus());
        }

        if ($this->params->getDateFrom() != null && $this->params->getDateTo() != null) {
            $query->setParameter('DATE_FROM', $this->params->getDateFrom());
            $query->setParameter('DATE_TO', $this->params->getDateTo());
        }

        $this->assignQuery($totalCount, $query);
    }

    /**
     * Build the correct DQL header depending on the current settings.
     *
     * @param $totalCount
     *
     * @return string
     */
    protected function generateDqlHeader($totalCount)
    {
        $select = $totalCount ? 'count(transaction)' : 'transaction';

        return 'SELECT '.$select.' from DvsaEntities\Entity\TestSlotTransaction transaction '.
                'LEFT JOIN DvsaEntities\Entity\Payment p '.
                'WITH transaction.payment = p.id '.
                'LEFT JOIN DvsaEntities\Entity\Organisation o '.
                'WITH transaction.organisation = o.id '.
                'WHERE';
    }

    /**
     * Build the correct DQL footer depending on the current settings.
     *
     * @param $totalCount
     * @param $dql
     */
    protected function generateDqlFooter($totalCount, $dql)
    {
        if ($totalCount) {
            $this->searchCountDql = implode(' ', $dql);
        } else {
            $dql[] = "ORDER BY {$this->getOrderForDql()} ".
                $this->params->getSortDirection();
            $this->searchDql = implode(' ', $dql);
        }
    }

    public function getOrderForDql()
    {
        $name = $this->params->getSortName();

        $entityAliasPart = 'transaction.';

        if (in_array(
            $name,
            [
                TransactionSearchParam::SORT_COL_AMOUNT,
            ]
        ) !== false) {
            $entityAliasPart = 'p.';
        }

        return $entityAliasPart.$name;
    }
}
