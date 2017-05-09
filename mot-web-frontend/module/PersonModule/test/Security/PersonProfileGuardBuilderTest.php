<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link http://gitlab.clb.npm/mot/mot
 */

namespace Dvsa\Mot\Frontend\PersonModuleTest\Security;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use Dashboard\Model\PersonalDetails;
use Dashboard\Service\TradeRolesAssociationsService;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuard;
use Dvsa\Mot\Frontend\PersonModule\Security\PersonProfileGuardBuilder;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use Dvsa\Mot\Frontend\SecurityCardModule\Support\TwoFaFeatureToggle;
use DvsaCommon\Model\TesterAuthorisation;
use DvsaClient\Mapper\TesterGroupAuthorisationMapper;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommonTest\TestUtils\XMock;

class PersonProfileGuardBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PersonProfileGuardBuilder
     */
    private $builder;

    /**
     * @var TwoFaFeatureToggle
     */
    private $twoFaFeatureToggle;

    public function setUp()
    {
        $this->twoFaFeatureToggle = XMock::of(TwoFaFeatureToggle::class);

        /** @var MotFrontendAuthorisationServiceInterface $authorisationService */
        $authorisationService = $this
            ->getMockBuilder(MotFrontendAuthorisationServiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MotIdentityProviderInterface $identityProvider */
        $identityProvider = $this
            ->getMockBuilder(MotIdentityProviderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TesterAuthorisation $testerAuthorisation */
        $testerAuthorisation = $this
            ->getMockBuilder(TesterAuthorisation::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var TesterGroupAuthorisationMapper $testerGroupAuthorisationMapper */
        $testerGroupAuthorisationMapper = $this
            ->getMockBuilder(TesterGroupAuthorisationMapper::class)
            ->disableOriginalConstructor()
            ->getMock();
        $testerGroupAuthorisationMapper
            ->method('getAuthorisation')
            ->willReturn($testerAuthorisation);

        /** @var TradeRolesAssociationsService $tradeRolesAndAssociationsService */
        $tradeRolesAndAssociationsService = $this
            ->getMockBuilder(TradeRolesAssociationsService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $tradeRolesAndAssociationsService
            ->method('getRolesAndAssociations')
            ->willReturn([]);

        $this->builder = new PersonProfileGuardBuilder($authorisationService, $identityProvider,
            $testerGroupAuthorisationMapper, $tradeRolesAndAssociationsService,
            $this->twoFaFeatureToggle);
    }

    public function testReturnsTesterAuthorisation()
    {
        $this->assertInstanceOf(TesterAuthorisation::class, $this->builder->getTesterAuthorisation(1));
    }

    public function testReturnsTradeRolesAndAssociations()
    {
        $this->assertInternalType('array', $this->builder->getTradeRolesAndAssociations(1));
    }

    public function testCreatesPersonProfileGuardInstance()
    {
        $personalDetails = $this->createPersonalDetails(1);

        $this->assertInstanceOf(PersonProfileGuard::class, $this->builder->createPersonProfileGuard($personalDetails,
            ContextProvider::NO_CONTEXT));
    }

    /**
     * @param int $personId
     *
     * @return PersonalDetails
     */
    private function createPersonalDetails($personId)
    {
        $personalDetails = $this
            ->getMockBuilder(PersonalDetails::class)
            ->disableOriginalConstructor()
            ->getMock();

        $personalDetails
            ->method('getId')
            ->willReturn($personId);

        return $personalDetails;
    }
}
