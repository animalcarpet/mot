<?php

namespace DvsaEntities\Repository;

use Doctrine\ORM\EntityRepository;
use DvsaEntities\Entity\PersonSystemRole;
use DvsaCommonApi\Service\Exception\NotFoundException;

/**
 * Repository for {@link \DvsaEntities\Entity\PersonSystemRole}.
 */
class PersonSystemRoleRepository extends EntityRepository
{
    /**
     * Gets person system role by id.
     *
     * @param int $id
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return PersonSystemRole
     */
    public function get($id)
    {
        $personSystemRole = $this->find($id);
        if (null === $personSystemRole) {
            $this->notFound($id);
        }

        return $personSystemRole;
    }

    /**
     * @param mixed $id
     * @param null  $lockMode
     * @param null  $lockVersion
     *
     * @return PersonSystemRole
     */
    public function find($id, $lockMode = null, $lockVersion = null)
    {
        return parent::find($id, $lockMode, $lockVersion);
    }

    /**
     * Gets person system role by name.
     *
     * @param string $name
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     *
     * @return PersonSystemRole
     */
    public function getByName($name)
    {
        $personSystemRole = $this->findOneBy(['name' => $name]);
        if (null === $personSystemRole) {
            $this->notFound($name);
        }

        return $personSystemRole;
    }

    /**
     * @param $params
     */
    protected function getSqlBuilder($params)
    {
        // @todo What should this be?
    }

    /**
     * @param mixed $param
     *
     * @throws NotFoundException
     */
    private function notFound($param)
    {
        throw new NotFoundException(sprintf('PersonSystemRole %s not found', $param));
    }
}
