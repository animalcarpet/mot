<?php
/**
 * This file is part of the DVSA MOT API project.
 *
 * @link https://github.com/dvsa/mot
 */

namespace AccountApi\Service;

use AccountApi\Crypt\SecurityAnswerHashFunction;
use AccountApi\Mapper\SecurityQuestionMapper;
use AccountApi\Service\Validator\PersonSecurityAnswerValidator;
use Doctrine\ORM\EntityManager;
use Dvsa\Mot\Api\RegistrationModule\Service\AbstractPersistableService;
use Dvsa\Mot\Api\RegistrationModule\Service\PersonSecurityAnswerRecorder;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\Obfuscate\ParamObfuscator;
use DvsaEntities\Repository\PersonRepository;
use DvsaEntities\Repository\PersonSecurityAnswerRepository;
use DvsaEntities\Repository\SecurityQuestionRepository;

/**
 * Class SecurityQuestionService.
 */
class SecurityQuestionService extends AbstractPersistableService
{
    const ERR_TYPE_INVALID = 'Incorrect argument types, expected: int, string';

    const ERR_TYPE_PERSON_ID = 'personId must be a positive number, received %s';

    const ERR_MSG_INVALID_ARGUMENT = 'Questions and answers must be passed as an array '.
    'containing question id and answer pairs, received : %s';

    /** @var SecurityQuestionRepository */
    private $securityQuestionRepository;

    /** @var SecurityQuestionMapper */
    private $mapper;

    /** @var PersonSecurityAnswerRecorder */
    private $personSecurityAnswerRecorder;

    /** @var PersonRepository */
    private $personRepository;

    /** @var PersonSecurityAnswerRepository */
    private $personSecurityAnswerRepository;

    /** @var PersonSecurityAnswerValidator */
    private $personSecurityAnswerValidator;

    /** @var ParamObfuscator */
    private $obfuscator;

    /**
     * @var SecurityAnswerHashFunction
     */
    private $hashFunction;

    /**
     * the delay in second which we will cause on each fail attempt.
     *
     * @var int
     */
    private $delay;

    /**
     * SecurityQuestionService constructor.
     *
     * @param SecurityQuestionRepository     $securityQuestionRepository
     * @param SecurityQuestionMapper         $mapper
     * @param PersonSecurityAnswerRecorder   $personSecurityAnswerRecorder
     * @param PersonRepository               $personRepository
     * @param PersonSecurityAnswerRepository $personSecurityAnswerRepository
     * @param PersonSecurityAnswerValidator  $personSecurityAnswerValidator
     * @param ParamObfuscator                $obfuscator
     * @param EntityManager                  $entityManager
     * @param SecurityAnswerHashFunction     $securityAnswerHashFunction
     * @param int                            $delayOnFailedVerification
     */
    public function __construct(
        SecurityQuestionRepository $securityQuestionRepository,
        SecurityQuestionMapper $mapper,
        PersonSecurityAnswerRecorder $personSecurityAnswerRecorder,
        PersonRepository $personRepository,
        PersonSecurityAnswerRepository $personSecurityAnswerRepository,
        PersonSecurityAnswerValidator $personSecurityAnswerValidator,
        ParamObfuscator $obfuscator,
        EntityManager $entityManager,
        SecurityAnswerHashFunction $securityAnswerHashFunction,
        $delayOnFailedVerification
    ) {
        parent::__construct($entityManager);

        $this->securityQuestionRepository = $securityQuestionRepository;
        $this->mapper = $mapper;
        $this->personSecurityAnswerRecorder = $personSecurityAnswerRecorder;
        $this->personRepository = $personRepository;
        $this->personSecurityAnswerRepository = $personSecurityAnswerRepository;
        $this->personSecurityAnswerValidator = $personSecurityAnswerValidator;
        $this->obfuscator = $obfuscator;
        $this->hashFunction = $securityAnswerHashFunction;
        $this->delay = $delayOnFailedVerification;
    }

    /**
     * @return SecurityQuestionDto[]
     */
    public function getAll()
    {
        $questions = $this->securityQuestionRepository->findAll();

        return $this->mapper->manyToDto($questions);
    }

