<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Api\RegistrationModuleTest\Service;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Dvsa\Mot\Api\RegistrationModule\Service\BusinessRoleAssigner;
use DvsaCommon\Constants\Role;
use DvsaCommon\Enum\BusinessRoleName;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonSystemRoleMap;
use DvsaEntities\Repository\PersonSystemRoleRepository;

/**
 * Class BusinessRoleAssignerTest.
 */
class BusinessRoleAssignerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BusinessRoleAssigner
     */
    private $subject;

    public function setUp()
    {
        /* @var EntityManager $entityManager */
        $mockEntityManager = XMock::of(EntityManager::class);

        $mockConnection = XMock::of(Connection::class);
        $mockEntityManager->expects($this->any())
            ->method('getConnection')
            ->willReturn($mockConnection);

        /** @var PersonSystemRoleRepository $mockPersonSystemRoleRepository */
        $mockPersonSystemRoleRepository = XMock::of(PersonSystemRoleRepository::class);

        /** @var EntityRepository $mockEntityRepository */
        $mockEntityRepository = XMock::of(EntityRepository::class);

        $this->subject = new BusinessRoleAssigner(
            $mockEntityManager,
            $mockPersonSystemRoleRepository,
            $mockEntityRepository
        );
    }

    /**
     * @dataProvider dpPeopleAndRoles
     *
     * @param Person $person
     * @param string $role
     */
    public function testAssignRolesToPerson($person, $role, $expectException)
    {
        if ($expectException) {
            $this->setExpectedException(
                \OutOfRangeException::class,
                sprintf(BusinessRoleAssigner::EXP_NON_BUSINESS_ROLE, $role)
                );
        }

        $this->assertNull($person->getPersonSystemRoleMaps());

        $this->assertInstanceOf(
            PersonSystemRoleMap::class,
            $this->subject->assignRoleToPerson($person, $role)
        );

        $personRoleMaps = $person->getPersonSystemRoleMaps();

        $this->assertContainsOnly(PersonSystemRoleMap::class, $personRoleMaps);
        $this->assertCount(1, $personRoleMaps);
    }

    public function dpPeopleAndRoles()
    {
        return [
            [
                new Person(),
                BusinessRoleName::USER,
                false,
            ],
            [
                new Person(),
                Role::TESTER_ACTIVE,
                true,
            ],
        ];
    }
}
