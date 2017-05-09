<?php

namespace DvsaEntitiesTest\Entity;

use DvsaEntities\Entity\AuthorisedExaminerDesignatedManager;
use PHPUnit_Framework_TestCase;

/**
 * Class AuthorisedExaminerDesignatedManagerTest.
 */
class AuthorisedExaminerDesignatedManagerTest extends PHPUnit_Framework_TestCase
{
    public function testInitialState()
    {
        $authorisedExaminer = new AuthorisedExaminerDesignatedManager();

        $this->assertNull($authorisedExaminer->getId(), '"id" should initially be null');
        $this->assertNull(
            $authorisedExaminer->getUserIdentificationNumber(),
            '"userIdentificationNumber" should initially be null'
        );
        $this->assertNull(
            $authorisedExaminer->getMotSchemeTrainedPerson(),
            '"motSchemeTrainedPerson" should initially be null'
        );
        $this->assertNull(
            $authorisedExaminer->getMotManagersCourse(),
            '"motManagersCourse" should initially be null'
        );
        $this->assertNull($authorisedExaminer->getAnotherRole(), '"anotherRole" should initially be null');
        $this->assertNull($authorisedExaminer->getContactDetails(), '"organisationId" should initially be null');
        $this->assertNull(
            $authorisedExaminer->getIsAuthorisedExaminerPrincipal(),
            '"isAuthorisedExaminerPrincipal" should initially be null'
        );
    }

    public function testSetsPropertiesCorrectly()
    {
        $authorisedExaminerDesignatedManager = new AuthorisedExaminerDesignatedManager();

        $userIdentificationNumber = 1;
        $contactDetailsId = 2;
        $motSchemeTrainedPerson = false;
        $motManagersCourse = true;
        $anotherRole = true;
        $isAuthorisedExaminerPrincipal = false;

        $authorisedExaminerDesignatedManager
            ->setIsAuthorisedExaminerPrincipal($isAuthorisedExaminerPrincipal)
            ->setAnotherRole($anotherRole)
            ->setMotManagersCourse($motManagersCourse)
            ->setUserIdentificationNumber($userIdentificationNumber)
            ->setMotSchemeTrainedPerson($motSchemeTrainedPerson)
            ->setContactDetails($contactDetailsId);

        $this->assertEquals($anotherRole, $authorisedExaminerDesignatedManager->getAnotherRole());
        $this->assertEquals($motManagersCourse, $authorisedExaminerDesignatedManager->getMotManagersCourse());
        $this->assertEquals(
            $userIdentificationNumber,
            $authorisedExaminerDesignatedManager->getUserIdentificationNumber()
        );
        $this->assertEquals(
            $motSchemeTrainedPerson,
            $authorisedExaminerDesignatedManager->getMotSchemeTrainedPerson()
        );
        $this->assertEquals($contactDetailsId, $authorisedExaminerDesignatedManager->getContactDetails());
        $this->assertEquals(
            $isAuthorisedExaminerPrincipal, $authorisedExaminerDesignatedManager->getIsAuthorisedExaminerPrincipal()
        );
    }
}
