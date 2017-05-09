<?php

namespace DvsaClient\Entity;

use DvsaCommon\Enum\PhoneContactTypeCode;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class ContactDetail.
 */
class ContactDetail
{
    private $address;

    /**
     * @var Phone[]
     */
    private $phones = [];
    /**
     * @var Email[]
     */
    private $emails = [];
    private $faxNumber;
    private $type;

    /**
     * @param string $address
     *
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param \DvsaClient\Entity\Email[] $emails
     *
     * @return $this
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * @return \DvsaClient\Entity\Email[]
     */
    public function getEmails()
    {
        return $this->emails;
    }

    /**
     * @param string $faxNumber
     *
     * @return $this
     */
    public function setFaxNumber($faxNumber)
    {
        $this->faxNumber = $faxNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getFaxNumber()
    {
        return $this->faxNumber;
    }

    /**
     * @param \DvsaClient\Entity\Phone[] $phones * @return $this
     *
     * @return ContactDetail
     */
    public function setPhones($phones)
    {
        $this->phones = $phones;

        return $this;
    }

    /**
     * @return \DvsaClient\Entity\Phone[]
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return Phone
     */
    private function getPrimaryPhone()
    {
        return ArrayUtils::firstOrNull(
            $this->phones,
            function (Phone $phone) {
                return $phone->getIsPrimary()
                    && $phone->getContactType() !== PhoneContactTypeCode::FAX;
            }
        );
    }

    /**
     * @return string
     */
    public function getPrimaryPhoneNumber()
    {
        $primaryPhone = $this->getPrimaryPhone();

        return $primaryPhone ? $primaryPhone->getNumber() : '';
    }

    /**
     * @return Email
     */
    private function getPrimaryEmail()
    {
        return ArrayUtils::firstOrNull(
            $this->emails,
            function (Email $email) {
                return $email->getIsPrimary();
            }
        );
    }

    /**
     * @return string
     */
    public function getPrimaryEmailAddress()
    {
        $primaryEmail = $this->getPrimaryEmail();

        return $primaryEmail ? $primaryEmail->getEmail() : '';
    }

    /**
     * @return Phone
     */
    private function getPrimaryFax()
    {
        return ArrayUtils::firstOrNull(
            $this->phones,
            function (Phone $fax) {
                return $fax->getIsPrimary() && $fax->getContactType() === PhoneContactTypeCode::FAX;
            }
        );
    }

    /**
     * @return string
     */
    public function getPrimaryFaxNumber()
    {
        $primaryFax = $this->getPrimaryFax();

        return $primaryFax ? $primaryFax->getNumber() : '';
    }
}
