<?php

namespace UserApi\Application\Service;

use Doctrine\ORM\EntityManager;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommonApi\Service\AbstractService;
use DvsaEntities\Entity\Application;
use DvsaMotApi\Service\UserService;

/**
 * Class ApplicationService.
 */
class ApplicationService extends AbstractService
{
    private $userService;

    public function __construct(EntityManager $entityManager, UserService $userService)
    {
        parent::__construct($entityManager);

        $this->userService = $userService;
    }

    public function getApplicationsForUser($userId)
    {
        $user = $this->userService->get($userId);

        $applications = $this->entityManager
            ->getRepository(Application::class)
            ->findBy(
                ['person' => $user],
                ['submittedOn' => 'DESC']
            );

        $result = [];

        /** @var $application Application */
        foreach ($applications as $application) {
            $result[] = [
                'id' => $application->getId(),
                'uuid' => $application->getApplicationReference(),
                'submitDate' => DateTimeApiFormat::dateTime($application->getSubmittedOn()),
                'status' => $application->getStatus()->getName(),
            ];
        }

        return $result;
    }
}
