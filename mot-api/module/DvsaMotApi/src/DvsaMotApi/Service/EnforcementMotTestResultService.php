<?php

namespace DvsaMotApi\Service;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Utility\ArrayUtils;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeDisplayFormat;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\BadRequestExceptionWithMultipleErrors;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Comment;
use DvsaEntities\Entity\EnforcementDecisionOutcome;
use DvsaEntities\Entity\EnforcementMotTestDifference;
use DvsaEntities\Entity\EnforcementMotTestResult;
use DvsaEntities\Entity\MotTest;
use DvsaEntities\Entity\Person;
use DvsaEntities\Repository\MotTestRepository;
use DvsaMotApi\Service\Mapper\MotTestMapper;
use DvsaMotApi\Service\RfrValidator\CheckAdvisoryWarningHasJustificationAgainstScore;
use DvsaMotApi\Service\RfrValidator\CheckCategoryAllowedForDefectNotApplicable;
use DvsaMotApi\Service\RfrValidator\CheckCategoryExistsForScore;
use DvsaMotApi\Service\RfrValidator\CheckCategoryPleaseSelectForDefect;
use DvsaMotApi\Service\RfrValidator\CheckDecisionExistsForScore;
use DvsaMotApi\Service\RfrValidator\CheckDecisionsForCategoryNotApplicable;
use DvsaMotApi\Service\RfrValidator\CheckDisciplinaryActionHasJustificationAgainstScore;
use DvsaMotApi\Service\RfrValidator\CheckJustificationForScoreDisregard;
use DvsaMotApi\Service\RfrValidator\CheckNoFurtherActionHasJustificationAgainstScore;
use DvsaMotApi\Service\RfrValidator\CheckScoreForDefectNotApplicable;

/**
 * Class EnforcementMotTestResultService.
 */
class EnforcementMotTestResultService extends AbstractService
{
    const MOT_DATE_FORMAT = 'Y-m-d H:i:s';

    /** @var DoctrineObject */
    protected $objectHydrator;

    /** @var EnforcementMotTestResult */
    protected $repository;

    /** @var AuthorisationServiceInterface */
    protected $authService;

    /** @var MotTestMapper */
    protected $motTestMapper;

    /**
     * @var array motTestNumber => MotTest entity
     */
    protected $motTestCache = [];

    /** @var array */
    protected $validationErrors = [];

    /**
     * @param EntityManager                 $entityManager
     * @param DoctrineObject                $objectHydrator
     * @param AuthorisationServiceInterface $authService
     * @param MotTestMapper                 $motTestMapper
     */
    public function __construct(
        EntityManager $entityManager,
        DoctrineObject $objectHydrator,
        AuthorisationServiceInterface $authService,
        MotTestMapper $motTestMapper
    ) {
        parent::__construct($entityManager);

        $this->objectHydrator = $objectHydrator;
        $this->repository = $this->entityManager->getRepository(EnforcementMotTestResult::class);
        $this->authService = $authService;
        $this->motTestMapper = $motTestMapper;
    }

    /**
     * Return an array containing the saved Vehicle Examiners decisions.
     *
     * @param $resultId
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getEnforcementMotTestResultData($resultId)
    {
        $this->authService->assertGranted(PermissionInSystem::MOT_TEST_COMPARE);

        /** @var EnforcementMotTestResult $result */
        $result = $this->repository->findOneBy(['id' => $resultId]);
        if (!$result) {
            throw new NotFoundException('EnforcementMotTestResult', $resultId);
        }

