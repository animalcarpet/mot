<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Class AuthenticationMethod.
 *
 * Used to store & retrieve the method of authentication for MOT testers to complete
 * MOT tests.
 *
 * @ORM\Table(name="person_auth_type_lookup")
 * @ORM\Entity(repositoryClass="\DvsaEntities\Repository\AuthenticationMethodRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class AuthenticationMethod
{
    use CommonIdentityTrait;

    const PIN_CODE = 'PIN';

    const CARD_CODE = 'CARD';

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5, nullable=false)
     */
    private $code;

    public function __construct()
    {
    }

    /**
     * @param string $name
     *
     * @return AuthenticationMethod
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $code
     *
     * @return AuthenticationMethod
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return bool
     */
    public function isCard()
    {
        return self::CARD_CODE === $this->code;
    }

    /**
     * @return bool
     */
    public function isPin()
    {
        return self::PIN_CODE === $this->code;
    }
}
