<?php

namespace SiteApi\Service;

use Doctrine\ORM\EntityManager;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionAtSite;
use DvsaCommon\Database\Transaction;
use DvsaCommon\Enum\BusinessRoleStatusCode;
use DvsaCommonApi\Service\Exception\NotFoundException;
use DvsaEntities\Entity\Site;
use DvsaEntities\Entity\SiteBusinessRole;
use DvsaEntities\Entity\SiteBusinessRoleMap;
use SiteApi\Model\Operation\NominateOperation;
use Zend\Authentication\AuthenticationService;

/**
 * Class NominateRoleService
 *
 * @package SiteApi\Service
 */
class NominateRoleService
{

    /** @var  EntityManager $entityManager */
    protected $entityManager;

    /** @var  AuthenticationService $authenticationService */
    private $authenticationService;

    /**
    * @var  AbstractMotAuthorisationService $authorisationService
    */
    private $authorisationService;

    private $nominateOperation;
    private $transaction;

    public function __construct(
        EntityManager $entityManager,
        AuthenticationService $authenticationService,
        AuthorisationServiceInterface $authorisationService,
        NominateOperation $nominateOperation,
        Transaction $transaction
    ) {
        $this->entityManager = $entityManager;
        $this->authenticationService = $authenticationService;
        $this->authorisationService = $authorisationService;
        $this->nominateOperation = $nominateOperation;
        $this->transaction = $transaction;
    }

    /**
     * @param $siteId
     * @param $nomineeId
     * @param $roleCode
     *
     * @return SiteBusinessRoleMap
     */
    public function nominateRole($siteId, $nomineeId, $roleCode)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::NOMINATE_ROLE_AT_SITE, $siteId);

        $nominator = $this->getNominator();
        $nomination = $this->getNomination($nomineeId, $roleCode, $siteId);

        $sitePosition = $this->nominateOperation->nominate($nominator, $nomination);

        $this->transaction->flush();

        return $sitePosition;
    }

    /**
     * @param $siteId
     * @param $nomineeId
     * @param $roleCode
     * @return SiteBusinessRoleMap
     * @throws \Exception
     */
    public function updateRoleNominationNotification($siteId, $nomineeId, $roleCode)
    {
        $roleId = $this->getRole($roleCode)->getId();

        /**
         * @var SiteBusinessRoleMap $siteBusinessRoleMap
         */
        $siteBusinessRoleMap = $this->getSiteBusinessRoleMap($siteId, $nomineeId, $roleId);

        if (!$siteBusinessRoleMap) {
            throw new \Exception('Site Business role map not found');
        }

        $nominator = $siteBusinessRoleMap->getCreatedBy();

        return $this->nominateOperation->sendUpdatedNominationNotification($nominator, $siteBusinessRoleMap);
    }

    /**
     * @param $siteId
     * @param $nomineeId
     * @param $roleCode
     *
     * @return SiteBusinessRoleMap
     */
    public function verifyNomination($siteId, $nomineeId, $roleCode)
    {
        $this->authorisationService->assertGrantedAtSite(PermissionAtSite::NOMINATE_ROLE_AT_SITE, $siteId);

        $nomination = $this->getNomination($nomineeId, $roleCode, $siteId);
        $this->nominateOperation->verifyNomination($nomination);

        return true;
    }

    private function getNomination($nomineeId, $roleCode, $siteId)
    {
        $role = $this->getRole($roleCode);
        $site = $this->getSite($siteId);
        $nominee = $this->getNominee($nomineeId);
        if (!$nominee) {
            throw new NotFoundException('Person ' . $nomineeId . ' not found');
        }
        $status = $this->getStatus(BusinessRoleStatusCode::PENDING);

        $map = new SiteBusinessRoleMap();
        $map->setSite($site)
            ->setSiteBusinessRole($role)
            ->setPerson($nominee)
            ->setBusinessRoleStatus($status);
        return $map;
    }

    protected function getStatus($code)
    {
        return $this->entityManager->getRepository(\DvsaEntities\Entity\BusinessRoleStatus::class)->findOneBy(
            ['code' => $code]
        );
    }

    private function getNominator()
    {
        return $this->authenticationService->getIdentity()->getPerson();
    }

    private function getNominee($nomineeId)
    {
        return $this->entityManager->getRepository(\DvsaEntities\Entity\Person::class)->findOneBy(['id' => $nomineeId]);
    }

    /**
     * @param $roleCode
     * @return SiteBusinessRole
     */
    private function getRole($roleCode)
    {
        return $this->entityManager->getRepository(SiteBusinessRole::class)->findOneBy(['code' => $roleCode]);
    }

    /**
     * @param $siteId
     * @return Site
     */
    private function getSite($siteId)
    {
        return $this->entityManager->getRepository(Site::class)->findOneBy(['id' => $siteId]);
    }

    /**
     * @param $nomineeId
     * @param $roleId
     * @param $siteId
     * @return SiteBusinessRoleMap
     */
    private function getSiteBusinessRoleMap($siteId, $nomineeId, $roleId)
    {
        return $this->entityManager->getRepository(SiteBusinessRoleMap::class)
            ->findOneBy(
                [
                    'site' => $siteId,
                    'person' => $nomineeId,
                    'siteBusinessRole' => $roleId,
                    'businessRoleStatus' => $this->getStatus(BusinessRoleStatusCode::PENDING)
                ]
            );
    }
}
