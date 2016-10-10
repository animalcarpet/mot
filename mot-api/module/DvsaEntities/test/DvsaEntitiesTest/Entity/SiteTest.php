<?php
namespace DvsaEntitiesTest\Entity;

use DvsaCommon\Enum\AuthorisationForTestingMotAtSiteStatusCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommonTest\TestUtils\TestCaseTrait;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSite;
use DvsaEntities\Entity\AuthorisationForTestingMotAtSiteStatus;
use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\OrganisationSiteMap;
use DvsaEntities\Entity\OrganisationSiteStatus;
use DvsaEntities\Entity\SiteComment;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use DvsaEntities\Entity\SiteContact;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\SiteFacility;
use DvsaEntities\Entity\SiteType;
use DvsaEntities\Entity\VehicleClass;
use PHPUnit_Framework_TestCase;

/**
 * Class SiteTest
 */
class SiteTest extends PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    public function testContacts()
    {
        $addressId = 5;
        $contactDetail = (new ContactDetail())->setAddress((new Address())->setId($addressId));
        $contactType = (new SiteContactType())->setCode(SiteContactTypeCode::BUSINESS);
        $site = new Site;

        $site->setContact($contactDetail, $contactType);
        $this->assertCount(1, $site->getContacts());
        $this->assertEquals($addressId, $site->getAddress()->getId());
    }

    public function testComments()
    {
        $site = new Site;
        $siteComment = new SiteComment();
        $site->addSiteComment($siteComment);
        $this->assertEquals($siteComment, $site->getSiteComments()->first());
    }

    public function testFreeLocation()
    {
        $expect = 'free location';

        $site = new Site;
        $siteType = (new SiteType())->setCode(SiteTypeCode::OFFSITE);
        $siteComment = new SiteComment();
        $comment = new Comment();

        $site->setType($siteType);
        $comment->setComment($expect);
        $siteComment->setComment($comment);
        $site->addSiteComment($siteComment);

        $this->assertEquals('', $site->getSiteNumber());
        $this->assertEquals($expect, $site->getName());
    }

    public function testGetName()
    {
        $name = "John & Son's Garage";
        $site = new Site;
        $siteType = (new SiteType())->setCode(SiteTypeCode::VEHICLE_TESTING_STATION); // something other than OFFSITE
        $site->setType($siteType);
        $site->setName($name);
        $this->assertEquals($name, $site->getName());
    }

    public function testSetContactDetailsOverridesOldCorrespondenceContact()
    {
        // GIVEN I have a site with correspondence contact
        $site = new Site();

        $contactDetail = new ContactDetail();
        $contactId = 1;
        $contactDetail->setId($contactId);
        $siteContactType = (new SiteContactType())->setCode(SiteContactTypeCode::CORRESPONDENCE);

        $site->setContact($contactDetail, $siteContactType);

        // WHEN I set new correspondence contact

        $newContact = new ContactDetail();
        $newContactId = 2;
        $newContact->setId($newContactId);
        $site->setContact($newContact, $siteContactType);

        // THEN The old one is removed

        /** @var SiteContact $contactWithOldId */
        $contactWithOldId = ArrayUtils::firstOrNull(
            $site->getContacts(),
            function (SiteContact $contact) use ($contactId) {
                return $contact->getDetails()->getId() === $contactId;
            }
        );

        $this->assertNull($contactWithOldId);

        // AND the new one takes it's place
        $this->assertNotEquals($newContactId, $site->getCorrespondenceContact()->getId());
    }

    public function testSetContactDetailsOverridesOldBusinessContact()
    {
        // GIVEN I have a site with business contact
        $site = new Site();

        $contact = new ContactDetail();
        $contactId = 1;
        $contact->setId($contactId);
        $siteContactType = (new SiteContactType())->setCode(SiteContactTypeCode::BUSINESS);

        $site->setContact($contact, $siteContactType);

        // WHEN I set new business contact

        $newContact = new ContactDetail();
        $newContactId = 2;
        $newContact->setId($newContactId);

        $site->setContact($newContact, $siteContactType);

        // THEN The old one is removed

        /** @var SiteContact $contactWithOldId */
        $contactWithOldId = ArrayUtils::firstOrNull(
            $site->getContacts(),
            function (SiteContact $contact) use ($contactId) {
                return $contact->getDetails()->getId() === $contactId;
            }
        );

        $this->assertNull($contactWithOldId);

        // AND the new one takes it's place
        $this->assertNotEquals($newContactId, $site->getBusinessContact()->getId());
    }

    /**
     * @param string $code
     *
     * @return AuthorisationForTestingMotAtSite
     */
    public static function newAuthForTesting($code = '4')
    {
        /** @var AuthorisationForTestingMotAtSite $authorisationForTestingMotAtSite */
        $authorisationForTestingMotAtSite = new AuthorisationForTestingMotAtSite();

        $vehicleClass = new VehicleClass();
        $vehicleClass->setCode($code);
        $authorisationForTestingMotAtSite->setVehicleClass($vehicleClass);

        $status = new AuthorisationForTestingMotAtSiteStatus();
        $status->setCode(AuthorisationForTestingMotAtSiteStatusCode::APPROVED);
        $authorisationForTestingMotAtSite->setStatus($status);

        return $authorisationForTestingMotAtSite;
    }

    public function testGetActiveAssociationWithAe()
    {
        $maps = [
            (new OrganisationSiteMap())
                ->setId(99)
                ->setStatus(
                    (new OrganisationSiteStatus())->setCode(OrganisationSiteStatusCode::ACTIVE)
                ),
            (new OrganisationSiteMap())
                ->setId(2)
                ->setStatus(
                    (new OrganisationSiteStatus())->setCode(OrganisationSiteStatusCode::UNKNOWN)
                ),
        ];

        /**
         * @var Site                $site
         * @var OrganisationSiteMap $actual
         */
        $site = XMock::of(Site::class, ['getAssociationWithAe']);
        $this->mockMethod($site, 'getAssociationWithAe', $this->once(), $maps);

        $actual = $site->getActiveAssociationWithAe();

        $this->assertEquals(99, $actual->getId());
    }
}
