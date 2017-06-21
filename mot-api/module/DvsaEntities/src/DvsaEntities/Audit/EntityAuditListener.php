<?php

namespace DvsaEntities\Audit;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use DvsaCommon\Auth\MotIdentityInterface;
use DvsaEntities\Entity\Entity;
use DvsaEntities\Entity\Person;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Handles auditing actions:
 * - sets createdOn/lastUpdatedOn
 * - sets createdBy/lastUpdatedBy
 * on entities that inherit from DvsaEntities\Entity\Entity abstract class.
 *
 * One lightweight class to replace usage of Gedmo library which was too heavy for such a simple task
 */
class EntityAuditListener implements EventSubscriber
{
    /** @var Person $user */
    private $user = null;

    /**
     * @var ServiceLocatorInterface
     */
    private $serviceLocator;

    public function __construct($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function getSubscribedEvents()
    {
        return [
            'onFlush',
        ];
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        $this->handleAuditing(
            $em,
            $uow->getScheduledEntityInsertions(),
            function (Entity $object) use ($em) {
                $object->setCreatedOn(self::currentTimestamp())->setCreatedBy($this->getUser($em));
                $object->setLastUpdatedOn(self::currentTimestamp())->setLastUpdatedBy($this->getUser($em));
            }
        );
        $this->handleAuditing(
            $em,
            $uow->getScheduledEntityUpdates(),
            function (Entity $object) use ($em) {
                $object->setLastUpdatedOn(self::currentTimestamp());
            }
        );
    }

    private function handleAuditing(EntityManager $em, $changedObjects, $modHandler)
    {
        foreach ($changedObjects as $object) {
            if ($object instanceof Entity) {
                $metadata = $em->getClassMetadata(get_class($object));
                $modHandler($object);
                $em->getUnitOfWork()->recomputeSingleEntityChangeSet($metadata, $object);
            }
        }
    }

    private static function currentTimestamp()
    {
        $dt = new \DateTime();
        $us = substr(explode(' ', microtime())[0], 1, 7);

        return \DateTime::createFromFormat('Y-m-d H:i:s.u', $dt->format('Y-m-d H:i:s').$us);
    }

    private function getUser(EntityManager $em)
    {
        if (!$this->user) {
            /** @var MotIdentityInterface $identity */
            $identity = $this->serviceLocator->get('DvsaAuthenticationService')->getIdentity();

            if (!$identity) {
                //Assume root user?
                $userId = 1;
            } else {
                $userId = $identity->getUserId();
            }
            $this->user = $em->getReference(Person::class, $userId);
        }

        return $this->user;
    }
}
