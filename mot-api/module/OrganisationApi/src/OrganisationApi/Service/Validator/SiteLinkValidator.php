<?php

namespace OrganisationApi\Service\Validator;

use DvsaCommon\Enum\AuthorisationForAuthorisedExaminerStatusCode;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommonApi\Service\Validator\AbstractValidator;
use DvsaEntities\Entity\Organisation;
use DvsaEntities\Entity\Site;
use DvsaCommon\Enum\SiteStatusCode;

class SiteLinkValidator extends AbstractValidator
{
    const FIELD_STATUS = 'status';
    const FIELD_SITE_NUMBER = 'siteNumber';

    const ERR_ORGANISATION_NOT_FOUND = 'We could not find the organisation ID %s';
    const ERR_ORGANISATION_NOT_APPROVED = 'The organisation is not approved';
    const ERR_SITE_NOT_FOUND = 'We could not find the site ID %s';
    const ERR_SITE_NOT_APPROVED = 'The site %s is not approved';
    const ERR_SITE_NOT_AVAILABLE = 'This site is not available, it is currently associated to %s';
    const ERR_UNLINK_INVALID_STATUS = 'A status must be selected';

    /**
     * @param Organisation $organisation
     * @param Site         $site
     * @param int          $orgId
     * @param string       $siteNumber
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function validateLink($organisation, $site, $orgId, $siteNumber)
    {
        // First check: Check if the entities are valid
        if (empty($organisation)) {
            $this->errors->add(sprintf(self::ERR_ORGANISATION_NOT_FOUND, $orgId), self::FIELD_SITE_NUMBER);
        }

        if (empty($site)) {
            $this->errors->add(sprintf(self::ERR_SITE_NOT_FOUND, $siteNumber), self::FIELD_SITE_NUMBER);
        }

        if (!empty($site) && $site->getStatus()->getCode() != SiteStatusCode::APPROVED) {
            $this->errors->add(sprintf(self::ERR_SITE_NOT_APPROVED, $siteNumber), self::FIELD_SITE_NUMBER);
        }

        $this->errors->throwIfAnyField();

        // Second check: Check if the data are valid
        if ($site->getOrganisation() !== null) {
            $this->errors->add(
                sprintf(
                    self::ERR_SITE_NOT_AVAILABLE,
                    $site->getOrganisation()->getAuthorisedExaminer()->getNumber().' '.
                    $site->getOrganisation()->getName()
                ),
                self::FIELD_SITE_NUMBER
            );
        }

        $status = $organisation->getAuthorisedExaminer()->getStatus()->getCode();
        if ($status != AuthorisationForAuthorisedExaminerStatusCode::APPROVED) {
            $this->errors->add(self::ERR_ORGANISATION_NOT_APPROVED, self::FIELD_SITE_NUMBER);
        }

        $this->errors->throwIfAnyField();
    }

    public function validateUnlink($statusCode)
    {
        $allowedStatuses = [
            OrganisationSiteStatusCode::SURRENDERED => 1,
            OrganisationSiteStatusCode::WITHDRAWN => 1,
        ];

        if (!isset($allowedStatuses[$statusCode])) {
            $this->errors->add(self::ERR_UNLINK_INVALID_STATUS, self::FIELD_STATUS);
        }

        $this->errors->throwIfAnyField();
    }
}
