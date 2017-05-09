<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * PermissionToAssignRoleMap.
 *
 * @ORM\Table(
 *  name="permission_to_assign_role_map",
 *  indexes={
 *      @ORM\Index(name="created_by", columns={"created_by"}),
 *      @ORM\Index(name="last_updated_by", columns={"last_updated_by"}),
 *      @ORM\Index(name="uq_permission_to_assign_role_map", columns={"permission_id, role_id"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\PermissionToAssignRoleMapRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class PermissionToAssignRoleMap extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var Permission
     *
     * @ORM\OneToOne(targetEntity="Permission")
     * @ORM\JoinColumn(name="permission_id", referencedColumnName="id")
     */
    private $permission;

    /**
     * @var Role
     *
     * @ORM\OneToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     */
    private $role;

    /**
     * Get the associated permission entity.
     *
     * @return Permission
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Get the associated role entity.
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }
}
