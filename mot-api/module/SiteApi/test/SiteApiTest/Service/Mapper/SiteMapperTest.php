<?php

namespace SiteApiTest\Service\Mapper;

use DvsaCommon\Constants\OrganisationType as OrganisationTypeConst;
use DvsaCommon\Dto\Site\SiteDto;
use DvsaCommon\Enum\SiteContactTypeCode;
use DvsaCommon\Enum\SiteTypeCode;
use DvsaEntities\Entity\Address;
use DvsaEntities\Entity\ContactDetail;
use DvsaEntities\Entity\Email;
use DvsaEntities\Entity\EnforcementSiteAssessment;
use DvsaEntities\Entity\EnforcementVisitOutcome;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\OrganisationType;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\Phone;
use DvsaEntities\Entity\PhoneContactType;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteComment;
use DvsaEntities\Entity\SiteContactType;
use DvsaEntities\Entity\SiteType;
use SiteApi\Service\Mapper\SiteMapper;

/**
 * Test functionality of SiteMapper class
 *
 * @package OrganisationApiTest\Mapper
 */
class SiteMapperTest extends \PHPUnit_Framework_TestCase
{
    /** @var  SiteMapper */
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
                    ->setEmail('test@test.com')
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
                    ->setEmail('test@test.com')
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

        $org = new Organisation;
        $org->setId(7777)
            ->setOrganisationType($orgType);

        //  -- site Assessment  --
        $visitOutcome = new EnforcementVisitOutcome();

        $siteAssessment = new EnforcementSiteAssessment();
        $siteAssessment
            ->setTester((new Person))
            ->setVisitOutcome($visitOutcome);

        //  --  bind all    --
        $site = new Site();
        $site
            ->setId(99999)
            ->setSiteNumber('V99999')
            ->setName('UnitTestSite')
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
