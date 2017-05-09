<?php

namespace DvsaClient\Entity;

/**
 * Class Site.
 */
class Site
{
    private $name;
    private $contactDetails;

    /**
     * @param ContactDetail[] $contactDetails
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setContactDetails($contactDetails)
    {
        $this->contactDetails = $contactDetails;

        return $this;
    }

    /**
     * @return ContactDetail[]
     * @codeCoverageIgnore
     */
    public function getContactDetails()
    {
        return $this->contactDetails;
    }

    /**
     * @param string $name
     *
     * @return $this
     * @codeCoverageIgnore
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getName()
    {
        return $this->name;
    }
}
