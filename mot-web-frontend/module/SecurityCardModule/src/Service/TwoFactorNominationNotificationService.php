<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\Service;

use Application\Data\ApiPersonalDetails;
use Application\Model\RoleSummaryCollection;
use DvsaClient\Mapper\OrganisationPositionMapper;
use DvsaClient\Mapper\SitePositionMapper;

class TwoFactorNominationNotificationService
{
    /**
     * @var ApiPersonalDetails
     */
    private $personalDetailsRepository;

    /**
     * @var OrganisationPositionMapper
     */
    private $organisationPositionRepository;

    /**
     * @var SitePositionMapper
     */
    private $sitePositionRepository;

    /**
     * TwoFactorNominationNotificationService constructor.
     * @param ApiPersonalDetails $personalDetailsRepository
     * @param OrganisationPositionMapper $organisationPositionRepository
     * @param SitePositionMapper $sitePositionRepository
     */
    public function __construct(
        ApiPersonalDetails $personalDetailsRepository,
        OrganisationPositionMapper $organisationPositionRepository,
        SitePositionMapper $sitePositionRepository
    ) {
        $this->personalDetailsRepository = $personalDetailsRepository;
        $this->organisationPositionRepository = $organisationPositionRepository;
        $this->sitePositionRepository = $sitePositionRepository;
    }

    /**
     * @param int $nomineeId
     * @return RoleSummaryCollection
     */
    public function sendNotificationsForPendingNominations($nomineeId)
    {
        $pendingNominations = $this->personalDetailsRepository->getPendingRolesForPerson($nomineeId);

        if ($this->containsPendingNominations($pendingNominations)) {

            foreach ($pendingNominations['organisations'] as $orgId => $organisation) {
                foreach ($organisation['roles'] as $roleCode) {
                    $this->organisationPositionRepository->updatePosition($orgId, $nomineeId, $roleCode);
                }
            }

            foreach ($pendingNominations['sites'] as $siteId => $site) {
                foreach ($site['roles'] as $roleCode) {
                    $this->sitePositionRepository->update($siteId, $nomineeId, $roleCode);
                }
            }
        }

        return new RoleSummaryCollection($pendingNominations);
    }

    /**
     * @param $nomineeId
     * @return bool
     */
    public function hasPendingNominations($nomineeId)
    {
        $pendingNominations = $this->personalDetailsRepository->getPendingRolesForPerson($nomineeId);

        return $this->containsPendingNominations($pendingNominations);
    }

    /**
     * @param array $pendingNominations
     * @return bool
     */
    private function containsPendingNominations(array $pendingNominations)
    {
        return
            (isset($pendingNominations['organisations']) && !empty($pendingNominations['organisations'])) ||
            (isset($pendingNominations['sites']) && !empty($pendingNominations['sites']));
    }
}
