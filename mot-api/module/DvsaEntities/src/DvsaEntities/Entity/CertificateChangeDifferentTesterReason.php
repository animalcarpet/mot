<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * CertificateChangeDifferentTesterReason.
 *
 * @ORM\Table(name="certificate_change_different_tester_reason_lookup",
 * indexes={@ORM\Index(name="fk_certificate_change_different_tester_reason_lookup_creator", columns={"created_by"}),
 * @ORM\Index(name="fk_certificate_change_different_tester_reason_lookup_editor", columns={"last_updated_by"})
 * })
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\CertificateChangeReasonRepository", readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class CertificateChangeDifferentTesterReason extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=4, nullable=false)
     */
    private $code;

    /**
     * @param string $code
     *
     * @return $this
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
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }
}
