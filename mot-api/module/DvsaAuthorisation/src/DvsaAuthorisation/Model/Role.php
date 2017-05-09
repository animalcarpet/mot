<?php

namespace DvsaAuthorisation\Model;

/**
 * Class Role.
 */
class Role
{
    protected $name;

    protected $permissions = [];

    public function __construct($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param array $permissions
     */
    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    public function addPermission($permission)
    {
        $this->permissions[] = $permission;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->getName();
    }
}
