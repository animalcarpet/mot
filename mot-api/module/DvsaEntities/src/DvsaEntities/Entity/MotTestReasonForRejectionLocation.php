<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * Class MotTestReasonForRejectionLocation
 *
 * @ORM\Entity(
 *     repositoryClass="\DvsaEntities\Repository\MotTestReasonForRejectionLocationRepository"
 * )
 * @ORM\Table(
 *     name="mot_test_rfr_location_type",
 *     options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 */
class MotTestReasonForRejectionLocation extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var string
     * @ORM\Column(name="lateral", type="string", length=50, nullable=true)
     */
    private $lateral;

    /**
     * @var string
     * @ORM\Column(name="longitudinal", type="string", length=50, nullable=true)
     */
    private $longitudinal;

    /**
     * @var string
     * @ORM\Column(name="vertical", type="string", length=50, nullable=true)
     */
    private $vertical;

    /**
     * @return string
     */
    public function getLateral()
    {
        return $this->lateral;
    }

    /**
     * @param string $lateral
     * @return MotTestReasonForRejectionLocation
     */
    public function setLateral($lateral)
    {
        $this->lateral = $lateral;
        return $this;
    }

    /**
     * @return string
     */
    public function getLongitudinal()
    {
        return $this->longitudinal;
    }

    /**
     * @param string $longitudinal
     * @return MotTestReasonForRejectionLocation
     */
    public function setLongitudinal($longitudinal)
    {
        $this->longitudinal = $longitudinal;
        return $this;
    }

    /**
     * @return string
     */
    public function getVertical()
    {
        return $this->vertical;
    }

    /**
     * @param string $vertical
     * @return MotTestReasonForRejectionLocation
     */
    public function setVertical($vertical)
    {
        $this->vertical = $vertical;
        return $this;
    }
}
