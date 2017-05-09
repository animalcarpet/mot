<?php

namespace Core\Routing;

use Zend\Mvc\Controller\AbstractController;
use Zend\View\Helper\Url;
use Zend\View\Renderer\PhpRenderer;

abstract class AbstractRoutes
{
    private $urlHelperAdapter;

    /**
     * @param Url|PhpRenderer|AbstractController|\Zend\Mvc\Controller\Plugin\Url $object
     */
    public function __construct($object)
    {
        $urlHelper = null;

        if ($object instanceof PhpRenderer) {
            $object = $object->plugin('url');
        } elseif ($object instanceof AbstractController) {
            $object = $object->plugin('url');
        }

        if ($object instanceof Url) {
            $this->urlHelperAdapter = $object;
        } elseif ($object instanceof \Zend\Mvc\Controller\Plugin\Url) {
            $this->urlHelperAdapter = function ($name, $params, $options, $reuseMatchedParams) use ($object) {
                return $object->fromRoute($name, $params, $options, $reuseMatchedParams);
            };
        } else {
            throw new \InvalidArgumentException('First parameter must be of class: '.
                Url::class
                .', '.PhpRenderer::class
                .', '.AbstractController::class
                .' or '.\Zend\Mvc\Controller\Plugin\Url::class
            );
        }
    }

    protected function url($name = null, $params = array(), $options = array(), $reuseMatchedParams = false)
    {
        $helper = $this->urlHelperAdapter;

        return $helper($name, $params, $options, $reuseMatchedParams);
    }
}
