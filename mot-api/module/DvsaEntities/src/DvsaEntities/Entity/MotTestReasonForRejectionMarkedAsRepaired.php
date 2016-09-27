<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * MotTestReasonForRejectionMarkedAsRepaired.
 *
 * @ORM\Table(name="mot_test_rfr_map_marked_as_repaired", options={"collate"="utf8_general_ci", "charset"="utf8",
 *      "engine"="InnoDB"})
 * @ORM\Entity
 */
class MotTestReasonForRejectionMarkedAsRepaired
{
    use CommonIdentityTrait;

    /**
     * @var MotTestReasonForRejection
     *
     * @ORM\ManyToOne(targetEntity="MotTestReasonForRejection", inversedBy="markedAsRepaired")
     * @ORM\JoinColumn(name="mot_test_rfr_map_id", referencedColumnName="id")
     */
    private $motTestRfr;

    /**
     * MotTestReasonForRejectionMarkedAsRepaired constructor.
     *
     * @param MotTestReasonForRejection $motTestRfr
     */
    public function __construct(MotTestReasonForRejection $motTestRfr)
    {
        $this->motTestRfr = $motTestRfr;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('MotTestReasonForRejectionMarkedAsRepaired {id: %s, motTestRfrId: %s}',
            $this->getId(), $this->motTestRfr->getId());
    }

    /**
     * @return MotTestReasonForRejection
     */
    public function getMotTestRfr()
    {
        return $this->motTestRfr;
    }
}