        return $this->extract($result);
    }

    /**
     * Create db records to store the Vehicle Examiner's decisions after a re-inspection.
     *
     * @param array  $data
     * @param string $username
     *
     * @return array
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function createEnforcementMotTestResult($data, $username)
    {
        $this->authService->assertGranted(PermissionInSystem::MOT_TEST_COMPARE);

        if (!array_key_exists('mappedRfrs', $data)) {
            // this is allowed - make sure it is initialised to a sane value.  There may not be any differences to
            // compare.
            $data['mappedRfrs'] = [];
        }

        if (!array_key_exists('reinspectionMotTestNumber', $data)) {
            throw new BadRequestException('Missing Reinspection Mot Test Number', BadRequestException::ERROR_CODE_INVALID_DATA);
        }

        if (!array_key_exists('caseOutcome', $data)) {
            throw new BadRequestException('Missing case outcome', BadRequestException::ERROR_CODE_INVALID_DATA);
        }

        $data = $this->injectRfrInformation($data);

        $this->validateMappedRfrs($data);

        $catalog = $this->getCatalog($data);
        $reinspectionMotTest = $this->getMotTestByNumber($data['reinspectionMotTestNumber']);
        $user = $this->getUser($username);
        $outcome = $this->getOutcome($data['caseOutcome']);
        $finalComment = $this->createFinalJustificationComment($data['finalJustification'], $user);
        $motTest = null;

        if (!array_key_exists('motTestNumber', $data) || is_null($data['motTestNumber'])) {
            $motTest = $reinspectionMotTest->getMotTestIdOriginal();
        } else {
            $motTest = $this->getMotTestByNumber($data['motTestNumber']);
        }

        // TODO: Serious refactor this method. Its bad. Very bad. For now this will stop the
        // TODO: situation occurring of trying to insert with a NULL mot_test_id column.
        if (is_null($motTest)) {
            throw new BadRequestException('No usable MOT test number: ', BadRequestException::ERROR_CODE_INVALID_DATA);
        }

        $enfMotTestResult = new EnforcementMotTestResult();
        $enfMotTestResult
            ->setDecisionOutcome($outcome)
            ->setMotTestInspection($reinspectionMotTest)
            ->setMotTest($motTest)
            ->setCreatedBy($user);

        if ($finalComment) {
            $enfMotTestResult->setComment($finalComment);
        }
        $this->entityManager->persist($enfMotTestResult);
        $this->processRfrs($data, $user, $catalog, $enfMotTestResult);

        $this->validateResults($data, $enfMotTestResult->getTotalScore());
        $this->entityManager->flush();

        return ['id' => $enfMotTestResult->getId()];
    }

    public function updateEnforcementMotTestResult($data, $username)
    {
        $this->authService->assertGranted(PermissionInSystem::MOT_TEST_COMPARE);

        $enforcementMotTestResultData = $data['data']['enforcementMotTestResult'];

        //$updateUser = $this->getUser($enforcementMotTestResultData['lastUpdatedBy']);

        $user = $this->getUser($username);

        $reinspectionMotTestResultEntity = $this->repository->findOneBy(['id' => $enforcementMotTestResultData['id']]);

        // Deal with witnesses
        if (isset($enforcementMotTestResultData['enforcementMotTestResultWitnesses'])) {
            $witnessesData = $enforcementMotTestResultData['enforcementMotTestResultWitnesses'];
            foreach ($witnessesData as $witnessData) {
                if (isset($witnessData['id']) && !empty($witnessData['id'])) {
                    $witnessEntity = $this->entityManager
                        ->getRepository(\DvsaEntities\Entity\EnforcementMotTestResultWitnesses::class)
                        ->findOneBy(array('id' => $witnessData['id']));
                }

                if (empty($witnessEntity)) {
                    $witnessEntity = new \DvsaEntities\Entity\EnforcementMotTestResultWitnesses();
                }

                $witnessEntity->setEnforcementMotTestResult($reinspectionMotTestResultEntity);
                $witnessEntity->setName($witnessData['name']);
                $witnessEntity->setPosition($witnessData['position']);
                $witnessEntity->setType($witnessData['type']);
                $this->entityManager->persist($witnessEntity);
                unset($witnessEntity);
            }
            unset($enforcementMotTestResultData['enforcementMotTestResultWitnesses']);
        }

        // Deal with Comment
        if (isset($enforcementMotTestResultData['comment'])) {
            $commentData = $enforcementMotTestResultData['comment'];

            if (isset($commentData['id']) && !empty($commentData['id'])) {
                $commentEntity = $this->entityManager
                    ->getRepository(\DvsaEntities\Entity\Comment::class)
                    ->findOneBy(array('id' => $commentData['id']));
            }

            if (empty($commentEntity)) {
                $commentEntity = $this->createFinalJustificationComment($commentData['comment'], $user);
            }

            $commentEntity->setComment($commentData['comment']);

            $reinspectionMotTestResultEntity->setComment($commentEntity);
            $this->entityManager->persist($commentEntity);

            unset($enforcementMotTestResultData['comment']);
        }
        $this->updateResultEntity($reinspectionMotTestResultEntity, $enforcementMotTestResultData);

        $this->entityManager->persist($reinspectionMotTestResultEntity);
        $this->entityManager->flush();

        return ['id' => $reinspectionMotTestResultEntity->getId()];
    }

    /**
     * @param EnforcementMotTestResult $entity
     * @param array                    $data
     */
    private function updateResultEntity(EnforcementMotTestResult $entity, $data)
    {
        $entity->setTotalScore(ArrayUtils::tryGet($data, 'totalScore'));
        $entity->setStep(ArrayUtils::tryGet($data, 'step'));
        $entity->setAwlAdviceGiven(ArrayUtils::tryGet($data, 'awlAdviceGiven'));
        $entity->setAwlImmediateAttention(ArrayUtils::tryGet($data, 'awlImmediateAttention'));
        $entity->setAwlReplyComments(ArrayUtils::tryGet($data, 'awlReplyComments'));
        $entity->setAwlNameAEre(ArrayUtils::tryGet($data, 'awlNameAEre'));
        $entity->setAwlMotRoles(ArrayUtils::tryGet($data, 'awlMotRoles'));
        $entity->setAwlPositionVts(ArrayUtils::tryGet($data, 'awlPositionVts'));
        $entity->setAwlUserId(ArrayUtils::tryGet($data, 'awlUserId'));
        $entity->setComplaintName(ArrayUtils::tryGet($data, 'complaintName'));
        $entity->setComplaintDetail(ArrayUtils::tryGet($data, 'complaintDetail'));
        $entity->setRepairsDetail(ArrayUtils::tryGet($data, 'repairsDetail'));
        $entity->setComplainantAddress(ArrayUtils::tryGet($data, 'complainantAddress'));
        $entity->setComplainantPostcode(ArrayUtils::tryGet($data, 'complainantPostcode'));
        $entity->setComplainantPhoneNumber(ArrayUtils::tryGet($data, 'complainantPhoneNumber'));
        $entity->setVeCompleted(ArrayUtils::tryGet($data, 'veCompleted'));
        $entity->setAgreeVehicleToCertificate(ArrayUtils::tryGet($data, 'agreeVehicleToCertificate'));
        $entity->setAgreeVehicleToFail(ArrayUtils::tryGet($data, 'agreeVehicleToFail'));
        $entity->setInputAgreeVehicleToFail(ArrayUtils::tryGet($data, 'inputAgreeVehicleToFail'));
        $entity->setVehicleSwitch(ArrayUtils::tryGet($data, 'vehicleSwitch'));
        $entity->setInputVehicleSwitch(ArrayUtils::tryGet($data, 'inputVehicleSwitch'));
        $entity->setSwitchPoliceStatusReport(ArrayUtils::tryGet($data, 'switchPoliceStatusReport'));
        $entity->setInputSwitchDetailReport(ArrayUtils::tryGet($data, 'inputSwitchDetailReport'));
        $entity->setSwitchVehicleResult(ArrayUtils::tryGet($data, 'switchVehicleResult'));
        $entity->setInputSwitchPoliceStatusReport(ArrayUtils::tryGet($data, 'inputSwitchPoliceStatusReport'));
        $entity->setInputPromoteSaleInterest(ArrayUtils::tryGet($data, 'inputPromoteSaleInterest'));
        $entity->setVehicleDefects(ArrayUtils::tryGet($data, 'vehicleDefects'));
        $entity->setReasonOfDefects(ArrayUtils::tryGet($data, 'reasonOfDefects'));
        $entity->setItemsDiscussed(ArrayUtils::tryGet($data, 'itemsDiscussed'));
        $entity->setConcludingRemarksTester(ArrayUtils::tryGet($data, 'concludingRemarksTester'));
        $entity->setConcludingRemarksAe(ArrayUtils::tryGet($data, 'concludingRemarksAe'));
        $entity->setConcludingRemarksRecommendation(ArrayUtils::tryGet($data, 'concludingRemarksRecommendation'));
        $entity->setConcludingRemarksName(ArrayUtils::tryGet($data, 'concludingRemarksName'));
        if (ArrayUtils::hasNotEmptyValue($data, 'decisionOutcome')) {
            $outcomeEntity = $this->entityManager
                ->getRepository(EnforcementDecisionOutcome::class)
                ->find($data['decisionOutcome']['id']);
            $entity->setDecisionOutcome($outcomeEntity);
        } else {
            $entity->setDecisionOutcome(null);
        }
    }

    /**
     * Mapped RFRs need to know who tested the RFR, VE or Tester.
     *
     * @param $data
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestExceptionWithMultipleErrors
     */
    public function validateMappedRfrs($data)
    {
        $this->validationErrors = [];

        foreach ($data['mappedRfrs'] as $mappedRfrId => $item) {
            $error = $this->validateMappedRfr($mappedRfrId, $item);

            if ($error) {
                $this->validationErrors[] = $error;
            }
        }

        if (count($this->validationErrors)) {
            throw new BadRequestExceptionWithMultipleErrors([], $this->validationErrors);
        }
    }

    /**
     * @param $data
     * @param $calculatedTotalScore
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestExceptionWithMultipleErrors
     */
    public function validateResults($data, $calculatedTotalScore)
    {
        foreach ($this->getResultValidators($data, $calculatedTotalScore) as $validator) {
            if ($validator->validate() === false) {
                $error = $validator->getError();
                if ($error) {
                    $this->validationErrors[] = $error;
                }
            }
        }

        if (count($this->validationErrors)) {
            throw new BadRequestExceptionWithMultipleErrors([], $this->validationErrors);
        }

        return null;
    }

    /**
     * @param                                               $data
     * @param                                               $user
     * @param                                               $catalog
     * @param \DvsaEntities\Entity\EnforcementMotTestResult $enfMotTestResult
     */
    public function processRfrs($data, $user, $catalog, EnforcementMotTestResult $enfMotTestResult)
    {
        $totalScore = 0;
        foreach ($data['mappedRfrs'] as $mappedRfrId => $item) {
            $testDifference = $this->createEnforcementMotTestDifference(
                $item,
                $mappedRfrId,
                $user,
                $catalog,
                $enfMotTestResult
            );
            $totalScore += (int) $testDifference->getScore()->getScore();
            $this->entityManager->persist($testDifference);
        }

        $enfMotTestResult->setTotalScore($totalScore);
    }

    /**
     * @param $mappedRfrId
     * @param $item
     */
    public function validateMappedRfr($mappedRfrId, $item)
    {
        foreach ($this->getValidators($mappedRfrId, $item) as $validator) {
            if ($validator->validate() === false) {
                return $validator->getError();
            }
        }

        return null;
    }

    /**
     * Extract the Doctrine entity to an array.
     *
     * @param EnforcementMotTestResult $testResult
     *
     * @return array
     */
    protected function extract(EnforcementMotTestResult $testResult)
    {
        $result = $this->objectHydrator->extract($testResult);
        if ($testResult->getComment()) {
            $result['comment'] = $this->objectHydrator->extract($testResult->getComment());
        }
        if ($testResult->getCreatedBy()) {
            $result['createdBy'] = $testResult->getCreatedBy()->getUsername();
        }
        if ($testResult->getLastUpdatedBy()) {
            $result['lastUpdatedBy'] = $testResult->getLastUpdatedBy()->getUsername();
        }
        if ($testResult->getDecisionOutcome()) {
            $result['decisionOutcome'] = $this->objectHydrator->extract($testResult->getDecisionOutcome());
        }
        if ($testResult->getDecisionInspectionOutcome()) {
            $result['decisionInspectionOutcome'] = $this->objectHydrator->extract(
                $testResult->getDecisionInspectionOutcome()
            );
        }
        if ($witnessEntities = $testResult->getEnforcementMotTestResultWitnesses()) {
            $result['enforcementMotTestResultWitnesses'] = [];
            foreach ($witnessEntities as $witnessEntity) {
                $result['enforcementMotTestResultWitnesses'][] = $this->objectHydrator->extract($witnessEntity);
                unset($result['enforcementMotTestResultWitnesses']
                      [key($result['enforcementMotTestResultWitnesses'])]['enforcementMotTestResult']);
            }
        }
        if ($testResult->getCreatedOn()) {
            $result['createdOn'] = DateTimeApiFormat::dateTime($testResult->getCreatedOn());
        }
        if ($testResult->getLastUpdatedOn()) {
            $result['lastUpdatedOn'] = DateTimeApiFormat::dateTime($testResult->getLastUpdatedOn());
        }
        unset($result['motTest']);
        unset($result['reinspectionMotTest']);
        unset($result['testDifferences']);

        $result['motTestInspection'] = $testResult->getMotTestInspection()->getNumber();
        $result['motTestNumber'] = $testResult->getMotTest()->getNumber();

        $testDifferences = $testResult->getTestDifferences();
        $data['testDifferences'] = [];
        foreach ($testDifferences as $testDiff) {
            $data['testDifferences'][] = $this->extractEnforcementMotTestDifference($testDiff);
        }

        // Add in the original MOT test if required
        $originalMotTest = $testResult->getMotTest();
        if ($originalMotTest && !array_key_exists($originalMotTest->getNumber(), $this->motTestCache)) {
            $extractedMotTest = $this->motTestMapper->mapMotTest($originalMotTest);
            $this->motTestCache[$originalMotTest->getNumber()] = $extractedMotTest;
        }

        // Add in the reinspection MOT test if required
        $reinspectionMotTestNumber = $testResult->getMotTestInspection()->getNumber();
        if (!array_key_exists($reinspectionMotTestNumber, $this->motTestCache)) {
            $extractedReinspectionMotTest = $this->motTestMapper->mapMotTest($testResult->getMotTestInspection());
            $this->motTestCache[$reinspectionMotTestNumber] = $extractedReinspectionMotTest;
        }

        $data['motTests'] = $this->motTestCache;
        $data['enforcementResult'] = $result;

        return $data;
    }

    /**
     * Retrieve Mapped RFRs by ID or array of ids - returns an array with the RFR ID as key + entity as value.
     *
     * @param $input
     *
     * @return \DvsaEntities\Entity\MotTestReasonForRejection[]
     */
    protected function getMappedRfrs($input)
    {
        $collection = $this->entityManager->getRepository(\DvsaEntities\Entity\MotTestReasonForRejection::class)
            ->findBy(['id' => $input]);

        return $this->createHashMap($collection);
    }

    protected function getDecisionScores()
    {
        $decisionScores = $this->entityManager->getRepository(\DvsaEntities\Entity\EnforcementDecisionScore::class)->findAll(
        );

        return $this->createHashMap($decisionScores);
    }

    protected function getDecisions()
    {
        $decisions = $this->entityManager->getRepository(\DvsaEntities\Entity\EnforcementDecision::class)->findAll();

        return $this->createHashMap($decisions);
    }

    protected function getDecisionCategories()
    {
        $decisionCategories = $this->entityManager->getRepository(\DvsaEntities\Entity\EnforcementDecisionCategory::class)
            ->findAll();

        return $this->createHashMap($decisionCategories);
    }

    /**
     * Index a collection of object by their Id.
     *
     * @param array $input
     *
     * @return array
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    protected function createHashMap($input)
    {
        if (!is_array($input)) {
            throw new \InvalidArgumentException('input is not an array');
        }
        $output = [];
        foreach ($input as $object) {
            if (is_object($object) && method_exists($object, 'getId')) {
                $output[$object->getId()] = $object;
            } else {
                throw new \Exception('Unable to create hash map');
            }
        }

        return $output;
    }

    /**
     * Helper function to create and populate a new EnforcementMotTestDifference entity.
     *
     * @param array                                         $item
     * @param int                                           $mappedRfrId
     * @param \DvsaEntities\Entity\Person                   $user
     * @param mixed[]                                       $catalog
     * @param \DvsaEntities\Entity\EnforcementMotTestResult $enfMotTestResult
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return \DvsaEntities\Entity\EnforcementMotTestDifference
     *
     * $item Data Structure is posted from the front-end controller: e.g.
     *  array[]
     *   'rfrId' => int, injected in createEnforcementMotTestResult from mappedRfrID
     *   'score' => string '5' (length=1)
     *   'decision' => string '2' (length=1)
     *   'category' => string '3' (length=1)
     *   'justification' => string 'missed brake binding' (length=20)
     */
    protected function createEnforcementMotTestDifference(
        $item,
        $mappedRfrId,
        Person $user,
        $catalog,
        EnforcementMotTestResult $enfMotTestResult
    ) {
        $this->validateItem($item);

        $motTestDifference = new EnforcementMotTestDifference();

        // Find the Mapped RFR object from the rfrId and catalog provided.
        if (!array_key_exists($mappedRfrId, $catalog['mappedRfrs'])) {
            throw new NotFoundException('mappedRfrId', $mappedRfrId);
        }
        $mappedRfr = $catalog['mappedRfrs'][$mappedRfrId];

        // Get the ReasonForRejection entity - this is (unusually) not defined as a relationship in Doctrine.
        $rfr = null;
        if (!empty($item['rfrId'])) {
            $rfr = $this->entityManager->getRepository(\DvsaEntities\Entity\ReasonForRejection::class)
                ->findOneBy(['rfrId' => $item['rfrId']]);
            if (!$rfr instanceof \DvsaEntities\Entity\ReasonForRejection) {
                throw new \Exception('ReasonForRejection', $item['rfrId']);
            }
        }

        // Get the MotTest from the MappedRFR
        /** @var MotTest $motTest */
        $motTest = $mappedRfr->getMotTest();

        // Get the decision score from the posted json and the catalog
        if (!array_key_exists($item['score'], $catalog['decisionScores'])) {
            throw new NotFoundException('DecisionScore', $item['score']);
        }
        $decisionScore = $catalog['decisionScores'][$item['score']];

        // Get the decision from the posted json and catalog
        if (!array_key_exists($item['decision'], $catalog['decisions'])) {
            throw new NotFoundException('Decision', $item['decision']);
        }
        $decision = $catalog['decisions'][$item['decision']];

        // Get the decisionCategory from the posted json and catalog
        if (!array_key_exists($item['category'], $catalog['decisionCategories'])) {
            throw new NotFoundException('Category', $item['category']);
        }
        $decisionCategory = $catalog['decisionCategories'][$item['category']];

        // Set the RFR FK
        $motTestDifference
            ->setRfr($rfr)
            ->setMotTestRfr($mappedRfr)
            ->setMotTest($motTest)
            ->setMotTestType($motTest->getMotTestType())
            ->setScore($decisionScore)
            ->setDecision($decision)
            ->setDecisionCategory($decisionCategory);

        // Set the comment
        $comment = $this->createComment(
            $item['justification'],
            $user
        );
        if ($comment) {
            $motTestDifference->setComment($comment);
        }
        $motTestDifference
            ->setCreatedBy($user)
            ->setLastUpdatedBy($user)
            ->setMotTestResult($enfMotTestResult);

        return $motTestDifference;
    }

    /**
     * Help function to create and populate a Comment.
     *
     * @param string                      $input
     * @param \DvsaEntities\Entity\Person $user
     *
     * @return \DvsaEntities\Entity\Comment;|null
     */
    protected function createComment($input, $user)
    {
        $input = trim($input);
        $comment = null;
        if (strlen($input) > 0) {
            $comment = new Comment();
            $comment->setComment($input);
            $comment->setCommentAuthor($user);
        }

        return $comment;
    }

    /**
     * Extract an EnforcementMotTestDifference to an array.
     *
     * @param EnforcementMotTestDifference $difference
     *
     * @return array
     */
    private function extractEnforcementMotTestDifference(EnforcementMotTestDifference $difference)
    {
        $data = $this->objectHydrator->extract($difference);
        unset($data['rfr']);

        $motTestNumber = $difference->getMotTest()->getNumber();
        if (!array_key_exists($motTestNumber, $this->motTestCache)) {
            $motTest = $this->motTestMapper->mapMotTest($difference->getMotTest());
            $this->motTestCache[$motTestNumber] = $motTest;
        }

        $data['motTest'] = $motTestNumber;
        $data['motTestRfr'] = $this->objectHydrator->extract($difference->getMotTestRfr());
        $data['motTestType'] = $difference->getMotTestType()->getCode();
        $data['score'] = $this->objectHydrator->extract($difference->getScore());
        $data['decision'] = $this->objectHydrator->extract($difference->getDecision());
        $data['decisionCategory'] = $this->objectHydrator->extract($difference->getDecisionCategory());
        $data['comment'] = '';

        if ($difference->getComment()) {
            $data['comment'] = $this->objectHydrator->extract($difference->getComment());
        }
        if ($difference->getCreatedBy()) {
            $data['createdBy'] = $difference->getCreatedBy()->getUsername();
        }
        if ($difference->getLastUpdatedBy()) {
            $data['lastUpdatedBy'] = $difference->getLastUpdatedBy()->getUsername();
        }
        if ($difference->getCreatedOn()) {
            $data['createdOn'] = DateTimeDisplayFormat::dateTime($difference->getCreatedOn());
        }
        if ($difference->getLastUpdatedOn()) {
            $data['lastUpdatedOn'] = DateTimeDisplayFormat::dateTime($difference->getLastUpdatedOn());
        }
        unset($data['motTestResult']);

        return $data;
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function getCatalog($data)
    {
        $catalog = [];
        $catalog['decisionScores'] = $this->getDecisionScores();
        $catalog['decisions'] = $this->getDecisions();
        $catalog['decisionCategories'] = $this->getDecisionCategories();
        $catalog['mappedRfrs'] = $this->getMappedRfrs(array_keys($data['mappedRfrs']));

        return $catalog;
    }

    /**
     * @param $motTestNumber
     *
     * @return MotTest
     */
    private function getMotTestByNumber($motTestNumber)
    {
        /** @var MotTestRepository $repository */
        $repository = $this->entityManager->getRepository(MotTest::class);

        return $repository->getMotTestByNumber($motTestNumber);
    }

    /**
     * @param $username
     *
     * @return null|\DvsaEntities\Entity\Person
     *
     * @throws \DvsaCommonApi\Service\Exception\BadRequestException
     */
    public function getUser($username)
    {
        $user = $this->entityManager->getRepository(Person::class)->findOneBy(
            ['username' => $username]
        );
        if (!$user) {
            throw new BadRequestException('Invalid user', BadRequestException::ERROR_CODE_INVALID_DATA);
        }

        return $user;
    }

    /**
     * @param $outcomeId
     *
     * @return null|object
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getOutcome($outcomeId)
    {
        $outcome = $this->entityManager->getRepository(\DvsaEntities\Entity\EnforcementDecisionOutcome::class)
            ->find($outcomeId);
        if (!$outcome) {
            throw new NotFoundException('EnforcementDecisionOutcome', $outcomeId);
        }

        return $outcome;
    }

    /**
     * @param $comment
     * @param $user
     *
     * @return Comment
     */
    public function createFinalJustificationComment($comment, $user)
    {
        $finalComment = $this->createComment(
            $comment,
            $user
        );

        return $finalComment;
    }

    /**
     * @param $mappedRfrId
     * @param $item
     *
     * @return array
     */
    public function getValidators($mappedRfrId, $item)
    {
        $validators = [];
        $validators[] = new CheckDecisionExistsForScore($mappedRfrId, $item);
        $validators[] = new CheckCategoryExistsForScore($mappedRfrId, $item);
        $validators[] = new CheckDecisionsForCategoryNotApplicable($mappedRfrId, $item);
        $validators[] = new CheckCategoryPleaseSelectForDefect($mappedRfrId, $item);
        $validators[] = new CheckCategoryAllowedForDefectNotApplicable($mappedRfrId, $item);
        $validators[] = new CheckScoreForDefectNotApplicable($mappedRfrId, $item);
        $validators[] = new CheckJustificationForScoreDisregard($mappedRfrId, $item);

        return $validators;
    }

    /**
     * @param $data
     * @param $calculatedTotalScore
     *
     * @return array
     */
    public function getResultValidators($data, $calculatedTotalScore)
    {
        $validators = [];
        $validators[] = new CheckAdvisoryWarningHasJustificationAgainstScore($data, $calculatedTotalScore);
        $validators[] = new CheckDisciplinaryActionHasJustificationAgainstScore($data, $calculatedTotalScore);
        $validators[] = new CheckNoFurtherActionHasJustificationAgainstScore($data, $calculatedTotalScore);

        return $validators;
    }

    /**
     * @param array() $item
     *
     * @throws \InvalidArgumentException
     */
    protected function validateItem(array $item)
    {
        // Check incoming item is an array
        if (!is_array($item)) {
            throw new \InvalidArgumentException('$item should be an array');
        }

        if (!array_key_exists('score', $item)) {
            throw new \InvalidArgumentException('$item does not contain a "score" key');
        }

        if (!array_key_exists('decision', $item)) {
            throw new \InvalidArgumentException('$item does not contain a "decision" key');
        }

        if (!array_key_exists('category', $item)) {
            throw new \InvalidArgumentException('$item does not contain a "category" key');
        }

        if (!array_key_exists('justification', $item)) {
            throw new \InvalidArgumentException('$item does not contain a "justification" key');
        }
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    protected function injectRfrInformation($data)
    {
        $collection = $this->entityManager
            ->getRepository(\DvsaEntities\Entity\MotTestReasonForRejection::class)
            ->findBy(['id' => array_keys($data['mappedRfrs'])]);

        foreach ($collection as $mappedRfr) {
            $data['mappedRfrs'][$mappedRfr->getId()]['rfrId'] =
                $mappedRfr->getReasonForRejection() !== null ? $mappedRfr->getReasonForRejection()->getRfrId() : null;
            $data['mappedRfrs'][$mappedRfr->getId()]['motTestType'] = $mappedRfr->getMotTest()->getMotTestType()
                ->getCode();
        }

        return $data;
    }
}
