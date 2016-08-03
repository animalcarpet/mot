<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModule\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use DvsaCommon\InputFilter\Registration\DetailsInputFilter;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\PersonContactType;
use DvsaEntities\Entity\PhoneContactType;

/**
 * Class ContactDetailCreatorTest.
 */
class ContactDetailsCreatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContactDetailsCreator
     */
    private $subject;

    public function setUp()
    {
        $personContactTypeRepository = XMock::of(EntityRepository::class);
        $personContactTypeRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(
                new PersonContactType()
            );

        $phoneContactTypeRepository = XMock::of(EntityRepository::class);
        $phoneContactTypeRepository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(
                new PhoneContactType()
            );

        /** @var EntityManager $mockEntityManager */
        $mockEntityManager = XMock::of(EntityManager::class);
        $mockEntityManager->expects($this->at(0))
            ->method('getRepository')
            ->with($this->equalTo(PersonContactType::class))
            ->willReturn($personContactTypeRepository);

        $mockEntityManager->expects($this->at(1))
            ->method('getRepository')
            ->with($this->equalTo(PhoneContactType::class))
            ->willReturn($phoneContactTypeRepository);

        $mockConnection = XMock::of(Connection::class);
        $mockEntityManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($mockConnection);

        $this->subject = new ContactDetailsCreator(
            $mockEntityManager,
            $mockEntityManager->getRepository(PersonContactType::class),
            $mockEntityManager->getRepository(PhoneContactType::class)
        );
    }

    /**
     * @dataProvider dpStepDetailsAndAddress
     *
     * @param array $data
     */
    public function testCreate($data)
    {
        $this->assertInstanceOf(
            PersonContact::class,
            $this->subject->create(new Person(), $data)
        );
    }

    public function dpStepDetailsAndAddress()
    {
        return [
            [
                [
                    ValidatorKeyConverter::inputFilterToStep(DetailsInputFilter::class) => [
                        DetailsInputFilter::FIELD_EMAIL => 'x',
                        DetailsInputFilter::FIELD_PHONE => '1',
                    ],
                    ValidatorKeyConverter::inputFilterToStep(AddressInputFilter::class) => [
                        AddressInputFilter::FIELD_ADDRESS_1    => 'a1',
                        AddressInputFilter::FIELD_ADDRESS_2    => 'a2',
                        AddressInputFilter::FIELD_ADDRESS_3    => 'a3',
                        AddressInputFilter::FIELD_TOWN_OR_CITY => 'TC',
                        AddressInputFilter::FIELD_POSTCODE     => 'PC',
                    ],
                ],
            ],
        ];
    }
}
