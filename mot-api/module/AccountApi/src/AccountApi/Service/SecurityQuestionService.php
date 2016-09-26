<?php

namespace AccountApi\Service;

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
 * Class SecurityQuestionService
 * @package UserApi\SecurityQuestion\Service
 */
class SecurityQuestionService extends AbstractPersistableService
{
    const ERR_TYPE_INVALID = 'Incorrect argument types, expected: int, string';

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
    /** @var ParamObfuscator  */
    private $obfuscator;

    /**
     * @param SecurityQuestionRepository $securityQuestionRepository
     * @param SecurityQuestionMapper $mapper
     * @param PersonSecurityAnswerRecorder $personSecurityAnswerRecorder
     * @param PersonRepository $personRepository
     * @param PersonSecurityAnswerRepository $personSecurityAnswerRepository
     * @param PersonSecurityAnswerValidator $personSecurityAnswerValidator
     * @param ParamObfuscator $obfuscator,
     * @param EntityManager $entityManager
     */
    public function __construct(
        SecurityQuestionRepository $securityQuestionRepository,
        SecurityQuestionMapper $mapper,
        PersonSecurityAnswerRecorder $personSecurityAnswerRecorder,
        PersonRepository $personRepository,
        PersonSecurityAnswerRepository $personSecurityAnswerRepository,
        PersonSecurityAnswerValidator $personSecurityAnswerValidator,
        ParamObfuscator $obfuscator,
        EntityManager $entityManager
    ) {
        parent::__construct($entityManager);

        $this->securityQuestionRepository = $securityQuestionRepository;
        $this->mapper = $mapper;
        $this->personSecurityAnswerRecorder = $personSecurityAnswerRecorder;
        $this->personRepository = $personRepository;
        $this->personSecurityAnswerRepository = $personSecurityAnswerRepository;
        $this->personSecurityAnswerValidator = $personSecurityAnswerValidator;
        $this->obfuscator = $obfuscator;
    }

    /**
     *
     * @return \DvsaCommon\Dto\Security\SecurityQuestionDto[]
     */
    public function getAll()
    {
        $questions = $this->securityQuestionRepository->findAll();
        return $this->mapper->manyToDto($questions);
    }

    /**
     * @param integer $questionId      is the database id of the security question to be tested with an answer.
     * @param integer $userId          the database id of the user on the phone
     * @param string  $answer contains the candidate answer as submitted.
     *
     * @return true or false, true meaning the answer is deemed correct.
     * @throws \Exception when required for duff data etc.
     */
    public function isAnswerCorrect($questionId, $userId, $answer)
    {
        if (is_integer($questionId) && is_integer($userId) && is_string($answer)) {
            return $this->securityQuestionRepository->isAnswerCorrect($questionId, $userId, $answer);
        }

        throw new \Exception(self::ERR_TYPE_INVALID);
    }

    /**
     * @param int $questionNumber   The zero based question number (0 => question1; 1 => question2)
     * @param int $userId           integer the database id of the user on the phone
     *
     * @return SecurityQuestionDto The security question of a person.
     * @throws \Exception when required for duff data etc.
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
}
