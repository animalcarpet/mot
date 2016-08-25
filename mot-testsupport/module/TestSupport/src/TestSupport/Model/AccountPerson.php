<?php

namespace TestSupport\Model;


use DvsaCommon\Enum\LicenceCountryCode;
use DvsaCommon\Enum\PersonAuthType;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\DataGeneratorHelper;
use TestSupport\Helper\RequestorParserHelper;

class AccountPerson {
    private $firstName;
    private $middleName;
    private $surname;
    private $username;
    private $emailAddress;
    private $addressLine1;
    private $addressLine2;
    private $postcode;
    private $phoneNumber;
    private $dateOfBirth;
    private $accountClaimRequired;
    private $securityQuestionsRequired;
    private $drivingLicenceNumber;
    private $drivingLicenceRegion;
    private $authenticationMethod;

    private $creatorUsername;
    private $creatorPassword;

    /**
     * @param array               $data
     * @param DataGeneratorHelper $dataGeneratorHelper
     */
    public function __construct(array $data, DataGeneratorHelper $dataGeneratorHelper)
    {
        list($this->creatorUsername,  $this->creatorPassword) = RequestorParserHelper::parse($data);

        $this->addressLine1 = ArrayUtils::tryGet($data, 'addressLine1', $dataGeneratorHelper->addressLine1());
        $this->username = ArrayUtils::tryGet($data, 'username', $dataGeneratorHelper->username());
        $this->emailAddress = ArrayUtils::tryGet($data, 'emailAddress', $dataGeneratorHelper->emailAddress());
        $this->firstName = ArrayUtils::tryGet($data, 'firstName', $dataGeneratorHelper->firstName());
        $this->middleName= ArrayUtils::tryGet($data, 'middleName', $dataGeneratorHelper->middleName());
        $this->phoneNumber = ArrayUtils::tryGet($data, 'phoneNumber', $dataGeneratorHelper->phoneNumber());
        $this->surname = ArrayUtils::tryGet($data, 'surname', $dataGeneratorHelper->surname());
        $this->postcode = ArrayUtils::tryGet($data, 'postcode', 'BA1 5LR');
        $this->dateOfBirth = ArrayUtils::tryGet($data, 'dateOfBirth', '1980-01-01');
        $this->accountClaimRequired = ArrayUtils::tryGet($data, 'accountClaimRequired', false);
        $this->passwordChangeRequired = ArrayUtils::tryGet($data, 'passwordChangeRequired', false);
        $this->addressLine2 = ArrayUtils::tryGet($data, 'addressLine2', $dataGeneratorHelper->addressLine2());
        $this->securityQuestionsRequired = ArrayUtils::tryGet($data, 'securityQuestionsRequired', false);
        $this->authenticationMethod = ArrayUtils::tryGet($data, 'authenticationMethod', PersonAuthType::PIN);
        $this->drivingLicenceNumber = ArrayUtils::tryGet(
            $data,
            'drivingLicenceNumber',
            $dataGeneratorHelper->drivingLicenceNumber()
        );
        $this->drivingLicenceRegion = ArrayUtils::tryGet(
            $data,
            'drivingLicenceRegion',
            LicenceCountryCode::GREAT_BRITAIN_ENGLAND_SCOTLAND_AND_WALES
        );
    }

    public function getAddressLine1()
    {
        return $this->addressLine1;
    }

    public function getAddressLine2()
    {
        return $this->addressLine2;
    }

    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    public function getMiddleName()
    {
        return $this->middleName;
    }

    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getCreatorUsername()
    {
        return $this->creatorUsername;
    }

    public function getCreatorPassword()
    {
        return $this->creatorPassword;
    }

    public function getDrivingLicenceNumber()
    {
        return $this->drivingLicenceNumber;
    }

    public function getDrivingLicenceRegion()
    {
        return $this->drivingLicenceRegion;
    }

    /**
     * @return string
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return string
     */
    public function getDateOfBirth()
    {
        return $this->dateOfBirth;
    }

    public function getAuthenticationMethod()
    {
        return $this->authenticationMethod;
    }

    public function isAccountClaimRequired()
    {
        return $this->accountClaimRequired;
    }

    public function isPasswordChangeRequired()
    {
        return $this->passwordChangeRequired;
    }

    public function isSecurityQuestionsRequired()
    {
        return $this->securityQuestionsRequired;
    }
}
