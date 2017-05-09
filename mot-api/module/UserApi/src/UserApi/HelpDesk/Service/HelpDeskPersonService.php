<?php

namespace UserApi\HelpDesk\Service;

use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaAuthorisation\Service\UserRoleService;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Dto\Person\PersonHelpDeskProfileDto;
use DvsaCommon\Dto\Person\SearchPersonResultDto;
use DvsaCommon\Model\SearchPersonModel;
use DvsaEntities\Repository\PersonRepository;
use UserApi\HelpDesk\Mapper\PersonHelpDeskProfileMapper;
use UserApi\HelpDesk\Service\Validator\SearchPersonValidator;
use DvsaCommonApi\Service\Exception\TooFewResultsException;
use DvsaCommonApi\Service\Exception\TooManyResultsException;

/**
 * Searching person account and showing profile.
 */
class HelpDeskPersonService
{
    const MAX_RESULT_COUNT = 10;
    const MAX_RESULT_COUNT_EXTENDED = 100;

    /**
     * @var PersonRepository
     */
    private $personRepository;
    /**
     * @var AuthorisationServiceInterface
     */
    private $authorisationService;
    /**
     * @var SearchPersonValidator
     */
    private $searchPersonValidator;
    /**
     * @var PersonHelpDeskProfileMapper
     */
    private $personHelpDeskProfileMapper;

    public function __construct(
        PersonRepository $personRepository,
        AuthorisationServiceInterface $authorisationService,
        UserRoleService $userRoleService,
        SearchPersonValidator $searchPersonValidator,
        PersonHelpDeskProfileMapper $personHelpDeskProfileMapper
    ) {
        $this->personRepository = $personRepository;
        $this->authorisationService = $authorisationService;
        $this->userRoleService = $userRoleService;
        $this->searchPersonValidator = $searchPersonValidator;
        $this->personHelpDeskProfileMapper = $personHelpDeskProfileMapper;
    }

    /**
     * @param SearchPersonModel $searchPersonModel
     *
     * @return \DvsaCommon\Dto\Person\SearchPersonResultDto[]
     *
     * @throws TooFewResultsException
     * @throws TooManyResultsException
     */
    public function search(SearchPersonModel $searchPersonModel)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::USER_SEARCH);

        if ($searchPersonModel->getTown() !== null) {
            $this->authorisationService->assertGranted(PermissionInSystem::USER_SEARCH_EXTENDED);
        }

        $this->searchPersonValidator->validate($searchPersonModel);
        $personData = $this->personRepository->searchAll($searchPersonModel);

        $userSearchExtended = $this->authorisationService->isGranted(PermissionInSystem::USER_SEARCH_EXTENDED);
        if ($userSearchExtended) {
            $maxResults = self::MAX_RESULT_COUNT_EXTENDED;
        } else {
            $maxResults = self::MAX_RESULT_COUNT;
        }

        if (count($personData) === 0) {
            throw new TooFewResultsException(
                'Your search returned no results. Add more details and try again.'
            );
        } elseif (count($personData) > $maxResults) {
            throw new TooManyResultsException(
                'Your search returned too many results. Add more details and try again.'
            );
        }

        return SearchPersonResultDto::getList($personData);
    }

    /**
     * @param $personId
     * @param bool|true $restricted
     *
     * @return PersonHelpDeskProfileDto
     *
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function getPersonProfile($personId, $restricted = true)
    {
        $this->authorisationService->assertGranted(PermissionInSystem::VIEW_OTHER_USER_PROFILE);

        $person = $this->personRepository->get($personId);
        $mapper = $this->personHelpDeskProfileMapper->fromPersonEntityToDto($person);

        if (!$restricted) {
            $mapper->setRoles(
                $this->userRoleService->getDetailedRolesForPerson($person)
            );
            $this->personHelpDeskProfileMapper->mapAuthenticationMethod($person->getAuthenticationMethod(), $mapper);
        }

        return $mapper;
    }
}
