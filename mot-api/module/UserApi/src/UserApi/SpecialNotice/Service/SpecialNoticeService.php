<?php
namespace UserApi\SpecialNotice\Service;

use DateTime;
use Doctrine\ORM\EntityManager;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommon\Date\DateTimeApiFormat;
use DvsaCommon\Date\DateTimeHolderInterface;
use DvsaCommon\Date\DateUtils;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaEntities\Entity\Person;
use DvsaEntities\Entity\AuthorisationForTestingMot;
use DvsaEntities\Entity\SpecialNotice;
use DvsaEntities\Entity\SpecialNoticeAudience;
use DvsaEntities\Entity\SpecialNoticeContent;
use UserApi\SpecialNotice\Data\SpecialNoticeAudienceMapper;
use UserApi\SpecialNotice\Service\Validator\SpecialNoticeValidator;
use Zend\Authentication\AuthenticationService;

/**
 * Class SpecialNoticeService
 */
class SpecialNoticeService extends AbstractService
{

    const ISSUE_NUMBER_FORMAT = '%d-%d';

    /** @var \DvsaEntities\Repository\SpecialNoticeRepository */
    private $specialNoticeRepository;
    /** @var \Doctrine\ORM\EntityRepository */
    private $specialNoticeContentRepository;
    /** @var \DoctrineModule\Stdlib\Hydrator\DoctrineObject */
    private $objectHydrator;
    /** @var \DvsaAuthorisation\Service\AuthorisationServiceInterface */
    private $authService;
    /** @var AuthenticationService */
    private $motIdentityProvider;
    /** @var SpecialNoticeValidator $validator */
    private $validator;
    private $dateTimeHolder;

    /**
     * @param EntityManager $entityManager
     * @param DoctrineObject $objectHydrator
     * @param AuthorisationServiceInterface $authService
     * @param AuthenticationService $motIdentityProvider
     * @param SpecialNoticeValidator $validator
     * @param DateTimeHolderInterface $dateTimeHolder
     */
    public function __construct(
        EntityManager $entityManager,
        DoctrineObject $objectHydrator,
        AuthorisationServiceInterface $authService,
        AuthenticationService $motIdentityProvider,
        SpecialNoticeValidator $validator,
        DateTimeHolderInterface $dateTimeHolder
    ) {
        parent::__construct($entityManager);
        $this->specialNoticeRepository = $entityManager->getRepository(SpecialNotice::class);
        $this->specialNoticeContentRepository = $entityManager->getRepository(
            SpecialNoticeContent::class
        );
        $this->objectHydrator = $objectHydrator;
        $this->authService = $authService;
        $this->motIdentityProvider = $motIdentityProvider;
        $this->validator = $validator;
        $this->dateTimeHolder = $dateTimeHolder;
    }

