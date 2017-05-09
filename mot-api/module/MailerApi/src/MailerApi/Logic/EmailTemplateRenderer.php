<?php

namespace MailerApi\Logic;

use Zend\View\Model\ViewModel;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Resolver\AggregateResolver;
use Zend\View\Resolver\TemplateMapResolver;

class EmailTemplateRenderer
{
    /**
     * @var PhpRenderer
     */
    protected $phpRenderer;

    /**
     * EmailTemplateRenderer constructor.
     *
     * @param array $templateMap
     */
    public function __construct($templateMap)
    {
        $resolver = new AggregateResolver();
        $resolver->attach(new TemplateMapResolver($templateMap));

        $this->phpRenderer = new PhpRenderer();
        $this->phpRenderer->setResolver($resolver);
    }

    /**
     * @param $data
     * @param $templateName
     *
     * @return string
     */
    public function renderTemplate($data, $templateName)
    {
        $viewModel = new ViewModel($data);
        $viewModel->setTemplate($templateName);

        return $this->phpRenderer->render($viewModel);
    }
}