    /**
     * @param int   $personId            the database id of the user on the phone
     * @param array $questionsAndAnswers contains the candidate answer as submitted
     *
     * @return bool[]
     *
     * @throws \InvalidArgumentException
     */
    public function verifySecurityAnswersForPerson($personId, $questionsAndAnswers)
    {
        if (!is_numeric($personId) || $personId < 0) {
            throw new \InvalidArgumentException(sprintf(self::ERR_TYPE_PERSON_ID, var_export($personId, true)));
        }

        if (!$this->areQuestionsAndAnswersTypeValid($questionsAndAnswers)) {
            throw new \InvalidArgumentException(
                sprintf(self::ERR_MSG_INVALID_ARGUMENT, var_export($questionsAndAnswers, true))
            );
        }

        foreach ($questionsAndAnswers as $questionId => $answer) {
            $recordedAnswer = $this->personSecurityAnswerRepository->getPersonAnswerForQuestion($personId, $questionId);

            if (is_null($recordedAnswer) || !$this->hashFunction->verify($answer, $recordedAnswer->getAnswer())) {
                $questionsAndAnswers[$questionId] = false;
            } else {
                $questionsAndAnswers[$questionId] = true;
            }
        }

        if (!array_product($questionsAndAnswers)) {
            sleep($this->delay);
        }

        return $questionsAndAnswers;
    }

    /**
     * @param int $questionNumber The zero based question number (0 => question1; 1 => question2)
     * @param int $userId         integer the database id of the user on the phone
     *
     * @return SecurityQuestionDto The security question of a person
     *
     * @throws \Exception when required for duff data etc
     */
    public function findQuestionByQuestionNumber($questionNumber, $userId)
    {
        if (is_integer($questionNumber) && is_integer($userId)) {
            return $this->mapper->toDto(
                $this->securityQuestionRepository
                    ->findQuestionByQuestionNumber(
                        $questionNumber,
                        $userId
                    )
            );
        }

        throw new \Exception(self::ERR_TYPE_INVALID);
    }

    /**
     * @param int $personId
     *
     * @return SecurityQuestionDto[]
     *
     * @throws \InvalidArgumentException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \DvsaCommonApi\Service\Exception\TooFewResultsException
     * @throws \DvsaCommonApi\Service\Exception\TooManyResultsException
     */
    public function getQuestionsForPerson($personId)
    {
        if (!is_numeric($personId) || $personId < 0) {
            throw new \InvalidArgumentException(sprintf(self::ERR_TYPE_PERSON_ID, var_export($personId, true)));
        }

        return $this->mapper->manyToDto(
            $this->securityQuestionRepository->findQuestionsByPersonId($personId)
        );
    }

    public function updateAnswersForUser($userId, $answersData)
    {
        $this->personSecurityAnswerValidator->validate($answersData);

        $person = $this->personRepository->find($userId);

        foreach ($person->getSecurityAnswers() as $oldSecurityAnswer) {
            $this->personSecurityAnswerRepository->remove($oldSecurityAnswer);
        }

        $answers = [];
        foreach ($answersData as $datum) {
            $answers[] = $this->personSecurityAnswerRecorder->create($person, $datum['questionId'], $datum['answer']);
        }

        $person->replaceSecurityAnswers($answers);
        $this->save($person);

        return true;
    }

    /**
     * @deprecated we will going to remove this method, once we played upcoming stories to combine security answer pages
     *             in the "lost or forgotten card" journey
     *
     * @param $questionId
     * @param $userId
     * @param $answer
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function isAnswerCorrect($questionId, $userId, $answer)
    {
        if (is_integer($questionId) && is_integer($userId) && is_string($answer)) {
            return $this->securityQuestionRepository->isAnswerCorrect($questionId, $userId, $answer);
        }

        throw new \Exception(self::ERR_TYPE_INVALID);
    }

    /**
     * @param array $questionsAndAnswers
     *
     * @return bool
     */
    private function areQuestionsAndAnswersTypeValid($questionsAndAnswers)
    {
        if (!is_array($questionsAndAnswers)) {
            return false;
        }

        foreach ($questionsAndAnswers as $questionId => $answers) {
            if (!is_numeric($questionId) || $questionId < 1) {
                return false;
            }

            if (!is_string($answers)) {
                return false;
            }
        }

        return true;
    }
}
