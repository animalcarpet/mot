<?php

namespace Dashboard\Model;

use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Utility\TypeCheck;

/**
 * Data model for Notification
 */
class Notification
{
    const FRIENDLY_ACTION_REJECTED = 'rejected';
    const FRIENDLY_ACTION_CONFIRMED = 'accepted';

    /** @var $id int */
    private $id;
    /** @var $content string */
    private $content;
    /** @var $subject string */
    private $subject;
    /** @var $readOn string */
    private $readOn;
    /** @var $createdOn string */
    private $createdOn;
    /** @var $updatedOn string */
    private $updatedOn;
    /** @var $actions array */
    private $actions;
    /** @var $action string */
    private $action;
    /** @var $fields array */
    private $fields;
    /** @var $templateId int */
    private $templateId;
    /** @var $isArchived bool */
    private $isArchived;

    public function __construct($data)
    {
        TypeCheck::assertArray($data);

        $this
            ->setId(ArrayUtils::get($data, 'id'))
            ->setCreatedOn(ArrayUtils::get($data, 'createdOn'))
            ->setContent(ArrayUtils::get($data, 'content'))
            ->setSubject(ArrayUtils::get($data, 'subject'))
            ->setUpdatedOn(ArrayUtils::get($data, 'updatedOn'))
            ->setFields(ArrayUtils::get($data, 'fields'))
            ->setIsArchived(ArrayUtils::get($data, 'isArchived'))
            ->setTemplateId(ArrayUtils::get($data, 'templateId'));

        if (!empty($data['readOn'])) {
            $this->setReadOn(ArrayUtils::get($data, 'readOn'));
        }

        if (array_key_exists('actions', $data)) {
            TypeCheck::assertArray($data['actions']);
            $this->setActions($data['actions']);
            $this->setAction(ArrayUtils::get($data, 'action'));
        }
    }

    /**
     * Returns list of Notification objects
     *
     * @param array $notifications
     *
     * @return Notification[]
     */
    public static function createList(array $notifications)
    {
        $result = [];

        foreach ($notifications as $item) {
            $result[] = new Notification($item);
        }

        return $result;
    }

    /**
     * Notification requires action (is nomination?)
     *
     * @return bool
     */
    public function isActionRequired()
    {
        return count($this->getActions()) > 0;
    }

    /**
     * User has already taken an action
     *
     * @return bool
     */
    public function isDone()
    {
        return ($this->getAction() !== null);
    }

    /**
     * @param string $subject
     *
     * @return Notification
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $content
     *
     * @return Notification
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $createdOn
     *
     * @return Notification
     */
    public function setCreatedOn($createdOn)
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @param string $updatedOn
     *
     * @return Notification
     */
    public function setUpdatedOn($updatedOn)
    {
        $this->updatedOn = $updatedOn;

        return $this;
    }

    /**
     * @return string
     */
    public function getUpdatedOn()
    {
        return $this->updatedOn;
    }

    /**
     * @param mixed $id
     *
     * @return Notification
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param array $fields
     *
     * @return Notification
     */
    public function setFields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if (isset($this->fields['siteName'])) {
            return $this->fields['siteName'];
        }

        if (isset($this->fields['organisationName'])) {
            return $this->fields['organisationName'];
        }

        return '';
    }

    /**
     * @param string $readOn
     *
     * @return Notification
     */
    public function setReadOn($readOn)
    {
        $this->readOn = $readOn;

        return $this;
    }

    /**
     * @return string
     */
    public function getReadOn()
    {
        return $this->readOn;
    }

    /**
     * @param string $action
     *
     * @return Notification
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

    /**
     * Returns human-friendly string
     */
    public function getFriendlyAction()
    {
        if ($this->isRejectedAction()) {
            return self::FRIENDLY_ACTION_REJECTED;
        }

        return self::FRIENDLY_ACTION_CONFIRMED;
    }

    /**
     * Returns if action is confirmed
     */
    public function isConfirmedAction()
    {
        return !$this->isRejectedAction();
    }

    /**
     * Returns if action is rejected
     * @return bool
     */
    private function isRejectedAction()
    {
        return (strrpos($this->action, 'REJECTED') > 0);
    }

    /**
     * @param array $actions
     *
     * @return Notification
     */
    public function setActions($actions)
    {
        $this->actions = $actions;

        return $this;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @param int $id
     * @return Notification
     */
    public function setTemplateId($id)
    {
        $this->templateId = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @return boolean
     */
    public function getIsArchived()
    {
        return $this->isArchived;
    }

    /**
     * @param boolean $isArchived
     * @return Notification
     */
    public function setIsArchived($isArchived)
    {
        $this->isArchived = $isArchived;
        return $this;
    }
}
