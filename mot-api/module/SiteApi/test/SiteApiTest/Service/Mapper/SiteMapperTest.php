<?php

namespace SiteApiTest\Service\Mapper;

use DvsaCommon\Constants\OrganisationType as OrganisationTypeConst;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaCommon\Validator\EmailAddressValidator;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteComment;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\SiteStatus;
use DvsaEntities\Entity\SiteType;
use SiteApi\Service\Mapper\SiteMapper;

/**
 * Test functionality of SiteMapper class.
 */
class SiteMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var SiteMapper */
    private $mapper;

    public function setUp()
    {
        $this->mapper = new SiteMapper();

        parent::setUp();
    }

    public function testToDto()
    {
        //  --  test fully filled site  --
        $this->assertInstanceOf(SiteDto::class, $this->mapper->toDto(self::getSiteEntity()));

        //  --  test not contacts, not comments, no assessment, no organisation --
        $site = new Site();
        $site
            ->setId(99999)
            ->setSiteNumber('V99999')
            ->setName('UnitTestSite')
            ->setType((new SiteType())->setCode(SiteTypeCode::AREA_OFFICE));

        $this->assertInstanceOf(SiteDto::class, $this->mapper->toDto($site));
    }

    public function testManyToDto()
    {
        $result = $this->mapper->manyToDto([self::getSiteEntity()]);
        $this->assertInstanceOf(SiteDto::class, $result[0]);
    }

    /**
     * @return Site
     */
    public static function getSiteEntity()
    {
        //  --  contacts    --
        $contactDetails1 = new ContactDetail();
        $contactDetails1
            ->setAddress((new Address())->setTown('Bristol'))
            ->addPhone(
                (new Phone())
                    ->setNumber('8888888')
                    ->setIsPrimary(true)
                    ->setContactType((new PhoneContactType()))
            )
            ->addEmail(
                (new Email())
                    ->setEmail('sitemappertest@'.EmailAddressValidator::TEST_DOMAIN)
                    ->setIsPrimary(true)
            );

        $contactDetails2 = new ContactDetail();
        $contactDetails2
            ->setAddress((new Address())->setTown('London'))
            ->addPhone(
                (new Phone())
                    ->setNumber('999999')
                    ->setIsPrimary(true)
                    ->setContactType((new PhoneContactType()))
            )
            ->addEmail(
                (new Email())
                    ->setEmail('sitemappertest@'.EmailAddressValidator::TEST_DOMAIN)
                    ->setIsPrimary(true)
            );

        $siteTypeBus = (new SiteContactType())->setCode(SiteContactTypeCode::BUSINESS);
        $siteTypeCorr = (new SiteContactType())->setCode(SiteContactTypeCode::CORRESPONDENCE);

        //  --  comments   --
        $comment1 = (new SiteComment())
            ->setComment('Comment Text1');

        $comment2 = (new SiteComment())
            ->setComment('Comment Text2');

        //  --  organisation    --
        $orgType = new OrganisationType();
        $orgType->setCode(OrganisationTypeConst::AUTHORISED_EXAMINER);

        $org = new Organisation();
        $org->setId(7777)
            ->setOrganisationType($orgType);

        //  -- site Assessment  --
        $siteAssessment = new EnforcementSiteAssessment();
        $siteAssessment
            ->setId(1234)
            ->setTester(
                (new Person())
                    ->setFirstName('tester')
            )
            ->setRepresentative(
                (new Person())
                    ->setFirstName('ae representative')
            )
            ->setExaminer(
                (new Person())
                    ->setFirstName('DVSA examiner')
            )
        ;

        // -- site status
        $siteStatus = (new SiteStatus())
            ->setCode(SiteStatusCode::APPROVED)
            ->setId(1234);

        //  --  bind all    --
        $site = new Site();
        $site
            ->setId(99999)
            ->setSiteNumber('V99999')
            ->setName('UnitTestSite')
            ->setStatus($siteStatus)
            ->setType((new SiteType())->setCode(SiteTypeCode::AREA_OFFICE))
            ->setContact($contactDetails1, $siteTypeBus)
            ->setContact($contactDetails2, $siteTypeCorr)
            ->addSiteComment($comment1)
            ->addSiteComment($comment2)
            ->setOrganisation($org)
            ->setLastSiteAssessment($siteAssessment);

        return $site;
    }
}
