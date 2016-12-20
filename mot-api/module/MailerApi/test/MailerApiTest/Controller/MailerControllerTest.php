<?php

namespace MailerApiTest\Controller;

use DvsaApplicationLogger\Log\Logger;
use DvsaCommonTest\Bootstrap;
use DvsaCommonTest\TestUtils\XMock;
use DvsaMotApi\Service\UserService;
use DvsaMotApiTest\Controller\AbstractMotApiControllerTestCase;
use MailerApi\Controller\MailerController;
use MailerApi\Service\MailerService;
use PersonApi\Service\PersonalDetailsService;
use DvsaEntities\Entity\Person;
use Zend\View\Renderer\PhpRenderer;

class MailerControllerTest extends AbstractMotApiControllerTestCase
{
    protected $controller;
    protected $mockUserService;
    protected $mockLogger;

    const DUMMY_EMAIL = MailerService::AWS_MAIL_SIMULATOR_SUCCESS;

    protected function setUp()
    {
        $appTestConfig = include getcwd() . '/test/test.config.php';
        Bootstrap::init($appTestConfig);

        $this->setController(new MailerController());
        parent::setUp();

        $this->mockUserService = XMock::of(UserService::class, ['findPerson']);
        $this->serviceManager->setService(UserService::class, $this->mockUserService);

        $this->mockLogger = XMock::of(Logger::class, []);
        $this->serviceManager->setService('Application\Logger', $this->mockLogger);
    }

    public function testControllerWorksWithGoodRequest()
    {
        $this->disableLogging();
        $detailsService = $this->setMockPersonalDetails();
        $this
            ->setMockPerson()
            ->primePostRequest($detailsService)
            ->primeMailRendering();

        $config = $this->serviceManager->get('Config');
        $config['mailer']['checkHost'] = false;
        $this->serviceManager->setService('Config', $config);

        /** @var \Zend\View\Model\JsonModel $result */
        $result = $this->triggerAction(
            [
                '_class' => '\\DvsaCommon\\Dto\\Mailer\\MailerDto',
                'data' => ['userid' => 5],
            ]
        );

        $data = $result->getVariable('data');
        $this->assertArrayHasKey('sent', $data);
        $this->assertEquals('yes', $data['sent']);
    }

    /**
     * @expectedException \Exception
     * @exceptionMessage 31415
     */
    public function testControllerWorksWithGoodRequestButInvalidRecipient()
    {
        $this->disableLogging();
        $detailsService = $this->setMockPersonalDetails();
        $this
            ->setMockPerson()
            ->primePostRequest($detailsService)
            ->primeMailRendering();

        // Remove the email override
        $config = $this->serviceManager->get('Config');
        $config['mailer']['mail-class'] = 31415;
        $config['mailer']['recipient'] = 'rubbish';
        $this->serviceManager->setService('Config', $config);

        /** @var \Zend\View\Model\JsonModel $result */
        $result = $this->triggerAction(
            [
                '_class' => '\\DvsaCommon\\Dto\\Mailer\\MailerDto',
                'data' => ['userid' => 5],
            ]
        );

        $data = $result->getVariable('data');
        $this->assertArrayHasKey('sent', $data);
        $this->assertEquals('yes', $data['sent']);
    }

    public function testControllerWorksWithGoodRequestLoggingActualRecipient()
    {
        $detailsService = $this->setMockPersonalDetails();
        $this
            ->setMockPerson()
            ->primePostRequest($detailsService)
            ->primeMailRendering();

        $this->mockLogger->expects($this->once())
            ->method('info');

        $config = $this->serviceManager->get('Config');
        $config['mailer']['checkHost'] = false;
        $this->serviceManager->setService('Config', $config);

        /** @var \Zend\View\Model\JsonModel $result */
        $result = $this->triggerAction(
            [
                '_class' => '\\DvsaCommon\\Dto\\Mailer\\MailerDto',
                'data' => ['userid' => 5],
            ]
        );

        $data = $result->getVariable('data');
        $this->assertArrayHasKey('sent', $data);
        $this->assertEquals('yes', $data['sent']);
    }

    /**
     * @expectedException \Exception
     * @exceptionMessage 31415
     */
    public function testControllerWorksWithGoodRequestLoggingActualRecipientNoOverride()
    {
        $detailsService = $this->setMockPersonalDetails();
        $this
            ->setMockPerson()
            ->primePostRequest($detailsService)
            ->primeMailRendering();

        $this->mockLogger->expects($this->once())
            ->method('info');

        // Remove the email override
        $config = $this->serviceManager->get('Config');
        $config['mailer']['mail-class'] = 31415;
        $config['mailer']['checkHost'] = false;
        $config['mailer']['recipient'] = self::DUMMY_EMAIL;
        $this->serviceManager->setService('Config', $config);

        /** @var \Zend\View\Model\JsonModel $result */
        $result = $this->triggerAction(
            [
                '_class' => '\\DvsaCommon\\Dto\\Mailer\\MailerDto',
                'data' => ['userid' => 5],
            ]
        );

        $data = $result->getVariable('data');
        $this->assertArrayHasKey('sent', $data);
        $this->assertEquals('yes', $data['sent']);
    }

