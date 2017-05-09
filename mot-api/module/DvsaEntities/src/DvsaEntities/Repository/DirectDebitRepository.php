<?php

namespace DvsaEntities\Repository;

use DvsaCommon\Enum\DirectDebitStatusCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\DirectDebit;
use DvsaEntities\Entity\DirectDebitStatus;

/**
 * Class DirectDebitRepository.
 *
 * @author Jakub Igla <jakub.igla@valtech.co.uk>
 */
class DirectDebitRepository extends AbstractMutableRepository
{
    /**
     * Get all direct debit mandates for slot increment for given collection date.
     *
     * @param \DateTime $collectionDate
     *
     * @return array
     */
    public function getValidDirectDebitMandatesForSlotIncrement($collectionDate)
    {
        $dqlBuilder = $this->getEntityManager()->createQueryBuilder();
        $directDebitActive = $this->getEntityManager()->getRepository(DirectDebitStatus::class)
            ->getByCode(DirectDebitStatusCode::ACTIVE);
        $dqlBuilder->select('dd')
            ->from($this->getEntityName(), 'dd')
            ->where('dd.status = :STATUS')
            ->andWhere('dd.nextCollectionDate = :DATE')
            ->andWhere('dd.lastIncrementDate is NULL OR dd.lastIncrementDate < :DATE')
            ->setParameters(
                [
                    'STATUS' => $directDebitActive,
                    'DATE' => $collectionDate->format('Y-m-d'),
                ]
            );

        return $dqlBuilder->getQuery()->getResult();
    }

    /**
     * @param int $organisationId
     *
     * @return DirectDebit|null
     */
    public function findFirstActiveByOrganisation($organisationId)
    {
        $directDebitActive = $this->getEntityManager()->getRepository(DirectDebitStatus::class)
            ->getByCode(DirectDebitStatusCode::ACTIVE);

        return $this->findOneBy(
            [
                'organisation' => $organisationId,
                'status' => $directDebitActive,
            ]
        );
    }

    /**
     * @param int $organisationId
     * @param int $directDebitId
     *
     * @return DirectDebit
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getByOrganisationAndId($organisationId, $directDebitId)
    {
        $directDebit = $this->findOneBy(
            [
                'organisation' => $organisationId,
                'id' => $directDebitId,
            ]
        );
        if (is_null($directDebit)) {
            throw new NotFoundException($this->getEntityName(), "$organisationId, $directDebitId");
        }

        return $directDebit;
    }
}
