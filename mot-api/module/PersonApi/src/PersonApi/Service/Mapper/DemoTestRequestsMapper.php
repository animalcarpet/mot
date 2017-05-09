<?php

namespace PersonApi\Service\Mapper;

use DvsaCommon\Dto\MotTesting\DemoTestRequestsDto;
use DvsaCommonApi\Service\Mapper\AbstractApiMapper;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class DemoTestRequestsMapper extends AbstractApiMapper implements AutoWireableInterface
{
    /**
     * @param $objects array[]
     *
     * @return DemoTestRequestsDto[]
     */
    public function manyToDto($objects)
    {
        return parent::manyToDto($objects);
    }

    /**
     * @param array $object
     *
     * @return DemoTestRequestsDto
     */
    public function toDto($object)
    {
        $dto = new DemoTestRequestsDto();

        $dto->setId($object['id'])
            ->setUsername($object['username'])
            ->setUserTelephoneNumber($object['number'])
            ->setUserEmail($object['email'])
            ->setUserFirstName($object['firstName'])
            ->setUserMiddleName($object['middleName'])
            ->setUserFamilyName($object['familyName'])
            ->setCertificateGroupCode($object['code'])
            ->setVtsNumber($object['siteNumber'])
            ->setVtsPostcode($object['postcode'])
            ->setCertificateDateAdded($object['createdOn']);

        return $dto;
    }
}
