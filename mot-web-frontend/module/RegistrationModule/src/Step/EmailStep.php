<?php

namespace Dvsa\Mot\Frontend\RegistrationModule\Step;

use DvsaCommon\InputFilter\Registration\EmailInputFilter;

class EmailStep extends AbstractRegistrationStep
{
    /**
     * const used for the session key, available via getId().
     */
    const STEP_ID = 'EMAIL';

    /**
     * @var string
     */
    private $emailAddress;

    /**
     * @var string
     */
    private $confirmEmailAddress;

    /**
     * @return string
     */
    public function getId()
    {
        return self::STEP_ID;
    }

    /**
     * Load the steps data from the session storage.
     *
     * @return array
     */
    public function load()
    {
        $values = $this->sessionService->load(self::STEP_ID);
        $this->readFromArray($values);

        return $this;
    }

    /**
     * @param array $values
     *
     * @return mixed
     */
    public function readFromArray(array $values)
    {
        if (is_array($values) && count($values)) {
            $this->setEmailAddress($values[EmailInputFilter::FIELD_EMAIL]);
            $this->setConfirmEmailAddress($values[EmailInputFilter::FIELD_EMAIL_CONFIRM]);

            $this->filter->setData($this->toArray());
        }
    }

    /**
     * Export the step values as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            EmailInputFilter::FIELD_EMAIL => $this->getEmailAddress(),
            EmailInputFilter::FIELD_EMAIL_CONFIRM => $this->getConfirmEmailAddress(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function getCleanFilterWhiteList()
    {
        return [
            EmailInputFilter::FIELD_EMAIL,
            EmailInputFilter::FIELD_EMAIL_CONFIRM,
        ];
    }

    /**
     * The route for this step.
     *
     * @return mixed
     */
    public function route()
    {
        return 'account-register/email';
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * @param string $emailAddress
     */
    public function setEmailAddress($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    /**
     * @return string
     */
    public function getConfirmEmailAddress()
    {
        return $this->confirmEmailAddress;
    }

    /**
     * @param string $confirmEmailAddress
     */
    public function setConfirmEmailAddress($confirmEmailAddress)
    {
        $this->confirmEmailAddress = $confirmEmailAddress;
    }
}
