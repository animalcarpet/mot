<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModule\Security;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dashboard\Model\PersonalDetails;
use Dashboard\Service\TradeRolesAssociationsService;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaFeature\FeatureToggles;

/**
 * PersonProfileGuardBuilder creates PersonProfileGuard instances.
 */
class PersonProfileGuardBuilder
{
    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    private $authorisationService;

    /**
     * @var MotIdentityProviderInterface
     */
    private $identityProvider;

    /**
     * @var TesterGroupAuthorisationMapper
     */
    private $testerGroupAuthorisationMapper;

    /**
     * @var TradeRolesAssociationsService
     */
    private $tradeRolesAndAssociationsService;

    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFeatureToggle;

    /**
     * @param MotFrontendAuthorisationServiceInterface $authorisationService
     * @param MotIdentityProviderInterface $identityProvider
     * @param TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper
     * @param TradeRolesAssociationsService $tradeRolesAndAssociationsService
     * @param TwoFaFeatureToggle $twoFeatureToggle
     */
    public function __construct(MotFrontendAuthorisationServiceInterface $authorisationService,
                                MotIdentityProviderInterface $identityProvider,
                                TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper,
                                TradeRolesAssociationsService $tradeRolesAndAssociationsService,
                                TwoFaFeatureToggle $twoFeatureToggle)
    {
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
        $this->testerGroupAuthorisationMapper = $testerGroupAuthorisationMapper;
        $this->tradeRolesAndAssociationsService = $tradeRolesAndAssociationsService;
        $this->twoFeatureToggle = $twoFeatureToggle;
    }

    /**
     * @param int $targetPersonId
     *
     * @return TesterAuthorisation
     */
    public function getTesterAuthorisation($targetPersonId)
    {
        return $this->testerGroupAuthorisationMapper->getAuthorisation($targetPersonId);
    }

    /**
     * @param int $targetPersonId
     *
     * @return array
     */
    public function getTradeRolesAndAssociations($targetPersonId)
    {
        return $this->tradeRolesAndAssociationsService->getRolesAndAssociations($targetPersonId);
    }

    /**
     * @param PersonalDetails $targetPersonDetails
     * @param string          $context             The context in which we are viewing the profile. Could be AE, VE or User Search.
     *
     * @return PersonProfileGuard
     */
    public function createPersonProfileGuard(PersonalDetails $targetPersonDetails, $context)
    {
        $targetPersonId = $targetPersonDetails->getId();

        $testerAuthorisation = $this->getTesterAuthorisation($targetPersonId);
        $tradeRolesAndAssociations = $this->getTradeRolesAndAssociations($targetPersonId);

        return new PersonProfileGuard($this->authorisationService, $this->identityProvider,
            $targetPersonDetails, $testerAuthorisation, $tradeRolesAndAssociations, $context, $this->twoFeatureToggle);
    }
}
