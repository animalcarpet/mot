<?php

namespace Dashboard\Controller;

use Core\Controller\AbstractAuthActionController;
use DvsaCommon\UrlBuilder\UrlBuilder;
use Zend\View\Model\ViewModel;

/**
 * Class MyApplicationsController.
 */
class MyApplicationsController extends AbstractAuthActionController
{
    const ROUTE_MY_APPLICATIONS = 'user-home/my-applications';
    const PUBLIC_API_CLIENT = 'PublicApiClient';

    public function myApplicationsAction()
    {
        $personId = $this->getIdentity()->getUserId();
        $url = UrlBuilder::user($personId)->applicationsForUser()->toString();
        $applications = $this->getRestClient()->get($url);

        return new ViewModel(
            [
                'applications' => $applications['data'],
            ]
        );
    }
}
