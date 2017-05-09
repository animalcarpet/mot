<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\Application;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\Person;
use PHPUnit_Framework_TestCase;

/**
 * Class ApplicationTest.
 */
class ApplicationTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $application = new Application();

        $this->assertNull($application->getId());
        $this->assertNull($application->getApplicationReference());
        $this->assertNull($application->getLockedOn());
        $this->assertNull($application->getSubmittedOn());
        $this->assertNull($application->getStatus());
        $this->assertNull($application->getLockedBy());
        $this->assertNull($application->getPerson());
    }

    public function testApplicationReturnsSet()
    {
        $application = new Application();
        $person = new Person();
        $status = new AuthForAeStatus();

        $data = [
            'applicationReference' => 'ABC123',
            'submittedOn' => new \DateTime(),
            'createdBy' => $person,
            'createdOn' => new \DateTime(),
            'lastUpdatedBy' => $person,
            'lastUpdatedOn' => new \DateTime(),
            'status' => $status,
            'lockedBy' => $person,
            'lockedOn' => new \DateTime(),
            'person' => $person,
            'version' => '1',
        ];

        $application->setApplicationReference($data['applicationReference'])
            ->setLockedOn($data['lockedOn'])
            ->setSubmittedOn($data['submittedOn'])
            ->setStatus($data['status'])
            ->setLockedBy($data['lockedBy'])
            ->setLockedOn($data['lockedOn'])
            ->setPerson($data['person']);

        $this->assertEquals($data['applicationReference'], $application->getApplicationReference());
        $this->assertEquals($data['lockedOn'], $application->getLockedOn());
        $this->assertEquals($data['submittedOn'], $application->getSubmittedOn());
        $this->assertEquals($data['status'], $application->getStatus());
        $this->assertEquals($data['lockedBy'], $application->getLockedBy());
        $this->assertEquals($data['lockedOn'], $application->getLockedOn());
        $this->assertEquals($data['person'], $application->getPerson());
    }
}
