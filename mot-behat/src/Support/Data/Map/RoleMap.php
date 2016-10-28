<?php

namespace Dvsa\Mot\Behat\Support\Data\Map;

use Dvsa\Mot\Behat\Support\Data\Collection\DataCollection;
use DvsaCommon\Dto\Security\RoleDto;
use DvsaCommon\Enum\RoleCode;

class RoleMap
{
    private $collection;

    public function __construct()
    {
        $this->collection = new DataCollection(RoleDto::class);
        $this->collection->add(
            $this->createRoleDto("Authorised examiner designated manager", RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER, 1),
            RoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER
        );
        $this->collection->add(
            $this->createRoleDto("Authorised examiner delegate", RoleCode::AUTHORISED_EXAMINER_DELEGATE, 2),
            RoleCode::AUTHORISED_EXAMINER_DELEGATE
        );
    }

    /**
     * @param $code
     * @return RoleDto
     */
    public function get($code)
    {
        return $this->collection->get($code);
    }

    public function getByName($name)
    {
        $role = $this->collection->filter(function (RoleDto $role) use ($name){
            return $role->getName() === $name;
        });

        $this->validate($role);

        return $role->first();
    }

    public function getById($id)
    {
        $role = $this->collection->filter(function (RoleDto $role) use ($id){
            return $role->getId() === $id;
        });

        $this->validate($role);

        $role->first();
    }

    private function validate(DataCollection $collection)
    {
        if ($collection->count() === 0) {
            throw new \InvalidArgumentException("Role not found");
        }
    }

    private function createRoleDto($name, $code, $id)
    {
        $dto = new RoleDto();
        $dto
            ->setName($name)
            ->setCode($code)
            ->setId($id);

        return $dto;
    }
}
