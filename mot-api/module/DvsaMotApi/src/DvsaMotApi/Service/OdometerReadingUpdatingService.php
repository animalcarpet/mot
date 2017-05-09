<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use Api\Check\CheckResultExceptionTranslator;
use DvsaCommonApi\Authorisation\Assertion\ApiPerformMotTestAssertion;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaEntities\Entity\MotTest;
use DvsaMotApi\Service\Validator\MotTestValidator;
use DvsaMotApi\Service\Validator\Odometer\OdometerReadingValidator;

/**
 * Class OdometerReadingUpdatingService.
 */
class OdometerReadingUpdatingService
{
    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;
    /**
     * @var MotTestSecurityService
     */
    private $motTestSecurityService;

    /** @var MotTestValidator $motTestValidator */
    private $motTestValidator;

    private $performMotTestAssertion;

    /**
     * @param AuthorisationServiceInterface $authService
     * @param MotTestSecurityService        $motTestSecurityService
     * @param Validator\MotTestValidator    $motTestValidator
     */
    public function __construct(
        AuthorisationServiceInterface $authService,
        MotTestSecurityService $motTestSecurityService,
        MotTestValidator $motTestValidator,
        ApiPerformMotTestAssertion $performMotTestAssertion
    ) {
        $this->authService = $authService;
        $this->motTestSecurityService = $motTestSecurityService;
        $this->motTestValidator = $motTestValidator;
        $this->performMotTestAssertion = $performMotTestAssertion;
    }

    /**
     * Updates odometer reading for a given MOT test.
     *
     * @param OdometerReadingDto $newReading
     * @param MotTest            $motTest
     *
     * @throws \DvsaCommonApi\Service\Exception\ForbiddenException
     */
    public function updateForMotTest(OdometerReadingDto $newReading, MotTest $motTest)
    {
        $this->performMotTestAssertion->assertGranted($motTest);

        $this->motTestValidator->assertCanBeUpdated($motTest);
        //TODO: dependency should be injected #przemek
        $checkResult = (new OdometerReadingValidator())->validate(
            $newReading->getValue(),
            $newReading->getUnit(),
            $newReading->getResultType()
        );
        CheckResultExceptionTranslator::tryThrowDataValidationException($checkResult);

        /* NEED TO FIGURE OUT RULES
         * if (!$this->motTestSecurityService->canModifyOdometerForTest($motTest)
         * || !$this->motTestSecurityService->isCurrentTesterAssignedToVts(
         * $motTest->getVehicleTestingStation()->getId()
         * )
         * ) {
         * throw new ForbiddenException("You are not allowed to change odometer reading");
         * }
         **/

        $motTest->setOdometerValue($newReading->getValue());
        $motTest->setOdometerUnit($newReading->getUnit());
        $motTest->setOdometerResultType($newReading->getResultType());
    }
}