    public function getSpecialNoticeContent($id)
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ);

        /** @var SpecialNoticeContent $specialNoticeContent */
        $specialNoticeContent = $this->findOrThrowException(SpecialNoticeContent::class, $id, 'Special Notice');

        return $this->extractContent($specialNoticeContent);
    }

    public function getSpecialNoticeContentForUser($id)
    {
        if ($this->authService->isGranted(PermissionInSystem::SPECIAL_NOTICE_READ)) {
            $specialNoticeContent = $this->findOrThrowException(SpecialNoticeContent::class, $id, 'Special Notice');
        } else {
            $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT);
            $username = $this->motIdentityProvider->getIdentity()->getUsername();
            $sn = $this->getCurrentSpecialNoticeForUserByContentId($id, $username);
            $specialNoticeContent = $sn->getContent();
        }

        return $this->extractContent($specialNoticeContent);
    }

    public function createSpecialNotice($data)
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_CREATE);

        $specialNoticeContent = $this->mapContent($data);

        $this->entityManager->persist($specialNoticeContent);
        $this->entityManager->flush();

        return $this->extractContent($specialNoticeContent);
    }

    public function update($id, $data)
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_UPDATE);
        /** @var SpecialNoticeContent $specialNoticeContent */
        $specialNoticeContent = $this->findOrThrowException(SpecialNoticeContent::class, $id, 'Special Notice');

        if ($specialNoticeContent->isPublished()) {
            throw new ForbiddenException("This special notice was already published and cannot be updated");
        }

        $this->validator->validate($data);
        $this->removeAudience($specialNoticeContent);
        $specialNoticeContent = $this->mapContent($data, $specialNoticeContent);

        $this->entityManager->persist($specialNoticeContent);
        $this->entityManager->flush();

        return $specialNoticeContent;
    }

    /**
     * @param int $id
     *
     * @return SpecialNoticeContent
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    public function publish($id)
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_CREATE);

        /** @var SpecialNoticeContent $specialNoticeContent */
        $specialNoticeContent = $this->findOrThrowException(SpecialNoticeContent::class, $id, 'Special Notice');

        $specialNoticeContent->setIsPublished(true);

        $this->fillIssueYearAndNumber($specialNoticeContent);

        $this->entityManager->persist($specialNoticeContent);
        $this->entityManager->flush();

        return $specialNoticeContent;
    }

    public function listSpecialNoticesForUser($username)
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ);

        $specialNotices = $this->getAllCurrentSpecialNoticesForUser($username);

        return array_map([$this, 'extract'], $specialNotices);
    }

    public function listCurrentSpecialNoticesForUser($username)
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT);

        $specialNotices = $this->getAllCurrentSpecialNoticesForUser($username);

        return array_map([$this, 'extract'], $specialNotices);
    }

    public function extract(SpecialNotice $specialNotice)
    {
        $specialNoticeData = $this->objectHydrator->extract($specialNotice);

        $contentData = $this->extractContent($specialNotice->getContent());

        unset($contentData['id']);
        unset($specialNoticeData['username']);
        $specialNoticeData['content'] = $contentData;
        $specialNoticeData['isExpired'] = $this->isExpired($specialNotice);

        return $specialNoticeData;
    }

    public function extractContent(SpecialNoticeContent $content)
    {
        $contentData = $this->objectHydrator->extract($content);
        unset($contentData['issueNumberNumber']);
        unset($contentData['issueNumberYear']);
        $contentData['issueNumber'] = !is_null($content->getIssueNumber()) ? sprintf(
            self::ISSUE_NUMBER_FORMAT,
            $content->getIssueNumber(),
            $content->getIssueYear()
        ) : null;
        $contentData['issueDate'] = DateTimeApiFormat::date($content->getIssueDate());
        $contentData['expiryDate'] = DateTimeApiFormat::date($content->getExpiryDate());
        $contentData['internalPublishDate'] = DateTimeApiFormat::date($content->getInternalPublishDate());
        $contentData['externalPublishDate'] = DateTimeApiFormat::date($content->getExternalPublishDate());
        $contentData['isPublished'] = $content->isPublished();
        $contentData['contentId'] = $content->getId();

        $targetRolesData = [];
        foreach ($content->getAudience() as $audience) {
            $targetRolesData[] = SpecialNoticeAudienceMapper::mapToString(
                new SpecialNoticeAudienceMapper($audience->getAudienceId(), $audience->getVehicleClassId())
            );
        }
        $contentData['targetRoles'] = $targetRolesData;

        return $contentData;
    }

    private function mapContent($data, SpecialNoticeContent $specialNoticeContent = null)
    {
        $this->validator->validate($data);
        $issueDate = DateUtils::toDate($data['externalPublishDate']);
        $specialNoticeExpiryDate = DateUtils::toDate($data['externalPublishDate']);

        $specialNoticeExpiryDate->add(new \DateInterval('P' . $data['acknowledgementPeriod'] . 'D'));

        if ($specialNoticeContent == null) {
            $specialNoticeContent = new SpecialNoticeContent();
        }

        $specialNoticeContent->setTitle($data['noticeTitle']);
        $specialNoticeContent->setExpiryDate($specialNoticeExpiryDate);
        $specialNoticeContent->setInternalPublishDate(DateUtils::toDate($data['internalPublishDate']));
        $specialNoticeContent->setExternalPublishDate(DateUtils::toDate($data['externalPublishDate']));
        $specialNoticeContent->setIssueDate($issueDate);
        $specialNoticeContent->setNoticeText($data['noticeText']);

        $this->assignAudience($data['targetRoles'], $specialNoticeContent);

        return $specialNoticeContent;
    }

    public function specialNoticeSummaryForUser($username)
    {
        $overdueCount = 0;
        $unreadCount = 0;
        $acknowledgementDeadlineValue = null;

        if (!$this->authService->isGranted(PermissionInSystem::SPECIAL_NOTICE_READ)) {
            $acknowledgementDeadline = null;

            $specialNotices = $this->getAllCurrentSpecialNoticesForUser($username);

            /** @var SpecialNotice $specialNotice */
            foreach ($specialNotices as $specialNotice) {
                if (!$specialNotice->getIsAcknowledged()) {
                    if ($this->isExpired($specialNotice)) {
                        $overdueCount++;
                    }

                    $expiryDate = $specialNotice->getContent()->getExpiryDate();
                    if ($acknowledgementDeadline == null || $acknowledgementDeadline > $expiryDate) {
                        $acknowledgementDeadline = $expiryDate;
                    }

                    $unreadCount++;
                }
            }

            $acknowledgementDeadlineValue = DateTimeApiFormat::date($acknowledgementDeadline);
        } else {
            $unreadCount = count($this->getAllCurrentSpecialNotices());
        }

        return [
            'overdueCount'            => $overdueCount,
            'unreadCount'             => $unreadCount,
            'acknowledgementDeadline' => $acknowledgementDeadlineValue,
        ];
    }

    public function isUserOverdue(Person $person)
    {
        $specialNotices = $this->getAllCurrentSpecialNoticesForUser($person->getUsername());
        foreach ($specialNotices as $specialNotice) {
            if ($this->isOverdue($specialNotice)) {
                return true;
            }
        }

        return false;
    }

    public function getAllSpecialNotices()
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ);

        $specialNotices = $this->specialNoticeContentRepository->findBy(['isDeleted' => false]);

        return array_map([$this, 'extractContent'], $specialNotices);
    }

    public function getAllCurrentSpecialNotices()
    {
        if($this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT)){
            return [];
        }
        return array_map([$this, 'extractContent'], $this->specialNoticeRepository->getAllCurrentSpecialNotices());
    }

    public function getRemovedSpecialNotices()
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_READ_REMOVED);

        $specialNotices = $this->specialNoticeContentRepository->findBy(['isDeleted' => true]);

        return array_map([$this, 'extractContent'], $specialNotices);
    }

    public function removeSpecialNoticeContent($id)
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_REMOVE);

        $specialNoticeContent = $this->specialNoticeContentRepository->find($id);
        $specialNoticeContent->setIsDeleted(true);
        $this->entityManager->persist($specialNoticeContent);
        $this->specialNoticeRepository->removeSpecialNoticeContent($id);
        $this->entityManager->flush();
    }

    /**
     * @param string $username
     * @return \DvsaEntities\Entity\SpecialNotice[]
     */
    protected function getAllCurrentSpecialNoticesForUser($username)
    {
        return $this->specialNoticeRepository->getAllCurrentSpecialNoticesForUser($username);
    }

    /**
     * @param int $id
     * @param string $username
     * @return SpecialNotice
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    protected function getCurrentSpecialNoticeForUser($id, $username)
    {
        return $this->specialNoticeRepository->getCurrentSpecialNoticeForUser($id, $username);
    }

    /**
     * @param int $contentId
     * @param string $username
     * @return SpecialNotice
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     */
    protected function getCurrentSpecialNoticeForUserByContentId($contentId, $username)
    {
        return $this->specialNoticeRepository->getCurrentSpecialNoticeForUserByContentId($contentId, $username);
    }

    protected function isOverdue(SpecialNotice $specialNotice)
    {
        return $this->isExpired($specialNotice) && !$specialNotice->getIsAcknowledged();
    }

    public function isExpired(SpecialNotice $specialNotice)
    {
        return $specialNotice->getContent()->getExpiryDate() < new \DateTime();
    }

    public function markAcknowledged($id)
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_ACKNOWLEDGE);
        $username = $this->motIdentityProvider->getIdentity()->getUsername();
        $specialNotice = $this->getCurrentSpecialNoticeForUser($id, $username);
        $specialNotice->markAcknowledged();
        $personRepository = $this->entityManager->getRepository(Person::class);
        $person = $personRepository->findOneBy(['username' => $specialNotice->getUsername()]);

        $this->entityManager->persist($person);
        $this->entityManager->flush();
    }

    public function generateIssueNumber($year = null)
    {
        if(is_null($year)){
            $year = $this->dateTimeHolder->getCurrent()->format('Y');
        }
        $latestIssueNumberResult = $this->specialNoticeRepository->getLatestIssueNumber($year);

        $latestIssueNumber = current($latestIssueNumberResult);

        if ($latestIssueNumber) {
            return ++$latestIssueNumber;
        } else {
            return 1;
        }
    }

    /**
     * This method adds special notices for users based on defined special
     *  notice content roles, user roles and existing special notice rows.
     *
     */
    public function addNewSpecialNotices()
    {
        $this->authService->assertGranted(PermissionInSystem::SPECIAL_NOTICE_BROADCAST);
        $personId = $this->motIdentityProvider->getIdentity()->getUserId();

        $this->specialNoticeRepository->addNewSpecialNotices($personId);
    }

    /**
     * @param array                $targetRoles
     * @param SpecialNoticeContent $specialNoticeContent
     */
    private function assignAudience(array $targetRoles, SpecialNoticeContent $specialNoticeContent)
    {
        foreach ($targetRoles as $value) {
            /** @var SpecialNoticeAudienceMapper $audienceVehicleClass */
            $audienceVehicleClass = SpecialNoticeAudienceMapper::mapToObject($value);

            $sna = new SpecialNoticeAudience();
            $sna->setAudienceId($audienceVehicleClass->getAudienceId());

            $vehicleClass = $audienceVehicleClass->getVehicleClassId();

            if ($vehicleClass) {
                $sna->setVehicleClassId($vehicleClass);
            }

            $specialNoticeContent->addSpecialNoticeAudience($sna);
        }
    }

    /**
     * @param SpecialNoticeContent $specialNoticeContent
     */
    private function removeAudience(SpecialNoticeContent $specialNoticeContent)
    {
        $this->specialNoticeRepository->removeEntities($specialNoticeContent->getAudience());
        $specialNoticeContent->clearSpecialNoticeAudience();
    }

    /**
     * @return array
     */
    public function getAmountOfOverdueSpecialNoticesForClasses()
    {
        if(!$this->authService->isGranted(PermissionInSystem::SPECIAL_NOTICE_READ_CURRENT)){
            return [];
        }

        $username = $this->motIdentityProvider->getIdentity()->getUsername();
        return $this->specialNoticeRepository->getAmountOfOverdueSpecialNoticesForClasses($username);
    }

    /**
     * @param $vehicleClass
     * @return int
     */
    public function countOverdueSpecialNoticesForClass($vehicleClass)
    {
        $overdueSpecialNotices = $this->getAmountOfOverdueSpecialNoticesForClasses();
        return $overdueSpecialNotices[$vehicleClass];
    }

    private function fillIssueYearAndNumber(SpecialNoticeContent $specialNoticeContent)
    {
        if (is_null($specialNoticeContent->getIssueNumber()) && !is_null($specialNoticeContent->getExternalPublishDate())) {
            $publishYear = $specialNoticeContent->getExternalPublishDate()->format('Y');
            $specialNoticeContent->setIssueNumber($this->generateIssueNumber($publishYear));
            $specialNoticeContent->setIssueYear($publishYear);
        }
        return $specialNoticeContent;
    }
}
