<?php

namespace DvsaClient\Mapper;

use DvsaClient\Entity\ContactDetail;
use DvsaCommon\Utility\ArrayUtils;

/**
 * Class ContactDetailMapper.
 */
class ContactDetailMapper extends Mapper
{
    protected $entityClass = ContactDetail::class;

    /**
     * @param array         $array
     * @param ContactDetail $obj
     * @param array         $params
     *
     * @return ContactDetail
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function hydrateNestedEntities($array, $obj, $params)
    {
        $addressMapper = new AddressMapper($this->client);

        $address = ArrayUtils::tryGet($array, 'address');
        if (!empty($address)) {
            $address = $addressMapper->doHydration($address);
            $obj->setAddress($address);
        }

        $phonesData = $array['phones'];
        $phoneMapper = new PhoneMapper($this->client);
        $phones = $phoneMapper->hydrateArray($phonesData);

        $obj->setPhones($phones);

        $emailsData = $array['emails'];
        $emailMapper = new EmailMapper($this->client);
        $emails = $emailMapper->hydrateArray($emailsData);
        $obj->setEmails($emails);

        return $obj;
    }
}
