<?php

namespace DvsaEntitiesTest\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommon\Enum\CompanyTypeCode;
use DvsaCommon\Enum\OrganisationBusinessRoleCode;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\BusinessRoleStatus;
use DvsaEntities\Entity\CompanyType;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationBusinessRole;
use DvsaEntities\Entity\OrganisationBusinessRoleMap;
use DvsaEntities\Entity\OrganisationType;
use DvsaEntities\Entity\Person;
use DvsaCommon\Enum\OrganisationContactTypeCode;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\OrganisationContactType;
use DvsaEntities\Entity\Site;
use PHPUnit_Framework_TestCase;

/**
 * Class OrganisationTest.
 */
class OrganisationTest extends PHPUnit_Framework_TestCase
{
    public function test_getContactDetail()
    {
        $organisation = new Organisation();
        $this->assertEquals(null, $organisation->getBusinessContact());
        $this->assertEquals(null, $organisation->getCorrespondenceContact());
    }

    public function testInitialState()
    {
        $organisation = new Organisation();

        $this->assertNull(
            $organisation->getId(), '"id" should initially be null'
        );
        $this->assertNull(
            $organisation->getName(), '"name" should initially be null'
        );
        $this->assertNull(
            $organisation->getRegisteredCompanyNumber(), '"registeredCompanyNumber" should initially be null'
        );
        $this->assertNull(
            $organisation->getOrganisationType(), '"organisationType" should initially be null'
        );

        AbstractCommonFieldsTesting::checkInitStateOfCommonFields($this, $organisation);
    }

    public function testSetsOrganisationPropertiesCorrectly()
    {
        $organisation = new Organisation();
        $registeredCompanyNumber = '07589628';
        $name = 'My Organisation';
        $orgTypeName = 'Authorised Examiner';
        $companyTypeName = CompanyTypeCode::COMPANY;
        $tradingAs = 'My garage';

        $organisationType = new OrganisationType();
        $organisationType->setName($orgTypeName);

        $companyType = new CompanyType();
        $companyType->setName($companyTypeName);

        $organisation->setRegisteredCompanyNumber($registeredCompanyNumber)
            ->setOrganisationType($organisationType)
            ->setCompanyType($companyType)
            ->setName($name)
            ->setTradingAs($tradingAs);

        $this->assertEquals($name, $organisation->getName());
        $this->assertEquals($registeredCompanyNumber, $organisation->getRegisteredCompanyNumber());
        $this->assertEquals($orgTypeName, $organisation->getOrganisationType()->getName());
        $this->assertEquals($companyTypeName, $organisation->getCompanyType()->getName());
        $this->assertEquals($tradingAs, $organisation->getTradingAs());
    }

    public function testGetDesignatedManager_activeAedmInOrganisation_shouldReturnNull()
    {
        $org = $this->setUpTestGetDesignatedManager([self::activeAedm()]);
        $this->assertInstanceOf(Person::class, $org->getDesignatedManager());
    }

    public function testGetDesignatedManager_inactiveAedmInOrganisation_shouldReturnPerson()
    {
        $org = $this->setUpTestGetDesignatedManager([self::inactiveAedm()]);
        $this->assertNull($org->getDesignatedManager());
    }

    public function testGetDesignatedManager_noAedmInOrganisation_shouldReturnNull()
    {
        $org = $this->setUpTestGetDesignatedManager(
            [
                self::buildOrganisationPosition(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE),
                self::buildOrganisationPosition(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_PRINCIPAL),
            ]
        );
        $this->assertNull($org->getDesignatedManager());
    }

    public function testGetDesignatedManager_emptyListOfPositions_shouldReturnNull()
    {
        $org = $this->setUpTestGetDesignatedManager(
            [
                self::buildOrganisationPosition(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DELEGATE),
                self::buildOrganisationPosition(OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_PRINCIPAL),
            ]
        );
        $this->assertNull($org->getDesignatedManager());
    }

    public function testGetSites()
    {
        $org = new Organisation();
        $sites = new ArrayCollection([new Site()]);

        XMock::mockClassField($org, 'sites', $sites);

        $this->assertCount(1, $org->getSites());
        $this->assertInstanceOf(Site::class, $org->getSites()[0]);
    }

    public function testGetCompanyType()
    {
        $org = (new Organisation())->setCompanyType(new CompanyType());
        $this->assertInstanceOf(CompanyType::class, $org->getCompanyType());
    }

