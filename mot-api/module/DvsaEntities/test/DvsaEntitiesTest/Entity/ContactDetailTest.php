<?php

namespace DvsaEntitiesTest\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Phone;

/**
 * Unit test for ContactDetail entity.
 */
class ContactDetailTest extends \PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $entity = new ContactDetail();

        $this->assertInstanceOf(ArrayCollection::class, $entity->getEmails());
        $this->assertInstanceOf(ArrayCollection::class, $entity->getPhones());
        $this->assertNull($entity->getId());
        $this->assertNull($entity->getAddress());
        $this->assertNull($entity->getForAttentionOf());
        $this->assertNull($entity->getPrimaryEmail());
        $this->assertNull($entity->getPrimaryPhone());
    }

    public function testGetSet()
    {
        $entity = new ContactDetail();

        $entity->setAddress($address = new Address());
        $this->assertSame($address, $entity->getAddress());

        $entity->setForAttentionOf($value = 'test attention for');
        $this->assertSame($value, $entity->getForAttentionOf());

        $entity->setId($value = 9999);
        $this->assertSame($value, $entity->getId());
    }

    public function testAddRemovePhone()
    {
        $detailsEntity = new ContactDetail();

        $entity = new Phone();
        $entity->setIsPrimary(true);

        $detailsEntity->addPhone($entity);
        $this->assertTrue($detailsEntity->getPhones()->contains($entity));

        $this->assertSame($entity, $detailsEntity->getPrimaryPhone());

        $detailsEntity->removePhone($entity);
        $this->assertFalse($detailsEntity->getPhones()->contains($entity));
        $this->assertEmpty($detailsEntity->getPhones());
    }

    public function testAddRemoveEmail()
    {
        $detailsEntity = new ContactDetail();

        $entity = new Email();
        $entity->setIsPrimary(true);

        $detailsEntity->addEmail($entity);
        $this->assertTrue($detailsEntity->getEmails()->contains($entity));

        $this->assertSame($entity, $detailsEntity->getPrimaryEmail());

        $detailsEntity->removeEmail($entity);
        $this->assertFalse($detailsEntity->getEmails()->contains($entity));
        $this->assertEmpty($detailsEntity->getEmails());
    }
}
