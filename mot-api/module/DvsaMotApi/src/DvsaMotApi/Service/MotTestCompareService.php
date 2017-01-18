<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Error\Message as ErrorMessage;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\BadRequestExceptionWithMultipleErrors;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Model\MotTestComparator;

/**
 * Class MotTestCompareService
 *
 * @package DvsaMotApi\Service
 */
class MotTestCompareService extends AbstractService
{

    /** @var MotTestRepository  */
    protected $motTestRepository;
    protected $objectHydrator;
    protected $authService;
    protected $errors = [];

    public function __construct(
        EntityManager $entityManager,
        DoctrineObject $objectHydrator,
        AuthorisationServiceInterface $authService
    ) {
        parent::__construct($entityManager);
        $this->motTestRepository = $this->entityManager->getRepository(MotTest::class);
        $this->objectHydrator = $objectHydrator;
        $this->authService = $authService;
    }

    public function getMotTestCompareData($motTestNumber)
    {
        $this->authService->assertGranted(PermissionInSystem::MOT_TEST_COMPARE);

        $motTest = $this->motTestRepository->getMotTestByNumber($motTestNumber);

        $originalMotTest = $motTest->getMotTestIdOriginal();

        if (!$originalMotTest) {
            throw new NotFoundException('No parent Mot Test', $motTestNumber);
        }

        return $this->compareTwoMotTest($motTest, $originalMotTest);
    }

    public function getMotTestCompareDataFromTwoTest($motTestNumber, $motTestNumberToCompare)
    {
        $this->authService->assertGranted(PermissionInSystem::MOT_TEST_COMPARE);

        /** @var MotTest $motTest */
        $motTest = $this->findMotTest($motTestNumber);

        if (!$motTest || ($motTest->getStatus() != 'FAILED' && $motTest->getStatus() != 'PASSED')) {
            $this->errors['motTestNumber'] = new ErrorMessage(
                'The Ve\'s Test Number is invalid',
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['motTestNumber' => null]
            );
        }

        /** @var MotTest $motTest */
        $originalMotTest = $this->findMotTest($motTestNumberToCompare);

        if (!$originalMotTest
            || ($originalMotTest->getStatus() != 'FAILED' && $originalMotTest->getStatus() != 'PASSED')
        ) {
            $this->errors['motTestNumberToCompare'] = new ErrorMessage(
                'The Tester\'s Test Number is invalid',
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['motTestNumberToCompare' => null]
            );
        }
        if (($motTest && $originalMotTest) && $originalMotTest->getVin() != $motTest->getVin()) {
            $this->errors['motTestNumber'] = new ErrorMessage(
                'The VINs for the Test shouldn\'t be different',
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['motTestNumber' => null]
            );
        }

        if (($motTest && $originalMotTest) && $originalMotTest->getNumber() == $motTest->getNumber()) {
            $this->errors['motTestNumber'] = new ErrorMessage(
                'The MOT test number must be different',
                BadRequestException::ERROR_CODE_INVALID_DATA,
                ['motTestNumber' => null]
            );
        }

        if (count($this->errors)) {
            throw new BadRequestExceptionWithMultipleErrors([], $this->errors);
        }

        return $this->compareTwoMotTest($motTest, $originalMotTest);
    }

    /**
     * @param MotTest $motTest
     * @param MotTest $originalMotTest
     *
     * @return array
     */
    protected function compareTwoMotTest($motTest, $originalMotTest)
    {
        $comparator = new MotTestComparator();

        $motTestRfrsData = $motTest->extractRfrs($this->objectHydrator);
        $originalMotTestRfrsData = $originalMotTest->extractRfrs($this->objectHydrator);

        $rfrCompare = $comparator->compareRfrArray($originalMotTestRfrsData, $motTestRfrsData);
        $rfrCompareGrouped = $comparator->getMotTestRfrGroupedByManualReference($rfrCompare);
        ksort($rfrCompareGrouped);

        return $rfrCompareGrouped;
    }

    /**
     * @param string $motTestNumber
     *
     * @return null|MotTest
     */
    private function findMotTest($motTestNumber)
    {
        try {
            return $this->motTestRepository->getMotTestByNumber($motTestNumber);
        } catch (NotFoundException $e) {
        }
    }
}
