<?php

namespace DvsaEntities\Repository;

/**
 * Class OrganisationRepository.
 *
 * @codeCoverageIgnore
 */
class ReasonForRejectionTypeRepository
    extends AbstractMutableRepository
    implements ReasonForRejectionTypeRepositoryInterface
{
    public function getByType($rfrType)
    {
        return $this->findOneBy(['reasonForRejectionType' => $rfrType]);
    }
}