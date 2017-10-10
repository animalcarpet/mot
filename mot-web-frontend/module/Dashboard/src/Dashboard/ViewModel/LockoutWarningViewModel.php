<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dashboard\ViewModel;

use Dashboard\Model\HelpDeskContact;

/**
 * Class LockoutWarningViewModel
 */

class LockoutWarningViewModel
{
    private $gaEventCode;

    private $heading;
    private $message;
    private $backLink;
    private $backLinkText;
    private $helpDeskContact;

    public function __construct(string $heading, string $message, string $backLink, string $backLinkText,
                                HelpDeskContact $helpDeskContact)
    {
        $this->heading = $heading;
        $this->message = $message;
        $this->backLink = $backLink;
        $this->backLinkText = $backLinkText;
        $this->helpDeskContact = $helpDeskContact;
    }

    /**
     * @param string $gaEventCode
     */
    public function setGaEventCode(string $gaEventCode)
    {
        $this->gaEventCode = $gaEventCode;
    }

    /**
     * @return string
     */
    public function getGaEventCode(): string
    {
        return $this->gaEventCode;
    }

    /**
     * @return string
     */
    public function getHeading(): string
    {
        return $this->heading;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getBackLink(): string
    {
        return $this->backLink;
    }

    /**
     * @return string
     */
    public function getBackLinkText(): string
    {
        return $this->backLinkText;
    }

    /**
     * @return HelpDeskContact
     */
    public function getHelpDeskContact(): HelpDeskContact
    {
        return $this->helpDeskContact;
    }
}
