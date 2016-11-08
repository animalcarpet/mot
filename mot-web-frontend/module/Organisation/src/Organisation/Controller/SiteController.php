<?php

namespace Organisation\Controller;

use Core\Service\MotFrontendAuthorisationServiceInterface;
use DvsaClient\MapperFactory;
use DvsaCommon\Auth\PermissionAtOrganisation;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Enum\OrganisationSiteStatusCode;
use DvsaCommon\Enum\OrganisationSiteStatusName;
use DvsaCommon\HttpRestJson\Exception\GeneralRestException;
use DvsaCommon\HttpRestJson\Exception\NotFoundException;
use DvsaCommon\HttpRestJson\Exception\ValidationException;
use DvsaCommon\UrlBuilder\AuthorisedExaminerUrlBuilderWeb;
use DvsaCommon\Utility\AddressUtils;
use DvsaMotTest\Controller\AbstractDvsaMotTestController;
use Organisation\Form\AeLinkSiteForm;
use Organisation\Form\AeUnlinkSiteForm;
use Organisation\ViewModel\AuthorisedExaminer\AeFormViewModel;
use Organisation\ViewModel\AuthorisedExaminer\AeSiteUnlinkModel;
use SebastianBergmann\Exporter\Exception;
use Zend\View\Model\ViewModel;

class SiteController extends AbstractDvsaMotTestController
{
    const LINK_TITLE = 'Associate a site';
    const UNLINK_TITLE = 'Remove site association';
    const AE_TEXT = 'Authorised Examiner';

    const ERR_ORG_SITE_LINK_NOT_FOUND = "Association between Authorised Examiner and Site not found";

    const ROUTE_INDEX = 'vehicle-testing-station';
    const ROUTE_LINK = 'authorised-examiner/site/link';
    const ROUTE_UNLINK = 'authorised-examiner/site/unlink';

    /**
     * @var MotFrontendAuthorisationServiceInterface
     */
    private $auth;
    /**
     * @var MapperFactory
     */
    private $mapper;

    public function __construct(
        MotFrontendAuthorisationServiceInterface $auth,
        MapperFactory $mapper
    ) {
        $this->auth = $auth;
        $this->mapper = $mapper;
    }

    /**
     * @return \Zend\Http\Response|ViewModel
     */
    public function linkAction()
    {
        $orgId = $this->params('id');

        $this->auth->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_SITE_LINK, $orgId);

        $aeViewUrl = AuthorisedExaminerUrlBuilderWeb::of($orgId);

        //  logical block :: get data from api
        $organisation = $this->mapper->Organisation->getAuthorisedExaminer($orgId);

        //  logical block :: prepare form and model
        $form = new AeLinkSiteForm();
        $form
            ->setMaxInputLength(10)
            ->setSites($this->mapper->OrganisationSites->fetchAllUnlinkedSites());

        $request = $this->getRequest();
        if ($request->isPost()) {
            $form->fromPost($request->getPost());

            try {
                $this->mapper->OrganisationSites->createSiteLink($orgId, $form->getSiteNumber());
                return $this->redirect()->toUrl($aeViewUrl);

            } catch (ValidationException $e) {
                $form->addErrors($e->getErrors());
            } catch (NotFoundException $e) {
                $this->addErrorMessages($e->getMessage());
            }
        }

        //  prepare model
        $model = new AeFormViewModel();
        $model
            ->setForm($form)
            ->setCancelUrl($aeViewUrl);

        //  logical block :: prepare view
        $subTitle = self::AE_TEXT . ' - ' .
            $organisation->getAuthorisedExaminerAuthorisation()->getAuthorisedExaminerRef();

        $breadcrumbs = [$organisation->getName() => $aeViewUrl->toString()];

        return $this->prepareViewModel(
            new ViewModel(['model' => $model]), self::LINK_TITLE, $subTitle, $breadcrumbs
        );
    }

    public function unlinkAction()
    {
        $linkId = $this->params('linkId');

        //  logical block :: get data from api
        $link = $this->mapper->OrganisationSites->getSiteLink($linkId);

        $organisationIdFromRoute = $this->params('id');

        $organisation = $link->getOrganisation();

        $site = $link->getSite();

        $orgId = $organisation->getId();

        //  check permission
        $this->auth->assertGrantedAtOrganisation(PermissionAtOrganisation::AE_SITE_UNLINK, $orgId);

        //  logical block :: prepare form and model
        $aeViewUrl = AuthorisedExaminerUrlBuilderWeb::of($orgId)->toString();

        $form = new AeUnlinkSiteForm();
        $form->setStatuses($this->getUnlinkStatuses());

        if (is_null($organisationIdFromRoute) || $orgId != $organisationIdFromRoute) {
            $this->addErrorMessages(self::ERR_ORG_SITE_LINK_NOT_FOUND);
        } else {
            /* @var \Zend\Http\Request $request */
            $request = $this->getRequest();

            if ($request->isPost()) {
                $form->fromPost($request->getPost());
                try {
                    $this->mapper->OrganisationSites->changeSiteLinkStatus($linkId, $form->getStatus());

                    return $this->redirect()->toUrl($aeViewUrl);
                } catch (ValidationException $e) {
                    $form->addErrors($e->getErrors());
                } catch (GeneralRestException $e) {
                    $this->addErrorMessages($e->getMessage());
                }
            }
        }

        //  create a model
        $model = new AeSiteUnlinkModel();
        $model
            ->setForm($form)
            ->setSite($site)
            ->setCancelUrl($aeViewUrl);

        //  logical block :: prepare view
        $orgName = $organisation->getName();

        $overTitle = self::AE_TEXT . ' - ' .
            $organisation->getAuthorisedExaminerAuthorisation()->getAuthorisedExaminerRef();

        $breadcrumbs = [$orgName => $aeViewUrl];
        $subTitle = $orgName . ' - ' .
            AddressUtils::stringify($organisation->getRegisteredCompanyContactDetail()->getAddress());

        return $this->prepareViewModel(
            new ViewModel(['model' => $model]), self::UNLINK_TITLE, $overTitle, $breadcrumbs, $subTitle
        );
    }

    /**
     * Prepare the view model for all the step of the create ae
     *
     * @param ViewModel $view
     * @param string    $title
     * @param string    $subtitle
     * @param null      $breadcrumbs
     * @param array     $progress
     * @param string    $template
     *
     * @return ViewModel
     */
    private function prepareViewModel(
        ViewModel $view,
        $title,
        $subtitle,
        $breadcrumbs = null,
        $tertiary = null,
        $progress = null,
        $template = null
    ) {
        //  logical block:: prepare view
        $this->layout('layout/layout-govuk.phtml');
        $this->layout()->setVariable('pageTitle', $title);
        $this->layout()->setVariable('pageSubTitle', $subtitle);
        $this->layout()->setVariable('pageTertiaryTitle', $tertiary);

        if ($progress !== null) {
            $this->layout()->setVariable('progress', $progress);
        }

        $breadcrumbs = (is_array($breadcrumbs) ? $breadcrumbs : []) + [$title => ''];
        $this->layout()->setVariable('breadcrumbs', ['breadcrumbs' => $breadcrumbs]);

        return $template !== null ? $view->setTemplate($template) : $view;
    }

    private function getUnlinkStatuses()
    {
        return [
            OrganisationSiteStatusCode::SURRENDERED => OrganisationSiteStatusName::SURRENDERED,
            OrganisationSiteStatusCode::WITHDRAWN   => OrganisationSiteStatusName::WITHDRAWN,
        ];
    }
}
