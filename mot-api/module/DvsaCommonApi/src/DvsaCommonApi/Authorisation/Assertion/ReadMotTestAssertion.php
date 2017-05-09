<?php

namespace DvsaCommonApi\Authorisation\Assertion;

use DvsaCommon\Auth\MotAuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Common\MotTestDto;
use DvsaEntities\Entity\MotTest;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaEntities\Entity\Vehicle;
use Zend\Authentication\AuthenticationService;

/**
 * Class ReadMotTestAssertion.
 */
class ReadMotTestAssertion
{
    private $authorisationService;
    private $identityProvider;

    public function __construct(
        MotAuthorisationServiceInterface $authorisationService,
        AuthenticationService $identityProvider
    ) {
        $this->authorisationService = $authorisationService;
        $this->identityProvider = $identityProvider;
    }

    public function isGranted(MotTest $motTest)
    {
        try {
            $this->assertGranted($motTest);
        } catch (UnauthorisedException $exception) {
            return false;
        }

        return true;
    }

    public function assertGranted(MotTest $motTest)
    {
        if ($motTest->getMotTestType()->getIsDemo()) {
            $this->assertGrantedForDemoTest($motTest);
        } else {
            $this->assertIsAllowedToReadAllTests();
        }
    }

    public function assertGrantedForVehicle(Vehicle $vehicle)
    {
        if ($vehicle->getMotTestType()->getIsDemo()) {
            $this->assertGrantedForDemoTest($vehicle);
        } else {
            $this->assertIsAllowedToReadAllTests();
        }
    }

    private function assertIsAllowedToReadAllTests()
    {
        return $this->authorisationService->assertGranted(PermissionInSystem::MOT_TEST_READ_ALL);
    }

    public function isMotTestOwnerForDto(MotTestDto $motTest)
    {
        $testerId = $motTest->getTester()->getId();

        return $this->compareCurrentUserIdAgainstTesterId($testerId);
    }

    public function isMotTestOwner(MotTest $motTest)
    {
        $testerId = $motTest->getTester()->getId();

        return $this->compareCurrentUserIdAgainstTesterId($testerId);
    }

    /**
     * Tells us if the current user has read access to the demo test.
     *
     * @param MotTest $motTest
     *
     * @throws UnauthorisedException
     */
    private function assertGrantedForDemoTest(MotTest $motTest)
    {
        // If the person is the owner then yes they can access
        if ($this->isMotTestOwner($motTest)) {
            return;
        }

        // Check the permission system for to see if the user has the permission
        return $this->authorisationService->assertGranted(PermissionInSystem::MOT_DEMO_READ);
    }

    private function compareCurrentUserIdAgainstTesterId($id)
    {
        return $this->identityProvider->getIdentity()->getUserId() == $id;
    }
}
