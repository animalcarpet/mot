<?php

namespace DvsaCommonApiTest\Service;

use DvsaEntities\Mapper\AddressMapper;
use DvsaCommonApi\Service\AddressService;
use DvsaEntities\Entity\Address;
use DvsaCommonApi\Service\Validator\AddressValidator;

/**
 * Class AddressServiceTest.
 */
class AddressServiceTest extends AbstractServiceTestCase
{
    public function testGetAddressData()
    {
        $addressId = 1;
        $address = new Address();
        $expectedHydratorData = ['id' => 1];

        $mockHydrator = $this->getMockHydrator();
        $this->setupMockForSingleCall($mockHydrator, 'extract', $expectedHydratorData, $address);

        $mockEntityManager = $this->getMockEntityManager();
        $mockEntityManager->expects($this->once())
                          ->method('find')
                          ->with(Address::class, $addressId)
                          ->will($this->returnValue($address));
        $mockAddressMapper = $this->getMockBuilder(AddressMapper::class)->disableOriginalConstructor()->getMock();

        $addressService = new AddressService(
            $mockEntityManager,
            $mockHydrator,
            new AddressValidator(),
            $mockAddressMapper
        );
        $addressData = $addressService->getAddressData($addressId);

        $this->assertEquals($expectedHydratorData, $addressData);
    }

    /**
     * @expectedException        \DvsaCommonApi\Service\Exception\NotFoundException
     * @expectedExceptionMessage Address 666 not found
     */
    public function testGetAddressDataThrowsNotFoundExceptionForNullFind()
    {
        $mockHydrator = $this->getMockHydrator();

        $mockEntityManager = $this->getMockEntityManager();
        $mockEntityManager->expects($this->once())
                          ->method('find')
                          ->will($this->returnValue(null));
        $mockAddressMapper = $this->getMockBuilder(AddressMapper::class)->disableOriginalConstructor()->getMock();
        $addressService = new AddressService(
            $mockEntityManager,
            $mockHydrator,
            new AddressValidator(),
            $mockAddressMapper
        );
        $addressService->getAddressData(666);
    }

    public function testCreateAddressData()
    {
        $mockAddress = $this->getMockBuilder(Address::class)->disableOriginalConstructor()->getMock();

        $mockAddressValidator = $this->getMockBuilder(AddressValidator::class)->disableOriginalConstructor()->getMock();
        $mockHydrator = $this->getMockHydrator();

        $mockEntityManager = $this->getMockEntityManager();
        $mockEntityManager->expects($this->once())
            ->method('persist')
            ->with($mockAddress);

        $mockAddressMapper = $this->getMockBuilder(AddressMapper::class)->disableOriginalConstructor()->getMock();

        $this->setupMockForCalls($mockAddressMapper, 'mapToEntity', $mockAddress, null);

        $addressService = new AddressService(
            $mockEntityManager,
            $mockHydrator,
            $mockAddressValidator,
            $mockAddressMapper
        );

        $result = $addressService->persist(new Address(), ['town' => 't']);

        $this->assertEquals($mockAddress, $result);
    }
}
