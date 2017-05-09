<?php

namespace DashboardTest\ViewModel;

use Account\Service\SecurityQuestionService;
use Dashboard\ViewModel\SecurityQuestionViewModel;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaClient\Entity\Person;
use DvsaCommon\Dto\Security\SecurityQuestionDto;
use DvsaCommon\UrlBuilder\AccountUrlBuilderWeb;
use DvsaCommonTest\TestUtils\XMock;
use UserAdmin\Service\UserAdminSessionManager;
use Zend\Mvc\Controller\Plugin\FlashMessenger;

/**
 * Class SecurityQuestionViewModelTest.
 */
class SecurityQuestionViewModelTest extends \PHPUnit_Framework_TestCase
{
    const QUESTION_NB = 1;
    const PERSON_ID = 1;

    /** @var SecurityQuestionViewModel */
    private $view;

    /** @var SecurityQuestionService */
    private $service;

    /** @var \DvsaClient\Entity\Person */
    private $person;

    /** @var \DvsaCommon\Dto\Security\SecurityQuestionDto */
    private $question;
    private $messenger;

    private $personProfileUrlGenerator;

    public function setup()
    {
        $this->service = XMock::of(
            SecurityQuestionService::class,
            ['getQuestionNumber', 'getUserId', 'getSearchParams', 'getPerson', 'getQuestion', 'getQuestionSuccess']
        );
        $this->person = new Person();
        $this->question = new SecurityQuestionDto();

        /* @var PersonProfileUrlGenerator $personProfileUrlGenerator */
        $this->personProfileUrlGenerator = $this
            ->getMockBuilder(PersonProfileUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->view = new SecurityQuestionViewModel($this->service, $this->personProfileUrlGenerator);
        $this->messenger = XMock::of(FlashMessenger::class);
    }

    public function testGetNextPageLinkQuestionOne()
    {
        $this->service->expects($this->any())
            ->method('getQuestionNumber')
            ->willReturn(UserAdminSessionManager::FIRST_QUESTION);
        $this->service->expects($this->any())
            ->method('getQuestionSuccess')
            ->willReturn(true);

        $this->withPersonProfileUrlGenerator(2);

        $link = $this->view->getNextPageLink($this->messenger);
        $this->assertSame('/profile/security-question/2', $link);
    }

    public function testGetNextPageLinkQuestionOneFailure()
    {
        $this->service->expects($this->at(0))
            ->method('getQuestionNumber')
            ->willReturn(UserAdminSessionManager::FIRST_QUESTION);

        $link = $this->view->getNextPageLink($this->messenger);
        $this->assertInstanceOf(AccountUrlBuilderWeb::class, $link);
        $this->assertSame('/forgotten-password/not-authenticated', $link->toString());
    }

    public function testGetNextPageLinkQuestionTwo()
    {
        $this->service->expects($this->at(0))
            ->method('getQuestionNumber')
            ->willReturn(UserAdminSessionManager::SECOND_QUESTION);

        $link = $this->view->getNextPageLink($this->messenger);
        $this->assertInstanceOf(AccountUrlBuilderWeb::class, $link);
        $this->assertSame('/forgotten-password/not-authenticated', $link->toString());
    }

    public function testGetCurrentLink()
    {
        $this->service->expects($this->any())
            ->method('getQuestionNumber')
            ->willReturn(UserAdminSessionManager::FIRST_QUESTION);

        $this->withPersonProfileUrlGenerator(1);

        $link = $this->view->getCurrentLink();
        $this->assertSame('/profile/security-question/1', $link);
    }

    public function testGetQuestion()
    {
        $this->service->expects($this->at(0))
            ->method('getQuestion')
            ->willReturn($this->question);

        $this->assertEquals($this->question, $this->view->getQuestion());
    }

    public function testGetQuestionNb()
    {
        $this->service->expects($this->at(0))
            ->method('getQuestionNumber')
            ->willReturn(self::QUESTION_NB);

        $this->assertEquals(self::QUESTION_NB, $this->view->getQuestionNumber());
    }

    public function testGetUserId()
    {
        $this->service->expects($this->at(0))
            ->method('getUserId')
            ->willReturn(self::PERSON_ID);

        $this->assertEquals(self::PERSON_ID, $this->view->getUserId());
    }

    private function withPersonProfileUrlGenerator($questionNumber)
    {
        return $this->personProfileUrlGenerator
            ->expects($this->once())
            ->method('fromPersonProfile')
            ->willReturn('/profile/security-question/'.$questionNumber);
    }
}
