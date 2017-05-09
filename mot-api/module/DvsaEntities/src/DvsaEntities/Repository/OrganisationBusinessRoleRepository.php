<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\OrganisationBusinessRole;

class OrganisationBusinessRoleRepository extends EntityRepository
{
    public function getByCode($code)
    {
        $role = $this->findOneBy(
            ['shortName' => $code]
        );

        if ($role === null) {
            throw new NotFoundException('Organisation business role');
        }

        return $role;
    }

    /**
     * @param $id
     *
     * @return OrganisationBusinessRole
     *
     * @throws NotFoundException
     */
    public function get($id)
    {
        $role = $this->find($id);

        if ($role === null) {
            throw new NotFoundException('Organisation business role');
        }

        return $role;
    }
}
