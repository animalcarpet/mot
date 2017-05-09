<?php

namespace MailerApiTest\Logic;

use DvsaCommon\Validator\EmailAddressValidator;
use MailerApi\Logic\AbstractMailerLogic;
use DvsaCommonTest\TestUtils\XMock;
use MailerApi\Service\MailerService;
use MailerApi\Service\TemplateResolverService;
use Zend\View\Resolver\ResolverInterface;

class AbstractMailerLogicTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MailerService
     */
    private $mailerService;

    /**
     * @var TemplateResolverService
     */
    private $templateResolver;

    /**
     * @var AbstractMailerLogic
     */
    private $object;

    public function testSendMail()
    {
        $this->mailerService = XMock::of(MailerService::class);
        $this->mailerService->expects($this->once())
            ->method('send')
            ->willReturn(true);

        $this->createServiceWithMocks();

        $this->assertTrue($this->object->send(
            'abstractmailerlogictest@'.EmailAddressValidator::TEST_DOMAIN,
            __METHOD__.'_subject',
            __METHOD__.'message'
        ));
    }

    public function testRenderTemplate()
    {
        // Mock the resolver to always return me the same file
        $resolver = XMock::of(ResolverInterface::class);
        $resolver->expects($this->once())
            ->method('resolve')
            ->willReturn(__DIR__.'/../fixtures/mailTemplate.phtml');

        // mock getResolver to return me the resolver defined above
        $this->templateResolver = XMock::of(TemplateResolverService::class);
        $this->templateResolver->expects($this->once())
            ->method('getResolver')
            ->willReturn($resolver);

        $this->createServiceWithMocks();

        /**
         * The two vars being passed are not important because I have defined (above) that the resolver
         * will always return the same template.
         */
        $file = $this->object->renderTemplate('notImportant', 'alsoNotImportant');
        $this->assertInternalType('string', $file);
        $this->assertNotEmpty($file);
    }

    private function createServiceWithMocks()
    {
        $config = [
            AbstractMailerLogic::CONFIG_KEY => [],
        ];
        $mailerService = $this->mailerService ?: XMock::of(MailerService::class);
        $templateResolver = $this->templateResolver ?: XMock::of(TemplateResolverService::class);
        $this->object = $this->getMockBuilder(AbstractMailerLogic::class)
            ->setConstructorArgs([
                $mailerService,
                $templateResolver,
                $config,
            ])
            ->getMockForAbstractClass();
    }
}
