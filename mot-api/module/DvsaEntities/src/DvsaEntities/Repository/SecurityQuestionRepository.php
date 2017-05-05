<?php
namespace DvsaEntities\Repository;

use AccountApi\Crypt\SecurityAnswerHashFunction;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaCommonApi\Service\Exception\TooFewResultsException;
use DvsaCommonApi\Service\Exception\TooManyResultsException;
use DvsaEntities\Entity\PersonSecurityAnswer;
use DvsaEntities\Entity\SecurityQuestion;

/**
 * Class SecurityQuestionRepository.
 */
class SecurityQuestionRepository extends AbstractMutableRepository
{
    const EXPECTED_NUMBER_OF_QUESTIONS_PER_USER = 2;

    /**
     * @return SecurityQuestion[]
     */
    public function findAll()
    {
        $questions = parent::findAll();

        return $questions;
    }

    /**
     * @param array $ids
     *
     * @return array
     */
    public function findAllByIds(array $ids)
    {
        return $this
            ->getEntityManager()
            ->createQuery(sprintf('SELECT sq FROM %s sq INDEX BY sq.id WHERE sq.id IN (:ids)', $this->getEntityName()))
            ->setParameter('ids', $ids)
            ->getResult();
    }

    /**
     * Find the security question attached to a person
     *
     * @param integer $questionNumber   the zero based question number (0 => question1; 1 => question2)
     * @param integer $userId           the database Id of the user presenting the answer
     *
     * @return SecurityQuestion
     * @throws NotFoundException
     */
    public function findQuestionByQuestionNumber($questionNumber, $userId)
    {
        /* @var \DvsaEntities\Entity\PersonSecurityAnswer[] $results */
        $results = $this->_em
            ->getRepository(PersonSecurityAnswer::class)
            ->findBy(['person' => $userId], ['id' => 'ASC'], 1, $questionNumber);

        if (count($results) !== 1) {
            throw new NotFoundException('Question not found');
        }
        return $results[0]->getSecurityQuestion();
    }

    /**
     * @param integer $personId
     * @return array
     * @throws NotFoundException
     * @throws TooFewResultsException
     * @throws TooManyResultsException
     */
    public function findQuestionsByPersonId($personId)
    {
        /* @var \DvsaEntities\Entity\PersonSecurityAnswer[] $results */
        $securityAnswers = $this->getEntityManager()->getRepository(PersonSecurityAnswer::class)->findBy(
            ['person' => $personId]
        );

        $securityAnswersCount = count($securityAnswers);

        if (empty($securityAnswers)) {
            throw new NotFoundException('No question has been found');
        }

        if ($securityAnswersCount < self::EXPECTED_NUMBER_OF_QUESTIONS_PER_USER) {
            throw new TooFewResultsException('Too few security questions have been fetched');
        }

        if ($securityAnswersCount > self::EXPECTED_NUMBER_OF_QUESTIONS_PER_USER) {
            throw new TooManyResultsException('Too many security questions have been fetched');
        }

        $securityQuestions = [];

        /** @var \DvsaEntities\Entity\PersonSecurityAnswer $securityAnswer */
        foreach ($securityAnswers as $securityAnswer) {
            $securityQuestions[] = $securityAnswer->getSecurityQuestion();
        }

        return $securityQuestions;
    }

    /**
     * @deprecated we will going to remove this method, once we played upcoming stories to combine security answer pages
     *             in the "lost or forgotten card" journey
     *
     * Find a SINGLE matching tuple of (question-id, user-id) and iff there is one, we can
     * then check that the correct answer has been submitted.
     *
     * @param integer $question the database Id of the security question being asked
     * @param integer $userId   the database Id of the user presenting the answer
     * @param string  $answer   the plain text response they gave to the SCSO over the phone
     *
     * @return bool
     */
    public function isAnswerCorrect($question, $userId, $answer)
    {
        /* @var \DvsaEntities\Entity\PersonSecurityAnswer $match */
        $match = $this->_em
            ->getRepository(PersonSecurityAnswer::class)
            ->findOneBy(['person' => $userId, 'securityQuestion' => $question]);

        if ($match !== null) {
            return (new SecurityAnswerHashFunction())
                ->verify(
                    $answer,
                    $match->getAnswer()
                );
        }
        return false;
    }
}
