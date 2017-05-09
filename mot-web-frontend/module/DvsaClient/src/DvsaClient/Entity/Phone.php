<?php

namespace DvsaClient\Entity;

/**
 * Class Phone.
 *
 * TODO duplicates \DvsaCommon\Dto\Contact\PhoneDto, remove
 */
class Phone
{
    private $id;
    private $isPrimary;
    private $number;
    private $contactType;

    /**
     * @param string $contactType
     *
     * @return $this
     */
    public function setContactType($contactType)
    {
        $this->contactType = $contactType;

        return $this;
    }

    /**
     * @return string
     */
    public function getContactType()
    {
        return $this->contactType;
    }

    /**
     * @param string $id
     *
     * @return $this
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
     * @param string $isPrimary
     *
     * @return $this
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = $isPrimary;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * @param string $number
     *
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }
}
