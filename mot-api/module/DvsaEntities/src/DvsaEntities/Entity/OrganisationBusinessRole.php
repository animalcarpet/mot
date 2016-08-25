<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * OrganisationBusinessRole
 *
 * @ORM\Table(
 *  name="organisation_business_role",
 *  indexes={
 *      @ORM\Index(name="fk_organisation_business_role_role", columns={"role_id"}),
 *      @ORM\Index(name="created_by", columns={"created_by"}),
 *      @ORM\Index(name="last_updated_by", columns={"last_updated_by"})
 *  }
 * )
 * @ORM\Entity(readOnly=true, repositoryClass="DvsaEntities\Repository\OrganisationBusinessRoleRepository")
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class OrganisationBusinessRole extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=250, nullable=false)
     */
    private $fullName;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5, nullable=false)
     */
    private $shortName;

    /**
     * @var \DvsaEntities\Entity\Role
     *
     * @ORM\ManyToOne(targetEntity="DvsaEntities\Entity\Role")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="role_id", referencedColumnName="id")
     * })
     */
    private $role;

    /**
     * Set name
     *
     * @param string $name
     * @return OrganisationBusinessRole
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     * @return OrganisationBusinessRole
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set shortName
     *
     * @param string $shortName
     * @return OrganisationBusinessRole
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Get shortName
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }

    public function getCode()
    {
        return $this->shortName;
    }

    /**
     * Set role
     *
     * @param \DvsaEntities\Entity\Role $role
     * @return OrganisationBusinessRole
     */
    public function setRole(\DvsaEntities\Entity\Role $role = null)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role
     *
     * @return \DvsaEntities\Entity\Role
     */
    public function getRole()
    {
        return $this->role;
    }
}
