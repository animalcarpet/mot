<?php

namespace Application\Navigation\Breadcrumbs;

use Application\Navigation\Breadcrumbs\Handler\BreadcrumbsPartResolver;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Helper\Layout;

/**
 * Helper to generate breadcrumbs.
 *
 * Example usage:
 *
 * $builder->organisationBySiteId($siteId)->site($siteId)->simple('Leaf page', 'leaf-page', ['id' => 4])->build()
 */
class BreadcrumbsBuilder
{
    private $parts = [];
    private $serviceLocator;
    /**
     * @var Layout
     */
    private $layout;

    public function __construct($resolvers, ServiceLocatorInterface $serviceLocator, Layout $layout)
    {
        $this->resolvers = $resolvers;
        $this->serviceLocator = $serviceLocator;
        $this->layout = $layout;
    }

    /**
     * Adds site name breadcrumb.
     *
     * @param $siteId
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function site($siteId)
    {
        $this->resolve(['site' => $siteId]);

        return $this;
    }

    /**
     * Adds organisation name breadcrumb based on site.
     *
     * @param $siteId
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function organisationBySiteId($siteId)
    {
        $this->resolve(['organisationBySite' => $siteId]);

        return $this;
    }

    /**
     * Adds simple breadcrumb which has a label and optionally link.
     *
     * @param $label - breadcrumb label
     * @param null  $link   - route
     * @param array $params - route parameters
     *
     * @return $this
     *
     * @throws \Exception
     */
    public function simple($label, $link = null, $params = [])
    {
        $this->resolve(
            [
                'simple' => [
                    'label' => $label,
                    'link' => ['route' => $link, 'params' => $params],
                ],
            ]
        );

        return $this;
    }

    /**
     * Sets breadcrumb in the layout.
     */
    public function build()
    {
        $layout = $this->layout;
        $layout()->setVariable('breadcrumbs', ['breadcrumbs' => $this->parts]);
        unset($this->parts);
        $this->parts = [];
    }

    private function resolve($mapping)
    {
        foreach ($mapping as $type => $value) {
            if (array_key_exists($type, $this->resolvers)) {
                $handlerClass = $this->resolvers[$type];
                /** @var BreadcrumbsPartResolver $resolver */
                $resolver = $this->serviceLocator->get($handlerClass);

                $ret = $resolver->resolve($value);
                // skip breadcrumb for which data cannot be determined
                if (!empty($ret)) {
                    $this->parts [] = $ret;
                }

                return;
            } else {
                throw new \Exception('Breadcrumb building failed for type: '.$type);
            }
        }
        throw new \Exception('Breadcrumb building failed');
    }
}
