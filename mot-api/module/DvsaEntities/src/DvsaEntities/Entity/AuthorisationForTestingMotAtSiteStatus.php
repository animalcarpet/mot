<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * AuthorisationForTestingMotAtSiteStatus.
 *
 * @ORM\Table(name="auth_for_testing_mot_at_site_status")
 * @ORM\Entity(
 *  repositoryClass="DvsaEntities\Repository\AuthorisationForTestingMotAtSiteStatusRepository",
 * readOnly=true
 * )
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class AuthorisationForTestingMotAtSiteStatus extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'AuthorisationForTestingMotAtSiteStatus';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5, nullable=false)
     */
    private $code;

    public function __construct($code = null, $name = null)
    {
        $this->code = $code;
        $this->name = $name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return AuthorisationForTestingMotAtSiteStatus
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

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return AuthorisationForTestingMotAtSiteStatus
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
