<?php
namespace NotificationApiTest\NotificationApiTest\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use DvsaCommon\Date\DateUtils;
use DvsaEntities\Entity\Notification;
use DvsaEntities\Entity\NotificationField;
use DvsaEntities\Entity\NotificationTemplate;
use NotificationApi\Mapper\NotificationMapper;
use PHPUnit_Framework_TestCase;

/**
 * Class NotificationMapperTest
 *
 * @package NotificationApiTest\NotificationApiTest\Controller
 */
class NotificationMapperTest extends PHPUnit_Framework_TestCase
{
    const FAKE_DATE_TIME = '2014-06-02T14:38:37Z';

    public function test_extract_validData_shouldReturnExtractedNotification()
    {
        $this->assertParsedTemplate('John, initial training required', '${name}, initial training required');
    }

    public function test_extract_validDataButOneFieldNotPassed_shouldReturnExtractedNotification()
    {
        $this->assertParsedTemplate('John, ${key} initial training', '${name}, ${key} initial training');
    }

    public function test_extract_validDataWithHtml_shouldReturnExtractedNotificationHtmlNotEscaped()
    {
        $this->assertParsedTemplate('John, <b>initial</b> training', '${name}, <b>initial</b> training');
    }

    private function assertParsedTemplate($expectedResult, $template)
    {
        $extractor = new NotificationMapper();

        $this->assertSame(
            $this->expectedResult($expectedResult),
            $extractor->toArray(
                $this->createNotificationObject($template)
            )
        );
    }

    private function createNotificationObject($content)
    {
        $notification = new Notification();
        $template = new NotificationTemplate();
        $field = new NotificationField();
        $field->setField('name')->setValue('John');
        $array = new ArrayCollection([$field]);

        $template->setContent($content);
        $template->setId(12345);
        $notification
            ->setId(2)
            ->setRecipient(5)
            ->setReadOn(DateUtils::toDateTime(self::FAKE_DATE_TIME))
            ->setIsArchived(true)
            ->setNotificationTemplate($template)
            ->setFields($array);

        return $notification;
    }

    private function expectedResult($content)
    {
        return [
            'id' => 2,
            'recipientId'=> 5,
            'templateId' => 12345,
            'subject' => '',
            'content' => $content,
            'readOn' => self::FAKE_DATE_TIME,
            'createdOn' => '',
            'isArchived' => true,
            'fields' => [
                'name' => 'John'
            ],
            'updatedOn' => ''
        ];
    }
}
