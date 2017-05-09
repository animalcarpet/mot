<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class MotTestReasonForRejectionDescription.
 *
 * @ORM\Entity
 * @ORM\Table(
 *     name="mot_test_rfr_map_custom_description",
 *     options={"collate"="utf8_general_ci", "charset"="utf8", "engine"="InnoDB"}
 * )
 */
class MotTestReasonForRejectionDescription extends Entity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="custom_description", type="string", length=100)
     */
    private $customDescription;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return MotTestReasonForRejectionComment
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getCustomDescription()
    {
        return $this->customDescription;
    }

    /**
     * @param string $customDescription
     *
     * @return MotTestReasonForRejectionDescription
     */
    public function setCustomDescription($customDescription)
    {
        $this->customDescription = $customDescription;

        return $this;
    }
}
