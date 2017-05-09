<?php

namespace TestSupport\Controller;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use DvsaCommon\Utility\ArrayUtils;
use TestSupport\Helper\RestClientGetterTrait;
use TestSupport\Helper\TestDataResponseHelper;

/**
 * Special notices related methods.
 *
 * Should not be deployed in production.
 */
class SpecialNoticeDataController extends BaseTestSupportRestfulController
{
    use RestClientGetterTrait;

    public function broadcastAction()
    {
        $data = json_decode($this->getRequest()->getContent(), true);

        $username = ArrayUtils::get($data, 'username');
        $specialNoticeContentId = (int) ArrayUtils::get($data, 'specialNoticeContentId');
        $isAcknowledged = ArrayUtils::get($data, 'isAcknowledged') === 'true';

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get(EntityManager::class);
        /** @var Connection $connection */
        $connection = $entityManager->getConnection();

        $connection->executeUpdate(
            'INSERT INTO special_notice (username, special_notice_content_id, is_acknowledged, created_by)
             VALUE (:username, :specialNoticeContentId, :isAcknowledged, :createdBy)',
            [
                'username' => $username,
                'specialNoticeContentId' => $specialNoticeContentId,
                'isAcknowledged' => (int) $isAcknowledged,
                'createdBy' => 1,
            ]
        );
        $id = $connection->lastInsertId();

        return TestDataResponseHelper::jsonOk($id);
    }

    public function createAction()
    {
        $data = json_decode($this->getRequest()->getContent(), true);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getServiceLocator()->get(EntityManager::class);
        /** @var Connection $connection */
        $connection = $entityManager->getConnection();

        $issueNumber = $connection->executeQuery('SELECT issue_number FROM special_notice_content ORDER BY issue_number DESC LIMIT 1');
        $data['issueNumber'] = $issueNumber->fetchColumn() + 1;

        $connection->executeUpdate(
            'INSERT INTO special_notice_content (title, issue_number, issue_year, issue_date, expiry_date, internal_publish_date,
              external_publish_date, notice_text, acknowledge_within, is_published, is_deleted, created_by)
             VALUE (:title, :issueNumber, :issueYear, :issueDate, :expiryDate, :internalPublishDate,
              :externalPublishDate, :noticeText, :acknowledgementPeriod, :isPublished, :isDeleted, :createdBy)',
            $data
        );

        $id = $connection->lastInsertId();

        return TestDataResponseHelper::jsonOk(['id' => $id]);
    }
}
