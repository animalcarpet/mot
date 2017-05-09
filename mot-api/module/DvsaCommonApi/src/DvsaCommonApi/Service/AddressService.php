<?php

namespace DvsaCommonApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Mapper\AddressMapper;
use DvsaCommonApi\Service\Validator\AddressValidator;
use DvsaEntities\Entity\Address;
use Zend\Stdlib\Hydrator\AbstractHydrator;

/**
 * Service to handle Address entities.
 */
class AddressService extends AbstractService
{
    private $hydrator;
    private $validator;
    private $addressMapper;

    public function __construct(
        EntityManager $entityManager,
        AbstractHydrator $hydrator,
        AddressValidator $validator,
        AddressMapper $addressMapper
    ) {
        parent::__construct($entityManager);
        $this->hydrator = $hydrator;
        $this->validator = $validator;
        $this->addressMapper = $addressMapper;
    }

    public function persist(Address $address, array $data, $isNeedValidate = true)
    {
        if ((bool) $isNeedValidate) {
            $this->validator->validate($data);
        }

        $address = $this->addressMapper->mapToEntity($address, $data);

        $this->entityManager->persist($address);
        $this->entityManager->flush();

        return $address;
    }

    public function getAddressData($id)
    {
        $address = $this->entityManager->find(Address::class, $id);

        if (!$address) {
            throw new NotFoundException('Address', $id);
        }

        return $this->extractAddress($address);
    }

    protected function extractAddress($address)
    {
        return $this->hydrator->extract($address);
    }
}
