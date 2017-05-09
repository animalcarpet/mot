<?php

namespace MailerApiTest\Logic;

use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommonTest\TestUtils\XMock;
use DvsaEntities\Entity\Person;
use MailerApi\Logic\AbstractMailerLogic;
use MailerApi\Logic\UsernameCreator;
use MailerApi\Service\MailerService;
use MailerApi\Service\TemplateResolverService;

class UserNameCreatorTest extends \PHPUnit_Framework_TestCase
{
    const FAKE_USERNAME = 'Fred0001';
    const FAKE_FIRSTNAME = 'Fred';

    public function testSetPerson()
    {
        $config = [
            AbstractMailerLogic::CONFIG_KEY => [],
        ];
        $person = $this->getPersonMock();
        $obj = new UsernameCreator(
            XMock::of(MailerService::class),
            XMock::of(TemplateResolverService::class),
            $config
        );
        $return = $obj->setPerson($person);
        $this->assertInstanceOf(UsernameCreator::class, $return);
    }

    public function testPrepareSubject()
    {
        $person = $this->getPersonMock();

        $allData = [
            'test' => __METHOD__,
        ];

        $obj = XMock::of(UsernameCreator::class, ['renderTemplate']);
        $obj->setPerson($person);
        $obj->expects($this->once())
            ->method('renderTemplate')
            ->with('username', 'create-subject', $allData);

        $obj->prepareSubject($allData);
    }

    public function testPrepareMessage()
    {
        $person = $this->getPersonMock();

        $extraData = [
            'test' => __METHOD__,
        ];

        $allData = array_merge([
            'userName' => $person->getUsername(),
            'firstName' => $person->getFirstName(),
            'signInUrl' => AccountUrlBuilderWeb::signIn(),
        ], $extraData);

        $obj = XMock::of(UsernameCreator::class, ['renderTemplate']);
        $obj->setPerson($person);
        $obj->expects($this->once())
            ->method('renderTemplate')
            ->with('username', 'create', $allData);

        $obj->prepareMessage($extraData);
    }

    private function getPersonMock()
    {
        $person = XMock::of(Person::class);
        $person->expects($this->any())
            ->method('getUsername')
            ->willReturn(self::FAKE_USERNAME);

        $person->expects($this->any())
            ->method('getFirstName')
            ->willReturn(self::FAKE_FIRSTNAME);

        return $person;
    }
}
