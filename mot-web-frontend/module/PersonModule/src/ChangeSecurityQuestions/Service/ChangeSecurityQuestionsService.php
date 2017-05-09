<?php

namespace Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\Service;

use Dvsa\Mot\Frontend\PersonModule\ChangeSecurityQuestions\ViewModel\ChangeSecurityQuestionsSubmissionModel;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\HttpRestJson\Client;

class ChangeSecurityQuestionsService
{
    const UPDATE_ROUTE = 'security-question/%s';

    /** @var MapperFactory $mapperFactory */
    private $mapperFactory;

    /** @var Client $client */
    private $client;

    private $identityProvider;

    public function __construct(MapperFactory $mapperFactory, Client $client, MotIdentityProviderInterface $identityProvider)
    {
        $this->mapperFactory = $mapperFactory;
        $this->client = $client;
        $this->identityProvider = $identityProvider;
    }

    /**
     * @return \DvsaClient\Entity\SecurityQuestionSet
     */
    public function getSecurityQuestions()
    {
        return $this->mapperFactory->SecurityQuestion->fetchAllGroupedAndOrdered();
    }

    /**
     * @param ChangeSecurityQuestionsSubmissionModel $model
     *
     * @return bool
     */
    public function updateSecurityQuestions(ChangeSecurityQuestionsSubmissionModel $model)
    {
        $userId = $this->identityProvider->getIdentity()->getUserId();

        $this->client->put(
            sprintf(self::UPDATE_ROUTE, $userId),
            [
                [
                    'questionId' => $model->getQuestionOneId(),
                    'answer' => $model->getQuestionOneAnswer(),
                ],
                [
                    'questionId' => $model->getQuestionTwoId(),
                    'answer' => $model->getQuestionTwoAnswer(),
                ],
            ]
        );

        return true;
    }
}
