<?php

namespace DvsaClient\ViewModel;

use DvsaCommon\Dto\Contact\PhoneDto;
use Zend\Stdlib\Parameters;

class PhoneFormModel extends AbstractFormModel
{
    const FIELD_CONTACT = '%s[%s]';
    const FIELD_NUMBER = 'phoneNumber';

    const ERR_REQUIRE = 'A telephone number must be entered';

    private $number;
    private $isPrimary;
    private $type;

    /**
     * @return $this
     */
    public function fromPost(Parameters $postData)
    {
        $this->setNumber($postData->get(self::FIELD_NUMBER));

        return $this;
    }

    /**
     * @return PhoneDto
     */
    public function toDto()
    {
        return (new PhoneDto())
            ->setNumber($this->getNumber())
            ->setIsPrimary($this->isPrimary())
            ->setContactType($this->getType());
    }

    /**
     * @return $this
     */
    public function fromDto(PhoneDto $dto = null)
    {
        if ($dto instanceof PhoneDto) {
            $this
                ->setNumber($dto->getNumber())
                ->setIsPrimary($dto->isPrimary())
                ->setType($dto->getContactType());
        }

        return $this;
    }

    public function isValid($type = null)
    {
        $this->resetErrors();
        $field = $type ? sprintf(self::FIELD_CONTACT, $type, self::FIELD_NUMBER) : self::FIELD_NUMBER;

        if (empty($this->getNumber())) {
            $this->addError($field, self::ERR_REQUIRE);
        }

        return !$this->hasErrors();
    }

    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @return $this
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    public function isPrimary()
    {
        return $this->isPrimary;
    }

    /**
     * @return $this
     */
    public function setIsPrimary($isPrimary)
    {
        $this->isPrimary = (bool) $isPrimary;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
}