    private function setUpTestGetDesignatedManager(array $positions = [])
    {
        $org = new Organisation();
        $collectionPositions = new ArrayCollection($positions);

        XMock::mockClassField($org, 'positions', $collectionPositions);

        return $org;
    }

    private static function activeAedm()
    {
        return self::buildOrganisationPosition();
    }

    private static function inactiveAedm($status = BusinessRoleStatusCode::INACTIVE)
    {
        return self::buildOrganisationPosition(
            OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
            $status
        );
    }

    /**
     * @param string $roleName
     * @param string $statusCode
     *
     * @return OrganisationBusinessRoleMap
     */
    private static function buildOrganisationPosition(
        $roleName = OrganisationBusinessRoleCode::AUTHORISED_EXAMINER_DESIGNATED_MANAGER,
        $statusCode = BusinessRoleStatusCode::ACTIVE
    ) {
        $position = new OrganisationBusinessRoleMap();
        $position->setPerson((new Person()))
            ->setOrganisationBusinessRole((new OrganisationBusinessRole())->setName($roleName))
            ->setBusinessRoleStatus((new BusinessRoleStatus())->setCode($statusCode));

        return $position;
    }

    public static function getTestData()
    {
        return [
            'id' => 1,
            'name' => 'Zdzisiuo',
            'registeredCompanyNumber' => 'Gdansk',
            'addressId' => '1',
            'correspondenceAddress' => null,
            'organisationType' => 'Company',
            'correspondenceAddressId' => null,
            'tradingAs' => 'My garage',
        ];
    }

    public function testSetContactWithOneContact()
    {
        // GIVEN
        $organisation = new Organisation();
        $contactDetail = new ContactDetail();
        $type = (new OrganisationContactType())->setCode(OrganisationContactTypeCode::REGISTERED_COMPANY);

        // WHEN
        $organisation->setContact($contactDetail, $type);

        // THEN
        $this->assertCount(1, $organisation->getContacts());
    }

    public function testSetContactWithBothTypesOfContactDetail()
    {
        // GIVEN
        $organisation = new Organisation();
        $contactDetail = new ContactDetail();
        $regCompType = (new OrganisationContactType())->setCode(OrganisationContactTypeCode::REGISTERED_COMPANY);
        $corrType = (new OrganisationContactType())->setCode(OrganisationContactTypeCode::CORRESPONDENCE);

        // WHEN
        $organisation->setContact($contactDetail, $regCompType);
        $organisation->setContact($contactDetail, $corrType);

        // THEN
        $this->assertCount(2, $organisation->getContacts());
    }

    public function testSetContactOverridesContactOfSameType()
    {
        // GIVEN
        $organisation = new Organisation();
        $contactDetail1 = (new ContactDetail())->setId(1);
        $contactDetail2 = (new ContactDetail())->setId(2);
        $regCompType = (new OrganisationContactType())->setCode(OrganisationContactTypeCode::REGISTERED_COMPANY);

        // WHEN
        $organisation->setContact($contactDetail1, $regCompType);
        $organisation->setContact($contactDetail2, $regCompType);

        // THEN
        $this->assertCount(1, $organisation->getContacts());

        $returnedContactDetail = $organisation->getContacts()[1]->getDetails();
        $this->assertSame($contactDetail2->getId(), $returnedContactDetail->getId());
    }

    public function testSetContactAndGetContactDetailByTypeReturnsCorrectContact()
    {
        // GIVEN
        $organisation = new Organisation();

        $regCompContactDetail = (new ContactDetail())->setId(1);
        $corrContactDetail = (new ContactDetail())->setId(2);
        $regCompType = (new OrganisationContactType())->setCode(OrganisationContactTypeCode::REGISTERED_COMPANY);
        $corrType = (new OrganisationContactType())->setCode(OrganisationContactTypeCode::CORRESPONDENCE);

        // WHEN
        $organisation->setContact($corrContactDetail, $corrType);
        $organisation->setContact($regCompContactDetail, $regCompType);

        // THEN
        $regCompContactDetail = $organisation->getBusinessContact()->getDetails();
        $this->assertEquals($regCompContactDetail->getId(), $regCompContactDetail->getId());

        $corrContactDetails = $organisation->getCorrespondenceContact()->getDetails();
        $this->assertEquals($corrContactDetail->getId(), $corrContactDetails->getId());
    }
}
