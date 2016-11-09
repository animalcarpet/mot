<?php

namespace AccountTest\ViewModel;

use Account\Service\SecurityQuestionService;
use Account\ViewModel\SecurityQuestionViewModel;
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
    const PERSON_ID   = 1;

    /**
     * @var SecurityQuestionViewModel
     */
    private $view;

    /** @var  SecurityQuestionService */
    private $service;

    /** @var  \DvsaClient\Entity\Person */
    private $person;

    /** @var  \DvsaCommon\Dto\Security\SecurityQuestionDto */
    private $question;
    private $messenger;

    public function setup()
    {
        $this->service = XMock::of(
            SecurityQuestionService::class,
            ['getQuestionNumber', 'getUserId', 'getSearchParams', 'getPerson', 'getQuestion', 'getQuestionSuccess']
        );
        $this->person = new Person();
        $this->question = new SecurityQuestionDto();

        /** @var PersonProfileUrlGenerator $personProfileUrlGenerator */
        $personProfileUrlGenerator = $this
            ->getMockBuilder(PersonProfileUrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->view = new SecurityQuestionViewModel($this->service, $personProfileUrlGenerator);
        $this->messenger = XMock::of(FlashMessenger::class);
    }

    public function testGetNextPageLinkQuestionOne()
    {
        $this->service->expects($this->at(0))
            ->method('getQuestionNumber')
            ->willReturn(UserAdminSessionManager::FIRST_QUESTION);
        $this->service->expects($this->at(1))
            ->method('getQuestionSuccess')
            ->willReturn(true);
        $this->service->expects($this->at(2))
            ->method('getUserId')
            ->willReturn(self::PERSON_ID);

        $link = $this->view->getNextPageLink($this->messenger);
        $this->assertInstanceOf(AccountUrlBuilderWeb::class, $link);
        $this->assertSame('/forgotten-password/security-question/1/2', $link->toString());
    }

    public function testGetNextPageLinkQuestionOneFailure()
    {
        $this->service->expects($this->at(0))
            ->method('getQuestionNumber')
            ->willReturn(UserAdminSessionManager::FIRST_QUESTION);
        $this->service->expects($this->at(1))
            ->method('getQuestionSuccess')
            ->willReturn(false);

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
        $this->service->expects($this->at(0))
            ->method('getUserId')
            ->willReturn(self::PERSON_ID);
        $this->service->expects($this->at(1))
            ->method('getQuestionNumber')
            ->willReturn(UserAdminSessionManager::FIRST_QUESTION);

        $link = $this->view->getCurrentLink();
        $this->assertInstanceOf(AccountUrlBuilderWeb::class, $link);
        $this->assertSame('/forgotten-password/security-question/1/1', $link->toString());
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
}
