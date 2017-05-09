<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace DvsaMotApi\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Dto\Common\OdometerReadingDto;
use DvsaCommon\Exception\UnauthorisedException;
use DvsaCommonApi\Authorisation\Assertion\ReadMotTestAssertion;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\Validator\Odometer\OdometerReadingDeltaAnomalyChecker;
use Zend\Authentication\AuthenticationService;

/**
 * Class OdometerReadinQueryService.
 *
 * Retrieves information on odometer reading
 */
class OdometerReadingQueryService
{
    /**
     * @var OdometerReadingDeltaAnomalyChecker
     */
    private $anomalyChecker;
    /**
     * @var AuthorisationServiceInterface
     */
    private $authService;

    /**
     * @var ReadMotTestAssertion
     */
    private $readMotTestAssertion;

    /**
     * @var MotTestRepository
     */
    private $motTestRepository;

    /**
     * @var AuthenticationService
     */
    private $authenticationService;

    /**
     * OdometerReadingQueryService constructor.
     *
     * @param OdometerReadingDeltaAnomalyChecker $odometerReadingDeltaAnomalyChecker
     * @param AuthorisationServiceInterface      $authorizationService
     * @param ReadMotTestAssertion               $readMotTestAssertion
     * @param MotTestRepository                  $motTestRepository
     * @param AuthenticationService              $authenticationService
     */
    public function __construct(
        OdometerReadingDeltaAnomalyChecker $odometerReadingDeltaAnomalyChecker,
        AuthorisationServiceInterface $authorizationService,
        ReadMotTestAssertion $readMotTestAssertion,
        MotTestRepository $motTestRepository,
        AuthenticationService $authenticationService
    ) {
        $this->anomalyChecker = $odometerReadingDeltaAnomalyChecker;
        $this->authService = $authorizationService;
        $this->readMotTestAssertion = $readMotTestAssertion;
        $this->motTestRepository = $motTestRepository;
        $this->authenticationService = $authenticationService;
    }

    /**
     * Returns notices for odometer reading done in a given test.
     * Currently, notices come from delta anomaly checker only.
     *
     * @param $motTestNumber
     *
     * @return array
     *               array of strings (notices)
     *
     * @throws UnauthorisedException
     */
    public function getNotices($motTestNumber)
    {
        if (!$this->motTestRepository->isTesterForMot(
            $this->authenticationService->getIdentity()->getUserId(),
            $motTestNumber
        )
        ) {
            throw new UnauthorisedException('You cannot read mot test because you are not the mot test owner');
        }
        $notices = [];

        $currentReadingDto = $this->motTestRepository->findReadingForTest($motTestNumber);
        $previousReadingDto = $this->motTestRepository->findPreviousReading($motTestNumber);

        if ($currentReadingDto instanceof OdometerReadingDto && $previousReadingDto instanceof OdometerReadingDto) {
            $notices = $this->anomalyChecker->check($currentReadingDto, $previousReadingDto)->toArrayOfTexts();
        }

        return $notices;
    }
}
