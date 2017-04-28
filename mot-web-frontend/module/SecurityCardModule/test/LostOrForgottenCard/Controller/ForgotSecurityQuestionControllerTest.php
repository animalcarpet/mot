<?php

namespace Dvsa\Mot\Frontend\SecurityCardModuleTest\LostOrForgottenCard\Controller;

use CoreTest\Controller\AbstractLightWebControllerTest;
use Dvsa\Mot\Frontend\SecurityCardModule\LostOrForgottenCard\Controller\ForgotSecurityQuestionController;

class ForgotSecurityQuestionControllerTest extends AbstractLightWebControllerTest
{
    private $config = [
        'helpdesk' => [
            'name' => 'DVSA',
            'number' => '111111',
        ],
    ];

    public function testConfigAndTemplateLoadedIntoViewCorrectly()
    {
        $controller = $this->buildController();
        $viewModel = $controller->forgotQuestionAnswerAction();
        $this->assertSame($this->config['helpdesk'], $viewModel->getVariable('config'));
        $this->assertSame('2fa/lost-forgotten/forgot-questions.twig', $viewModel->getTemplate());
    }

    private function buildController()
    {
        $controller = new ForgotSecurityQuestionController(
             $this->config
         );

        $this->setController($controller);
        $this->setUpPluginMocks();

        $layout = $controller->layout();
        $layout
             ->expects($this->any())
             ->method('setVariable')
             ->willReturn($layout);

        return $controller;
    }
}
