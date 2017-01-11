<?php

namespace Core\Controller;

use Application\Navigation\Breadcrumbs\BreadcrumbsBuilder;
use Core\Action\AbstractActionResult;
use Core\Action\ActionResult;
use Core\Action\RedirectToRoute;
use Core\Action\RedirectToUrl;
use Core\ViewModel\Sidebar\SidebarInterface;
use DvsaCommon\HttpRestJson\Client as HttpRestJsonClient;
use DvsaCommon\Utility\ArrayUtils;
use DvsaFeature\Exception\FeatureNotAvailableException;
use DvsaFeature\FeatureToggles;
use Zend\Form\Annotation\AnnotationBuilder;
use Zend\Http\Request;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Session\Container;
use Zend\View\Model\ViewModel;

abstract class AbstractDvsaActionController
    extends AbstractActionController
{
    const FORM_ERROR_CONTAINER_NAMESPACE = 'formErrorMessages';
    const FORM_ERROR_CONTAINER_KEY       = 'errorData';
    const TEMPLATE_FLASH_ERROR           = 'error/flash-error';

    protected $form;
    /**
     * @var HttpRestJsonClient
     */
    protected $restClient;

    public function isFeatureEnabled($name)
    {
        return $this->getFeatureToggles()
            ->isEnabled($name);
    }

    /**
     * @return FeatureToggles
     */
    protected function getFeatureToggles()
    {
        return $this
            ->getServiceLocator()
            ->get('Feature\FeatureToggles');
    }

    public function assertFeatureEnabled($name)
    {
        if (!$this->isFeatureEnabled($name)) {
            throw new FeatureNotAvailableException($name);
        }
    }

    protected function getLogger()
    {
        return $this->getServiceLocator()->get('Application/Logger');
    }

    /**
     * @return \DvsaCommon\HttpRestJson\Client|object
     * @deprecated Use mappers if it possible
     */
    protected function getRestClient()
    {
        if (!$this->restClient) {
            $sm = $this->getServiceLocator();
            $this->restClient = $sm->get(HttpRestJsonClient::class);
        }

        return $this->restClient;
    }

    protected function getForm($objectModel)
    {
        if (!$this->form) {
            $builder = new AnnotationBuilder();
            $this->form = $builder->createForm($objectModel);
        }

        return $this->form;
    }

    protected function addInfoMessages($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $this->flashMessenger()->addInfoMessage($message);
            }
        } else {
            $this->flashMessenger()->addInfoMessage($messages);
        }
        return $this;
    }

    protected function addErrorMessages($messages)
    {
        if (is_array($messages)) {
            foreach ($messages as $message) {
                $this->flashMessenger()->addErrorMessage($message);
            }
        } else {
            $this->flashMessenger()->addErrorMessage($messages);
        }
        return $this;
    }

    protected function addErrorMessage($message)
    {
        $this->flashMessenger()->addErrorMessage($message);
        return $this;
    }

    protected function addSuccessMessage($message)
    {
        $this->flashMessenger()->addSuccessMessage($message);
        return $this;
    }

    protected function getFlashErrorViewModel()
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate(self::TEMPLATE_FLASH_ERROR);
        return $viewModel;
    }

    /**
     *
     * @param $errorData
     *
     * errorData should be returned from the DvsaCommon\HttpRestJson\Exception;->getExpandedData()
     *
     */
    protected function addFormErrorMessagesToSession($errorData)
    {
        $container = new Container(self::FORM_ERROR_CONTAINER_NAMESPACE);
        $container->offsetSet(self::FORM_ERROR_CONTAINER_KEY, $errorData);
    }

    /**
     * @return \Application\Service\CatalogService
     */
    public function getCatalogService()
    {
        return $this->getServiceLocator()->get('CatalogService');
    }

    /**
     * @param string $catalogName
     * @return array
     */
    public function getCatalogByName($catalogName)
    {
        return ArrayUtils::get($this->getCatalogService()->getData(), $catalogName);
    }

    /**
     * @return array
     */
    protected function getConfig()
    {
        return $this->getServiceLocator()->get('config');
    }

    /**
     * @return Request
     */
    public function getRequest()
    {
        return parent::getRequest();
    }

    /**
     * @return BreadcrumbsBuilder
     */
    public function getBreadcrumbBuilder()
    {
        return $this->serviceLocator->get(BreadcrumbsBuilder::class);
    }

    public function getSidebar()
    {
        return $this->layout()->getVariable('sidebar');
    }

    public function setSidebar(SidebarInterface $sidebar)
    {
        $this->layout()->setVariable('sidebar', $sidebar);
    }

    public function setPageTitle($title)
    {
        $this->layout()->setVariable('pageTitle', $title);
    }

    public function setPageSubTitle($subTitle)
    {
        $this->layout()->setVariable('pageSubTitle', $subTitle);
    }

    public function setPageLede($lede)
    {
        $this->layout()->setVariable('pageLede', $lede);
    }

    public function setBreadcrumbs(array $breadcrumbs)
    {
        $this->layout()->setVariable('breadcrumbs', $breadcrumbs);
    }

    public function applyActionResult(AbstractActionResult $result)
    {
        if ($result->getSuccessMessages()) {
            foreach ($result->getSuccessMessages() as $message) {
                $this->addSuccessMessage($message);
            }
        }

        if ($result->getErrorMessages()) {
            foreach ($result->getErrorMessages() as $message) {
                $this->addErrorMessage($message);
            }
        }

        if ($result instanceof ActionResult) {
            if ($result->getSidebar()) {
                $this->setSidebar($result->getSidebar());
            }

            if ($result->layout()->getTemplate()) {
                $this->layout($result->layout()->getTemplate());
            }

            if ($result->layout()->getPageTitle()) {
                $this->setPageTitle($result->layout()->getPageTitle());
            }

            if ($result->layout()->getPageSubTitle()) {
                $this->setPageSubTitle($result->layout()->getPageSubTitle());
            }

            if ($result->layout()->getPageLede()) {
                $this->setPageLede($result->layout()->getPageLede());
            }

            if ($result->layout()->getBreadcrumbs()) {
                $this->setBreadcrumbs($result->layout()->getBreadcrumbs());
            }

            $viewModel = new ViewModel([
                'viewModel' => $result->getViewModel(),
            ]);

            if ($result->getTemplate()) {
                $viewModel->setTemplate($result->getTemplate());
            }

            return $viewModel;
        } elseif ($result instanceof RedirectToRoute) {
            return $this->redirect()->toRoute(
                $result->getRouteName(),
                $result->getRouteParams(),
                ['query' => $result->getQueryParams()]
            );
        } elseif ($result instanceof RedirectToUrl) {
            return $this->redirect()->toUrl($result->getUrl());
        }

        throw new \InvalidArgumentException();
    }
}
