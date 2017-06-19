<?php

namespace NotificationApi\Dto;

/**
 * Data transfer object for Notification.
 */
class Notification
{
    /**
     * values from database `notification_template`
     * These should be replaced with enums generated from codes _not_ use hardcoded ids.
     */
    const TEMPLATE_TESTER_APPLICATION_APPROVED = 1;
    const TEMPLATE_TESTER_APPLICATION_REJECTED = 2;
    const TEMPLATE_TESTER_INITIAL_TRAINING_PASSED = 3;
    const TEMPLATE_TESTER_INITIAL_TRAINING_FAILED = 4;
    const TEMPLATE_SITE_NOMINATION = 5;
    const TEMPLATE_SITE_NOMINATION_DECISION = 6;
    const TEMPLATE_ORGANISATION_NOMINATION = 7;
    const TEMPLATE_ORGANISATION_NOMINATION_DECISION = 8;
    const TEMPLATE_ORGANISATION_NOMINATION_GIVEN = 9;
    const TEMPLATE_ORGANISATION_POSITION_REMOVED = 10;
    const TEMPLATE_SITE_POSITION_REMOVED = 11;
    const TEMPLATE_TESTING_OUTSIDE_OPENING_HOURS = 12;
    const TEMPLATE_MOT_TEST_STATUS_CHANGED_BY_ANOTHER_USER = 13;
    const TEMPLATE_TESTER_QUALIFICATION_STATUS = 14;
    const TEMPLATE_DVSA_USER_LINK_SITE_TO_AE = 19;
    const TEMPLATE_AE_UNLINK_SITE = 18;
    const TEMPLATE_DVSA_ASSIGN_ROLE = 16;
    const TEMPLATE_DVSA_REMOVE_ROLE = 17;
    const TEMPLATE_TESTER_STATUS_CHANGE = 20;
    const TEMPLATE_TESTER_STATUS_CHANGE_NEW = 22;
    const TEMPLATE_PASSWORD_EXPIRY = 24;
    const TEMPLATE_USER_REMOVED_OWN_ROLE = 25;
    const TEMPLATE_PERSONAL_DETAILS_CHANGED = 26;
    const TEMPLATE_MOT_TESTING_CERTIFICATE_REMOVAL = 27;
    const TEMPLATE_MOT_TESTING_CERTIFICATE_CREATED = 28;
    const TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED = 29;
    const TEMPLATE_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED = 30;
    const TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ORDERED = 31;
    const TEMPLATE_DIRECT_NOMINATION_BLOCKED_UNTIL_CARD_ACTIVATED = 32;
    const TEMPLATE_ORDER_CARD_SUCCESS_NOTIFICATION = 33;
    const TEMPLATE_SITE_ASSESSMENT_CREATED = 34;

    /** @var $template int */
    private $template;

    /** @var $recipient int */
    private $recipient;

    /** @var $fields array */
    private $fields;

    public function __construct()
    {
        $this->fields = [];
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'template' => $this->getTemplate(),
            'recipient' => $this->getRecipient(),
            'fields' => $this->getFields(),
        ];
    }

    /**
     * @param string $key
     * @param string $value
     *
     * @return Notification
     */
    public function addField($key, $value)
    {
        $this->fields[$key] = $value;

        return $this;
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
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param mixed $recipient
     *
     * @return $this
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param mixed $template
     *
     * @return Notification
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
