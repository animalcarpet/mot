<?php

namespace Organisation\Controller;

use DvsaCommon\Auth\PermissionInSystem;
use Core\Controller\AbstractAuthActionController;
use DvsaCommon\UrlBuilder\PersonUrlBuilderWeb;
use Organisation\Traits\OrganisationServicesTrait;
use Organisation\ViewModel\Search\AeSearchViewModel;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use Zend\Validator\StringLength;

/**
 * Class SearchController.
 */
class SearchController extends AbstractAuthActionController
{
    use OrganisationServicesTrait;

    const PAGE_TITLE = 'Search for AE';
    const INPUT_SEARCH_NAME = 'number';
    const MAX_SEARCH_LENGTH = 30;
    const INVALID_SEARCH_MESSAGE = 'You must enter a valid AE Number';

    public function indexAction()
    {
        if ($this->getAuthorizationService()->isGranted(PermissionInSystem::AUTHORISED_EXAMINER_LIST) === false) {
            return $this->redirect()->toUrl(PersonUrlBuilderWeb::home());
        }

        return $this->validateAeNumber(new AeSearchViewModel(), $this->getRequest());
    }

    private function validateAeNumber(AeSearchViewModel $viewModel, Request $request)
    {
        if ($request->isPost()) {
            $viewModel->setSearch($request->getPost(self::INPUT_SEARCH_NAME));

            $validator = new StringLength(array('max' => self::MAX_SEARCH_LENGTH));
            if (!$validator->isValid($viewModel->getSearch())) {
                $viewModel->setErrorMessage(self::INVALID_SEARCH_MESSAGE);
            } else {
                try {
                    $organisation = $this->getMapperFactory()->Organisation->getAuthorisedExaminerByNumber(
                        ['number' => $viewModel->getSearch()]
                    );

                    return $this->redirect()->toUrl($viewModel->getDetailPage($organisation->getId()));
                } catch (NotFoundException $e) {
                    $viewModel->setIsAeFound(false);
                }
            }
        }

        return $this->initViewModel($viewModel);
    }

    private function initViewModel(AeSearchViewModel $viewModel)
    {
        $breadcrumbs = [
            'AE Information' => '',
        ];

        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', self::PAGE_TITLE);
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);
        $viewModel->setMaxSearchLength(self::MAX_SEARCH_LENGTH);

        return new ViewModel(['viewModel' => $viewModel]);
    }
}
