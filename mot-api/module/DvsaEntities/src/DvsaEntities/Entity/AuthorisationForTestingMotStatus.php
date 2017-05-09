<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;
use DvsaEntities\EntityTrait\EnumType1EntityTrait;

/**
 * AuthorisationForTestingMotStatus.
 *
 * @ORM\Table(name="auth_for_testing_mot_status")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\AuthorisationForTestingMotStatusRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class AuthorisationForTestingMotStatus extends Entity
{
    use CommonIdentityTrait;

    use EnumType1EntityTrait;

    const ENTITY_NAME = 'AuthorisationForTestingMotStatus';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return AuthorisationForTestingMotStatus
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
