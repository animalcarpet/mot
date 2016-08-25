<?php

namespace OrganisationApi\Model\Operation;

use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;

interface NominateOperationInterface
{
    public function nominate(Person $nominator, OrganisationBusinessRoleMap $nomination);

    public function updateNomination(Person $nominator, OrganisationBusinessRoleMap $nomination);
}
