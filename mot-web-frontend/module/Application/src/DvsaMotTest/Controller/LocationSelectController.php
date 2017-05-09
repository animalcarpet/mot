<?php

namespace DvsaMotTest\Controller;

use Application\Service\LoggedInUserManager;
use DvsaMotTest\Data\TesterInProgressTestNumberResource;
use Dashboard\Controller\UserHomeController;
use Zend\Http\Request;
use Zend\View\Model\ViewModel;
use DvsaMotTest\Model\LocationSelect;

/**
 * Class LocationSelectController.
 */
class LocationSelectController extends AbstractDvsaMotTestController
{
    const GARAGE_REQUIRED_ERROR = "You have to choose the garage you're currently working in.";

    const ROUTE = 'location-select';

    public function indexAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        $currentUserVts = $this->getIdentity()->getCurrentVts();
        $testInProgressId = $this->getInProgressTestNumber();
        if (!empty($testInProgressId) && !empty($currentUserVts)) {
            throw new \Exception('Test location can not be changed during MOT test');
        }

        /** @var LoggedInUserManager $loggedInUserManager */
        $loggedInUserManager = $this->getServiceLocator()->get('LoggedInUserManager');

        $form = $this->getForm(new LocationSelect());
        if ($request->isPost()) {
            $form->setData($request->getPost());
            if ($form->isValid()) {
                $vtsId = (int) $request->getPost('vtsId');
                $loggedInUserManager->changeCurrentLocation($vtsId);

                return $this->redirectBack();
            } else {
                $loggedInUserManager->clearCurrentLocation();
                $this->addErrorMessages(self::GARAGE_REQUIRED_ERROR);
            }
        }
        $tester = $loggedInUserManager->getAllVtsWithSlotBalance();

        // if tester has only single site, he shouldn't even see the form
        if ($request->isGet() && count($tester['vtsSites']) == 1) {
            $vtsId = $tester['vtsSites'][0]['id'];
            $loggedInUserManager->changeCurrentLocation($vtsId);

            return $this->redirectBack();
        }

        $form->setData($request->getQuery());
        $userDetails = $this->getUserDisplayDetails();

        return new ViewModel(
            [
                'form' => $form,
                'userDetails' => $userDetails,
                'vtsSites' => $tester['vtsSites'],
                'backRoute' => $this->params('back'),
            ]
        );
    }

    private function redirectBack()
    {
        $container = $this->getServiceLocator()->get('LocationSelectContainerHelper');
        $config = $container->fetchConfig();
        $container->clearConfig();

        if (!isset($config['route'])) {
            return $this->redirect()->toRoute(UserHomeController::ROUTE);
        }

        return $this->redirect()->toRoute($config['route'], $config['params']);
    }

    /**
     * @return int|null
     */
    private function getInProgressTestNumber()
    {
        /** @var TesterInProgressTestNumberResource $testerInProgressTestNumberResource */
        $testerInProgressTestNumberResource = $this->getServiceLocator()->get(
            TesterInProgressTestNumberResource::class
        );

        return $testerInProgressTestNumberResource->get($this->getIdentity()->getUserId());
    }
}
