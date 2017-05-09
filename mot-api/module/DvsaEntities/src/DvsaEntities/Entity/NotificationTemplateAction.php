<?php

namespace DvsaEntities\Entity;

use Doctrine\ORM\Mapping as ORM;
use DvsaEntities\EntityTrait\CommonIdentityTrait;

/**
 * NotificationTemplateAction.
 *
 * @ORM\Table(name="notification_template_action", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_notification_template_action_1", columns={"notification_template_id"}), @ORM\Index(name="fk_notification_template_action_2", columns={"action_id"})})
 * @ORM\Entity(readOnly=true)
 * @ORM\Cache(usage="READ_ONLY", region="staticdata")
 */
class NotificationTemplateAction extends Entity
{
    use CommonIdentityTrait;

    const ENTITY_NAME = 'Notification Template Action';

    /**
     * @var NotificationTemplate
     *
     * @ORM\ManyToOne(targetEntity="NotificationTemplate", inversedBy="actions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="notification_template_id", referencedColumnName="id")
     * })
     */
    private $template;

    /**
     * @var NotificationActionLookup
     *
     * @ORM\ManyToOne(targetEntity="NotificationActionLookup")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="action_id", referencedColumnName="id")
     * })
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=100, nullable=true)
     */
    private $label;

    /**
     * @param \DvsaEntities\Entity\NotificationActionLookup $action
     *
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\NotificationActionLookup
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param \DvsaEntities\Entity\NotificationTemplate $template
     *
     * @return $this
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return \DvsaEntities\Entity\NotificationTemplate
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
