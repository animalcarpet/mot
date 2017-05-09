<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * OrganisationPositionHistory.
 *
 * @ORM\Table(name="organisation_position_history")
 * @ORM\Entity(repositoryClass="DvsaEntities\Repository\OrganisationPositionHistoryRepository")
 */
class OrganisationPositionHistory extends Entity
{
    use CommonIdentityTrait;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\Person")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     * })
     */
    private $person;

    /**
     * @var int
     *
     * @ORM\Column(name="organisation_role_id", type="integer", nullable=false)
     */
    private $role;

    /**
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="\DvsaEntities\Entity\Organisation", inversedBy="positions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="organisation_id", referencedColumnName="id")
     * })
     */
    private $organisation;

    /**
     * @var int
     *
     * @ORM\Column(name="status_id", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="actioned_on", type="datetime", nullable=false)
     */
    private $actionedOn;

    /**
     * Constructs history item based on existing position.
     *
     * @param OrganisationBusinessRoleMap $organisationPosition
     * @param                             $status
     *
     * @return OrganisationPositionHistory
     */
    public static function fromOrganisationPosition(OrganisationBusinessRoleMap $organisationPosition, $status = null)
    {
        $historyItem = new self();
        $historyItem->person = $organisationPosition->getPerson();
        $historyItem->role = $organisationPosition->getOrganisationBusinessRole()->getId();
        $historyItem->organisation = $organisationPosition->getOrganisation();
        $historyItem->status = $status ?: $organisationPosition->getBusinessRoleStatus()->getId();
        $historyItem->actionedOn = new \DateTime();

        return $historyItem;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function getOrganisation()
    {
        return $this->organisation;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getActionedOn()
    {
        return $this->actionedOn;
    }
}
