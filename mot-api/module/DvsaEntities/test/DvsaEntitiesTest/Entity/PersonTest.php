<?php

namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\AuthorisationForTestingMotStatusCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommon\Enum\SiteBusinessRoleCode;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\AuthorisationForTestingMotStatus;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\PersonContact;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use PHPUnit_Framework_TestCase;

/**
 * Class PersonTest.
 */
class PersonTest extends PHPUnit_Framework_TestCase
{
    public function testIsTesterGivenProperAuthorizationShouldBeTester()
    {
        $qualifedAuthorisation = $this->authorisationForStatus(AuthorisationForTestingMotStatusCode::QUALIFIED);
        $p = (new Person())->setAuthorisationsForTestingMot([$qualifedAuthorisation]);
        $this->assertTrue($p->isTester());
    }

    public function testIsTesterGivenNoProperAuthorizationShouldNotBeTester()
    {
        $p = (new Person())->setAuthorisationsForTestingMot([]);
        $this->assertFalse($p->isTester());
    }

    private function authorisationForStatus($status)
    {
        return (new AuthorisationForTestingMot())
            ->setStatus((new AuthorisationForTestingMotStatus())->setCode($status));
    }

    /**
     * Check transformation First Middle and Last name to short presentation F. M. Last_name.
     *
     * @param array|Person $personData Person data
     * @param string       $expect     expected short name
     *
     * @dataProvider dataProviderTestGetShortName
     */
    public function testGetShortName($personData, $expect)
    {
        $actual = Person::getShortName($personData);

        $this->assertEquals($expect, $actual);
    }

    public function dataProviderTestGetShortName()
    {
        return [
            [
                'personData' => [
                    'firstName' => null,
                    'middleName' => null,
                    'familyName' => null,
                ],
                'expect' => '',
            ],
            [
                [
                    'firstName' => 'Simon',
                    'middleName' => 'John',
                    'familyName' => 'Thebad',
                ],
                'S. J. Thebad',
            ],
            [
                (new Person())->setFirstName('Simon')->setMiddleName('John')->setFamilyName('Thebad'),
                'S. J. Thebad',
            ],
            [
                (new Person())->setFirstName('  Simon ')->setMiddleName('  John ')->setFamilyName('    Thebad    '),
                'S. J. Thebad',
            ],
            [
                (new Person())->setFirstName('  Simon ')->setMiddleName('  John ')
                    ->setFamilyName('    TheVeryVeryVeryVeryLongFamilyNamebad    '),
                'S. J. TheVeryVeryVeryVeryLongFamilyNamebad',
            ],
        ];
    }

    public function testFindAllOrganisationPositions()
    {
        //GIVEN a person entity with some organisation positions
        $aedmCode = OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER;
        $aepCode = OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_PRINCIPAL;
        $person = (new Person());

        $this->givePersonOrganisationPosition($person, $aedmCode);
        $this->givePersonOrganisationPosition($person, $aepCode);

        //WHEN I find all positions with no filter param
        $allPositions = $person->findAllOrganisationPositions();

        //THEN I should find all positions
        $this->assertCount(2, $allPositions);
    }

    public function testFindAllOrganisationPositionsWithFilterParam()
    {
        //GIVEN a person entity with some organisation positions
        $filterCode = OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER;
        $otherCode = OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_PRINCIPAL;
        $person = (new Person());

        $this->givePersonOrganisationPosition($person, $filterCode);
        $this->givePersonOrganisationPosition($person, $otherCode);

        //WHEN I find all positions with a filter param
        $filteredPositions = $person->findAllOrganisationPositions($filterCode);

        //THEN I should only find the filtered positions
        $this->assertCount(1, $filteredPositions);
        $this->assertSame($filterCode, $filteredPositions[0]->getOrganisationBusinessRole()->getName());
    }

    public function testFindAllSitePositions()
    {
        //GIVEN a person entity with some site positions
        $testerCode = SiteBusinessRoleCode::TESTER;
        $siteAdminCode = SiteBusinessRoleCode::SITE_ADMIN;
        $person = (new Person());

        $this->givePersonSiteBusinessPosition($person, $testerCode);
        $this->givePersonSiteBusinessPosition($person, $siteAdminCode);

        //WHEN I find all positions with no filter param
        $allPositions = $person->findAllSitePositions();

        //THEN I should find all positions
        $this->assertCount(2, $allPositions);
    }

    public function testFindAllSitePositionsWithFilterParam()
    {
        //GIVEN a person entity with some site positions
        $filterCode = SiteBusinessRoleCode::TESTER;
        $siteAdminCode = SiteBusinessRoleCode::SITE_ADMIN;
        $person = (new Person());

        $this->givePersonSiteBusinessPosition($person, $filterCode);
        $this->givePersonSiteBusinessPosition($person, $siteAdminCode);

        //WHEN I find all positions with a filter param
        $filteredPositions = $person->findAllSitePositions($filterCode);

        //THEN I should only find the filtered positions
        $this->assertCount(1, $filteredPositions);
        $this->assertSame($filterCode, $filteredPositions[0]->getSiteBusinessRole()->getName());
    }

    private function givePersonOrganisationPosition(Person $person, $organisationBusinessRoleCode)
    {
        $role = (new OrganisationBusinessRole())->setName($organisationBusinessRoleCode);
        $organisationBusinessRoleMap = (new OrganisationBusinessRoleMap())->setOrganisationBusinessRole($role);

        $person->addOrganisationBusinessRoleMap($organisationBusinessRoleMap);
    }

    private function givePersonSiteBusinessPosition(Person $person, $siteBusinessRoleCode)
    {
        $role = (new SiteBusinessRole())->setName($siteBusinessRoleCode);
        $siteBusinessRoleMaps = (new SiteBusinessRoleMap())->setSiteBusinessRole($role);
        $person->addSiteBusinessRoleMaps($siteBusinessRoleMaps);
    }

    public function testGetUserReference()
    {
        $person = (new Person())
            ->setUserReference('USER_REFERENCE');

        $this->assertSame('USER_REFERENCE', $person->getUserReference());
    }

    public function testGetPrimaryEmail()
    {
        $person = new Person();

        $email = (new Email())
            ->setEmail('persontest@'.EmailAddressValidator::TEST_DOMAIN)
            ->setIsPrimary(true);

        $contactDetail = (new ContactDetail())
            ->addEmail($email);

        $contactType = new \DvsaEntities\Entity\PersonContactType();
        $personContact = new PersonContact($contactDetail, $contactType, $person);
        $person->addContact($personContact);

        $this->assertSame('persontest@'.EmailAddressValidator::TEST_DOMAIN, $person->getPrimaryEmail());
    }
}