    /**
     * @expectedException \Exception
     * @exceptionMessage 31415
     */
    public function testControllerWorksWithGoodRequestWithInvalidMailInstanceNotLogged()
    {
        $this->disableLogging();
        $detailsService = $this->setMockPersonalDetails();
        $this
            ->setMockPerson()
            ->primePostRequest($detailsService)
            ->primeMailRendering();

        // Set a bad mail message instance
        $config = $this->serviceManager->get('Config');
        $config['mailer']['mail-class'] = 31415;
        $this->serviceManager->setService('Config', $config);

        /** @var \Zend\View\Model\JsonModel $result */
        $this->triggerAction(
            [
                '_class' => '\\DvsaCommon\\Dto\\Mailer\\MailerDto',
                'data' => ['userid' => 5],
            ]
        );
    }

    /**
     * @expectedException \Exception
     * @exceptionMessage 42
     */
    public function testControllerWorksWithGoodRequestWithInvalidTransportInstanceNotLogged()
    {
        $this->disableLogging();
        $detailsService = $this->setMockPersonalDetails();
        $this
            ->setMockPerson()
            ->primePostRequest($detailsService)
            ->primeMailRendering();

        // Set a bad mail transport instance
        $config = $this->serviceManager->get('Config');
        $config['mailer']['mta-class'] = 42;
        $this->serviceManager->setService('Config', $config);

        /** @var \Zend\View\Model\JsonModel $result */
        $this->triggerAction(
            [
                '_class' => '\\DvsaCommon\\Dto\\Mailer\\MailerDto',
                'data' => ['userid' => 5],
            ]
        );
    }

    public function testControllerWorksWithGoodRequestWithRecipientOverride()
    {
        $this->disableLogging();
        $detailsService = $this->setMockPersonalDetails();
        $this
            ->setMockPerson()
            ->primePostRequest($detailsService)
            ->primeMailRendering();

        $config = $this->serviceManager->get('Config');
        $config['mailer']['checkHost'] = false;
        $this->serviceManager->setService('Config', $config);

        /** @var \Zend\View\Model\JsonModel $result */
        $result = $this->triggerAction(
            [
                '_class' => '\\DvsaCommon\\Dto\\Mailer\\MailerDto',
                'data' => ['userid' => 5],
            ]
        );

        $data = $result->getVariable('data');
        $this->assertArrayHasKey('sent', $data);
        $this->assertEquals('yes', $data['sent']);
    }

    public function testControllerHandlesExceptionThrownDuringMailerHandling()
    {
        // Make send() throw an exception
        $mockTransport = XMock::of(\Zend\Mail\Transport\Sendmail::class, ['send']);
        $mockTransport->expects($this->once())
            ->method('send')
            ->willThrowException(new \Exception("broken"));

        // Turn off the logging feature
        $config = $this->serviceManager->get('Config');
        $config['mailer']['logfile'] = '';
        $config['mailer']['logfile'] = '/tmp/testing.log';
        $config['mailer']['mta-class'] = $mockTransport;
        $config['mailer']['checkHost'] = false;
        $this->serviceManager->setService('Config', $config);

        $detailsService = $this->setMockPersonalDetails();
        $this
            ->setMockPerson()
            ->primePostRequest($detailsService)
            ->primeMailRendering();

        /** @var \Zend\View\Model\JsonModel $result */
        $result = $this->triggerAction(
            [
                '_class' => '\\DvsaCommon\\Dto\\Mailer\\MailerDto',
                'data' => ['userid' => 5],
            ]
        );

        $data = $result->getVariable('data');
        $this->assertArrayHasKey('sent', $data);
        $this->assertEquals('inhibited', $data['sent']);
    }

    private function setMockPersonalDetails($forAddress = self::DUMMY_EMAIL)
    {
        $detailsService = XMock::of(PersonalDetailsService::class, ['get', 'getEmail']);

        $detailsService->expects($this->once())
            ->method('get')
            ->willReturn($detailsService);

        $detailsService->expects($this->any())
            ->method('getEmail')
            ->willReturn($forAddress);

        $this->serviceManager->setService(PersonalDetailsService::class, $detailsService);

        return $detailsService;
    }

    private function setMockPerson($id = 5)
    {
        $person = new Person();
        $person->setId($id);

        $this->mockUserService->expects($this->any())
            ->method('findPerson')
            ->with($id)
            ->willReturn($person);

        return $this;
    }

    private function primePostRequest($detailsService)
    {
        $this->request->setMethod('post');
        $this->request->setContent(
            json_encode(
                [
                    '_class' => "\\DvsaCommon\\Dto\\Mailer\\MailerDto",
                    'data' => [
                        'userid' => 5,
                        'user' => $detailsService,
                    ],
                ]
            )
        );
        return $this;
    }

    private function primeMailRendering($subject = 'the subject', $message = 'the message')
    {
        $mockRender = XMock::of(PhpRenderer::class, ['render']);
        $mockRender->expects($this->any())
            ->method('render')
            ->will(
                $this->onConsecutiveCalls(
                    $subject,
                    $message
                )
            );
        $this->serviceManager->setService('Zend\View\Renderer\RendererInterface', $mockRender);

    }

    private function triggerAction(Array $postData)
    {
        return $this->getResultForAction('post', null, [], [], $postData);
    }

    private function disableLogging()
    {
        // Turn off the logging feature
        $config = $this->serviceManager->get('Config');
        $config['mailer']['logfile'] = '';
        $this->serviceManager->setService('Config', $config);
    }
}
