<?php

namespace OrganisationApiTest\Service\Validator;

use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommon\Enum\SiteStatusCode;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaEntities\Entity\AuthForAeStatus;
use DvsaEntities\Entity\AuthorisationForAuthorisedExaminer;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Site;
use OrganisationApi\Service\Validator\SiteLinkValidator;
use DvsaEntities\Entity\SiteStatus;

/**
 * Class SiteLinkValidatorTest.
 */
class SiteLinkValidatorTest extends \PHPUnit_Framework_TestCase
{
    const ORGANISATION_ID = 1;
    const SITE_NUMBER = 1;
    const AE_REF = 'AE1234';

    /** @var SiteLinkValidator */
    private $validator;

    public function setUp()
    {
        $this->validator = new SiteLinkValidator();
    }

    /**
     * @dataProvider dataProviderTestValidator
     */
    public function testValidator($organisation, $site, $orgId, $siteNumber, $isExpectError = false)
    {
        if ($isExpectError === true) {
            $this->setExpectedException(BadRequestException::class, 'Validation errors encountered');
        }

        $this->validator->validateLink($organisation, $site, $orgId, $siteNumber);
    }

    public function dataProviderTestValidator()
    {
        $orgEntity = (new Organisation())
            ->setAuthorisedExaminer(
                (new AuthorisationForAuthorisedExaminer())
                    ->setStatus(
                        (new AuthForAeStatus())
                            ->setCode(AuthorisationForAuthorisedExaminerStatusCode::APPROVED)
                    )
            );

        $siteEntity = new Site();
        $siteEntity->setStatus(
            (new SiteStatus())->setCode(SiteStatusCode::APPROVED)
        );

        return [
            //  logical block :: check validateLink method
            //  no errors
            [
                'organisation' => $orgEntity,
                'site' => $siteEntity,
                'orgId' => self::ORGANISATION_ID,
                'siteNumber' => self::SITE_NUMBER,
                'errors' => false,
            ],
            //  Error no organisation
            [
                'organisation' => null,
                'site' => $siteEntity,
                'orgId' => self::ORGANISATION_ID,
                'siteNumber' => self::SITE_NUMBER,
                'errors' => true,
            ],
            //  Error no site
            [
                'organisation' => new Organisation(),
                'site' => null,
                'orgId' => self::ORGANISATION_ID,
                'siteNumber' => self::SITE_NUMBER,
                'errors' => true,
            ],
            //  Error site not approved
            [
                'organisation' => new Organisation(),
                'site' => (new Site())->setStatus((new SiteStatus())->setCode(SiteStatusCode::APPLIED)),
                'orgId' => self::ORGANISATION_ID,
                'siteNumber' => self::SITE_NUMBER,
                'errors' => true,
            ],
            //  Error site already linked
            [
                'organisation' => $orgEntity,
                'site' => (new Site())->setOrganisation(
                                        (new Organisation())->setAuthorisedExaminer(
                                            (new AuthorisationForAuthorisedExaminer())->setNumber(self::AE_REF)
                                        )
                                  )->setStatus((new SiteStatus())->setCode(SiteStatusCode::APPROVED)),
                'orgId' => self::ORGANISATION_ID,
                'siteNumber' => self::SITE_NUMBER,
                'errors' => true,
            ],
            //  Error organisation not approved
            [
                'organisation' => (new Organisation())
                    ->setAuthorisedExaminer(
                        (new AuthorisationForAuthorisedExaminer())
                            ->setStatus(
                                (new AuthForAeStatus())
                                    ->setCode(AuthorisationForAuthorisedExaminerStatusCode::APPLIED)
                            )
                    ),
                'site' => $siteEntity,
                'orgId' => self::ORGANISATION_ID,
                'siteNumber' => self::SITE_NUMBER,
                'errors' => true,
            ],
        ];
    }

    /**
     * @dataProvider dataProviderTestValidateUnlink
     */
    public function testValidateUnlink($status, $isExpectError)
    {
        if ($isExpectError === true) {
            $this->setExpectedException(BadRequestException::class, 'Validation errors encountered');
        }

        $this->validator->validateUnlink($status);
    }

    public function dataProviderTestValidateUnlink()
    {
        return [
            //  success
            [
                'status' => OrganisationSiteStatusCode::SURRENDERED,
                'errors' => false,
            ],
            //  error :: not allowed status
            [
                'status' => OrganisationSiteStatusCode::APPLIED,
                'errors' => true,
            ],
            //  error :: status not provided
            [
                'status' => '',
                'errors' => true,
            ],
        ];
    }
}
