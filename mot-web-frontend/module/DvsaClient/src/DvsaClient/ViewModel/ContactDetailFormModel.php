<?php

namespace DvsaClient\ViewModel;

use DvsaCommon\Dto\Contact\ContactDto;
use DvsaCommon\Enum\PhoneContactTypeCode;
use Zend\Stdlib\Parameters;

class ContactDetailFormModel extends AbstractFormModel
{
    /**
     * @var EmailFormModel
     */
    private $emailModel;
    /**
     * @var AddressFormModel
     */
    private $addressModel;
    /**
     * @var PhoneFormModel
     */
    private $phoneModel;
    /**
     * @var string|null
     */
    private $type;

    public function __construct($type)
    {
        $this->type = $type;

        $this->addressModel = new AddressFormModel();

        $this->emailModel = new EmailFormModel($type);
        $this->emailModel->setIsPrimary(true);

        $this->phoneModel = new PhoneFormModel();
        $this->phoneModel
            ->setIsPrimary(true)
            ->setType(PhoneContactTypeCode::BUSINESS);
    }

    /**
     * @param Parameters $postData
     *
     * @return $this
     */
    public function fromPost(Parameters $postData)
    {
        /** @var Parameters $contactData */
        $contactData = new Parameters($postData->get($this->type));

        if ($contactData->count() != 0) {
            $this->getEmailModel()->fromPost($contactData);
            $this->getAddressModel()->fromPost($contactData);
            $this->getPhoneModel()->fromPost($contactData);
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function fromDto(ContactDto $dto = null)
    {
        if ($dto instanceof ContactDto) {
            $this->getEmailModel()->fromDto($dto->getPrimaryEmail());
            $this->getAddressModel()->fromDto($dto->getAddress());
            $this->getPhoneModel()->fromDto($dto->getPrimaryPhone());
        }

        return $this;
    }

    /**
     * @return ContactDto
     */
    public function toDto(ContactDto $dto = null)
    {
        if (!$dto instanceof ContactDto) {
            $dto = new ContactDto();
        }

        $dto
            ->setType($this->type)
            ->setAddress($this->getAddressModel()->toDto())
            ->setEmails([$this->getEmailModel()->toDto()])
            ->setPhones([$this->getPhoneModel()->toDto()]);

        return $dto;
    }

    public function addErrorsFromApi($errors)
    {
        $this->getEmailModel()->addErrors($errors);
        $this->getAddressModel()->addErrors($errors);
        $this->getPhoneModel()->addErrors($errors);
    }

    /**
     * @return EmailFormModel
     */
    public function getEmailModel()
    {
        return $this->emailModel;
    }

    /**
     * @return $this
     */
    public function setEmailModel(EmailFormModel $emailModel)
    {
        $this->emailModel = $emailModel;

        return $this;
    }

    /**
     * @return AddressFormModel
     */
    public function getAddressModel()
    {
        return $this->addressModel;
    }

    /**
     * @return $this
     */
    public function setAddressModel(AddressFormModel $addressModel)
    {
        $this->addressModel = $addressModel;

        return $this;
    }

    /**
     * @return PhoneFormModel
     */
    public function getPhoneModel()
    {
        return $this->phoneModel;
    }

    /**
     * @return $this
     */
    public function setPhoneModel(PhoneFormModel $phoneModel)
    {
        $this->phoneModel = $phoneModel;

        return $this;
    }
}
