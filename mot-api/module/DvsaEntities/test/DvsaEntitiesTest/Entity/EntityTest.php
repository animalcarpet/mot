<?php

namespace DvsaEntityTest\Entity;

use DvsaEntities\Entity\Entity;
use DvsaEntities\Entity\Person;
use PHPUnit_Framework_TestCase;

/**
 * Test for base entity.
 */
class EntityTest extends PHPUnit_Framework_TestCase
{
    public function testBaseEntitySettersAndGetters()
    {
        $creatorIdStub = (new Person())->setId(999);
        $updaterIdStub = (new Person())->setId(632);
        $createDateStub = new \DateTime();
        $updateDateStub = (new \DateTime())->add(new \DateInterval('P1D'));
        $versionStub = 12345;

        $entity = $this->getEntityMock();
        $entity->setCreatedBy($creatorIdStub);
        $entity->setLastUpdatedBy($updaterIdStub);
        $entity->setCreatedOn($createDateStub);
        $entity->setLastUpdatedOn($updateDateStub);
        $entity->setVersion($versionStub);

        $this->assertEquals($creatorIdStub, $entity->getCreatedBy());
        $this->assertEquals($updaterIdStub, $entity->getLastUpdatedBy());
        $this->assertEquals($createDateStub, $entity->getCreatedOn());
        $this->assertEquals($updateDateStub, $entity->getLastUpdatedOn());
        $this->assertEquals($versionStub, $entity->getVersion());
    }

    public function testIsLastUpdatedBy()
    {
        $updaterId = (new Person())->setId(1234);
        $entity = $this->getEntityMock();
        $entity->setLastUpdatedBy($updaterId);

        $this->assertTrue($entity->isLastModifiedBy($updaterId));
    }

    /**
     * @return Entity
     */
    private function getEntityMock()
    {
        return $this->getMockForAbstractClass(\DvsaEntities\Entity\Entity::class);
    }
}
