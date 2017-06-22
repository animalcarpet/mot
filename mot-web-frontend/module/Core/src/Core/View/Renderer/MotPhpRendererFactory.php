<?php
namespace Core\View\Renderer;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Renderer\PhpRenderer;

class MotPhpRendererFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $renderer = $this->getPhpRenderer();
        $renderer->setHelperPluginManager($container->get('ViewHelperManager'));
        $renderer->setResolver($container->get('ViewResolver'));

        return $renderer;
    }

    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, PhpRenderer::class);
    }

    private function getPhpRenderer()
    {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            return new MotPhpRenderer();
        } else {
            return new PhpRenderer();
        }
    }
}