<?php
namespace NotificationApi\Service;

use Doctrine\ORM\EntityManager;
use Dvsa\Mot\ApiClient\Service\AuthorisationService;
use DvsaAuthorisation\Service\AuthorisationServiceInterface;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use DvsaCommon\Auth\PermissionInSystem;
use DvsaCommonApi\Service\AbstractService;
use DvsaCommonApi\Service\Exception\BadRequestException;
use DvsaCommonApi\Service\Exception\ForbiddenException;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationField;
use DvsaEntities\Entity\NotificationTemplate;
use DvsaEntities\Entity\Person;
use NotificationApi\Dto\Notification as NotificationDto;
use NotificationApi\Service\BusinessLogic\AbstractNotificationActionHandler;
use NotificationApi\Service\Validator\NotificationValidator;
use DvsaEntities\Repository\NotificationRepository;
use Zend\ServiceManager\ServiceManager;

/**
 * Class NotificationService
 *
 * @package NotificationApi\Service
 */
class NotificationService extends AbstractService
{

    /** @var $validator NotificationValidator */
    private $validator;

    /** @var $serviceManager ServiceManager */
    private $serviceManager;

    /** @var  $authService AuthorisationServiceInterface */
    private $authService;

    /** @var  NotificationRepository */
    private $notificationRepository;

    public function __construct(
        ServiceManager $serviceManager,
        NotificationValidator $validator,
        NotificationRepository $notificationRepository
    ) {
        parent::__construct($serviceManager->get(EntityManager::class));
        $this->validator = $validator;
        $this->serviceManager = $serviceManager;
        $this->authService = $serviceManager->get('DvsaAuthorisationService');
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * @param array $data
     *
     * @return int notificationId
     */
    public function add($data)
    {
        if ($data instanceof NotificationDto) {
            $data = $data->toArray();
        }

        $this->validator->validate($data);
        /** @var $person Person */
        $person = $this->findOrThrowException(
            Person::class,
            $data['recipient'],
            Person::ENTITY_NAME
        );
        /** @var $template NotificationTemplate */
        $template = $this->findOrThrowException(
            NotificationTemplate::class,
            $data['template'],
            NotificationTemplate::ENTITY_NAME
        );

        $notification = new Notification();
        $notification->setRecipient($person)
                     ->setNotificationTemplate($template);

        foreach ($data['fields'] as $field => $value) {
            $notificationField = new NotificationField();
            $notificationField->setField($field)->setValue($value)->setNotification($notification);
            $notification->addField($notificationField);
        }

        $this->notificationRepository->save($notification);

        return $notification->getId();
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete($id)
    {
        $this->authService->assertGranted(PermissionInSystem::NOTIFICATION_DELETE);
        $notification = $this->get($id);

        $this->notificationRepository->remove($notification);
        $this->notificationRepository->flush();

        return true;
    }

    /**
     * Return Notifictation message# "$id" but IFF the recipient ID is the same
     * as the requesting sessions user id, otherwise we must throw an exception.
     *
     * @param $id Integer the notification message to retrieve
     *
     * @return Notification
     * @throws ForbiddenException
     * @throws \DvsaCommonApi\Service\Exception\NotFoundException
     * @throws \Exception
     */
    public function get($id)
    {
        $this->authService->assertGranted(PermissionInSystem::NOTIFICATION_READ);
        $notification = $this->notificationRepository->get($id);
        $service = $this->serviceManager->get('DvsaAuthenticationService');
        $identity = $service->getIdentity();
        $recipientId = $notification->getRecipient();

        if ($this->authService->isGranted(PermissionInSystem::NOMINATE_AEDM)) {
            return $notification;
        }

        if ($recipientId) {
            if ($recipientId->getId() != $identity->getUserId()) {
                throw new ForbiddenException('Access Denied invalid user');
            }
        } else {
            throw new \Exception('Failed to get recipientId for notification : ' . $id);
        }
        return $notification;
    }

    /**
     * Gets all notifications (and nominations) by personId
     *
     * @param int $personId
     *
     * @return Notification[]
     */
    public function getAllByPersonId($personId)
    {
        $this->authService->assertGranted(PermissionInSystem::NOTIFICATION_READ);
        $person = $this->findOrThrowException(Person::class, $personId, Person::ENTITY_NAME);

        return $this->notificationRepository->findAllByPersonId($person->getId());
    }

    /**
     * @param int $id
     *
     * @return Notification
     */
    public function markAsRead($id)
    {
        $this->authService->assertGranted(PermissionInSystem::NOTIFICATION_UPDATE);
        /** @var  $notification Notification */
        $notification = $this->get($id);

        if (null === $notification->getReadOn()) {
            $notification->setReadOn(new \DateTime());
            $this->notificationRepository->save($notification);
        }

        return $notification;
    }

    /**
     * @param int   $notificationId
     * @param array $data
     *
     * @return bool
     * @throws BadRequestException
     */
    public function action($notificationId, $data)
    {
        $this->authService->assertGranted(PermissionInSystem::NOTIFICATION_ACTION);

        $this->validator->validateActionData($data);
        $notification = $this->get($notificationId);

        $this->verify($notification, $data['action']);

        AbstractNotificationActionHandler::getInstance(
            $data['action'],
            $this->serviceManager
        )->proceed($notification);

        return true;
    }

    private function verify(Notification $notification, $action)
    {
        if (false === $notification->isActionRequired()) {
            throw new BadRequestException(
                'No action can be taken for this notification',
                BadRequestException::ERROR_CODE_INVALID_ENTITY_STATE
            );
        }

        if (false === $notification->isActionValid($action)) {
            throw new BadRequestException(
                'Action ' . $action . ' is illegal for this nomination',
                BadRequestException::ERROR_CODE_BUSINESS_FAILURE
            );
        }
    }
}
