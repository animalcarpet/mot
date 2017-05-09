<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * NotificationActionLookup.
 *
 * @ORM\Table(name="notification_action_lookup", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class NotificationActionLookup extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Notification Action Lookup';

    /** values from database `notification_action_lookup` */
    const SITE_NOMINATION_ACCEPTED = 1;
    const SITE_NOMINATION_REJECTED = 2;
    const ORGANISATION_NOMINATION_ACCEPTED = 3;
    const ORGANISATION_NOMINATION_REJECTED = 4;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=30, nullable=false)
     */
    private $action;

    /**
     * @param string $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }
}
