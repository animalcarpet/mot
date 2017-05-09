<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\View;

use Core\View\ContentBreadcrumbs;
use Dvsa\Mot\Frontend\MotTestModule\ViewModel\ComponentCategoryCollection;
use Zend\Mvc\Router\RouteStackInterface as Router;

/**
 * Factory class to produce ContentBreadcrumbs instances.
 *
 * Generates the breadcrumb links used in the Defects screens. If the user is at the Defects home screen, then no
 * breadcrumb links will be returned.
 */
class DefectsContentBreadcrumbsBuilder
{
    /**
     * @var Router
     */
    private $router;

    /**
     * DefectsContentBreadcrumbsBuilder constructor.
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param ComponentCategoryCollection $componentCategoryCollection
     * @param int                         $motTestNumber
     *
     * @return ContentBreadcrumbs
     */
    public function getContentBreadcrumbs(ComponentCategoryCollection $componentCategoryCollection, $motTestNumber)
    {
        $contentBreadcrumbs = new ContentBreadcrumbs();
        $options = ['name' => 'mot-test-defects/categories/category'];
        $params = ['motTestNumber' => $motTestNumber];

        $categoryPath = $componentCategoryCollection->getCategoryPath();

        foreach ($categoryPath as $parent) {
            $params['categoryId'] = $parent->getCategoryId();
            $url = $this->router->assemble($params, $options);

            // First item in breadcrumb list should be 'Categories'
            if ($parent === reset($categoryPath)) {
                $contentBreadcrumbs->set('Categories', $url);
            } else {
                $contentBreadcrumbs->set($parent->getName(), $url);
            }
        }

        $name = $componentCategoryCollection->getComponentCategory()->getName();
        $contentBreadcrumbs->set($name, null);

        return $contentBreadcrumbs;
    }
}
