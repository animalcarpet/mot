<?php

namespace Core\Presenter;

/**
 * Class MastheadPresenter
 */
class MastheadPresenter
{
    /**
     * URL for Feedback
     * @var string
     */
    const FEEDBACK_URL = 'http://www.smartsurvey.co.uk/s/MTSFeedback/';

    /**
     * @return string
     */
    public function getFeedbackUrl()
    {
        return self::FEEDBACK_URL;
    }
}
