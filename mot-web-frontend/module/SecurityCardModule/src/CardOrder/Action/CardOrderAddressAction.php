<?php

namespace Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Action;

use Core\Action\ActionResult;
use Core\Action\RedirectToRoute;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Form\SecurityCardAddressForm;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderNewSecurityCardSessionService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardStepService;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\ViewModel\CardOrderAddressViewModel;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;
use Dvsa\Mot\Frontend\SecurityCardModule\CardOrder\Service\OrderSecurityCardAddressService;
use DvsaCommon\InputFilter\Registration\AddressInputFilter;
use Zend\Form\Form;
use Zend\Http\Request;

class CardOrderAddressAction
{
    const ADDRESS_PAGE_TITLE = "Choose a delivery address";
    const ADDRESS_PAGE_SUBTITLE = "Order a security card";

    /**
     * @var OrderSecurityCardAddressService $orderSecurityCardAddressService
     */
    private $orderSecurityCardAddressService;

    /**
     * @var OrderNewSecurityCardSessionService $sessionService
     */
    private $sessionService;

    /**
     * @var OrderSecurityCardStepService $stepService
     */
    private $stepService;

    /**
     * @var CardOrderProtection $cardOrderProtection
     */
    private $cardOrderProtection;

    public function __construct(OrderSecurityCardAddressService $orderSecurityCardAddressService,
                                OrderNewSecurityCardSessionService $sessionService,
                                OrderSecurityCardStepService $stepService,
                                CardOrderProtection $cardOrderProtection )
    {
        $this->orderSecurityCardAddressService = $orderSecurityCardAddressService;
        $this->sessionService = $sessionService;
        $this->stepService = $stepService;
        $this->cardOrderProtection = $cardOrderProtection;
    }

    public function execute(Request $request, $userId)
    {
        $cardOrderProtectionResult = $this->cardOrderProtection->checkAuthorisation($userId);

        if ($cardOrderProtectionResult instanceof RedirectToRoute) {
            return $cardOrderProtectionResult;
        }

        if(!$this->stepService->isAllowedOnStep($userId, OrderSecurityCardStepService::ADDRESS_STEP)) {
            return new RedirectToRoute('security-card-order/new', ['userId' => $userId]);
        }

        $addresses = $this->orderSecurityCardAddressService->getSecurityCardOrderAddresses($userId);

        if ($request->isPost()) {
            $form = new SecurityCardAddressForm($addresses);
            $postData = $request->getPost()->toArray();
            $form->setData($postData);

            if ($form->isValid()) {
                $this->savePostDataToSession($postData, $userId);
                $this->stepService->updateStepStatus($userId, OrderSecurityCardStepService::REVIEW_STEP, true);
                return new RedirectToRoute('security-card-order/review', ['userId' => $userId]);
            } else {
                $result = new ActionResult();
                $viewModel = new CardOrderAddressViewModel();
                $viewModel->setUserId($userId);
                $viewModel->setForm($form);
                $result->setViewModel($viewModel);
                $result->layout()->setPageTitle(self::ADDRESS_PAGE_TITLE);
                $result->layout()->setPageSubTitle(self::ADDRESS_PAGE_SUBTITLE);
                $result->setTemplate('2fa/card-order/address');
                return $result;
            }
        }

        $result = new ActionResult();
        $form = new SecurityCardAddressForm($addresses);
        $form = $this->populateForm($form, $userId);
        $viewModel = new CardOrderAddressViewModel();
        $viewModel->setForm($form);
        $viewModel->setUserId($userId);
        $result->setViewModel($viewModel);
        $result->layout()->setPageTitle(self::ADDRESS_PAGE_TITLE);
        $result->layout()->setPageSubTitle(self::ADDRESS_PAGE_SUBTITLE);
        $result->setTemplate('2fa/card-order/address');
        return $result;
    }


    /**
     * @param array $values
     * @param $userId
     * @throws \Exception
     */
    public function savePostDataToSession(array $values, $userId)
    {
        $addressData = [];
        if (is_array($values) && count($values)) {
            if($values[SecurityCardAddressForm::ADDRESS_RADIOS] != SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE) {
                $address = $this->getAddressInformationByAddressChoice($values['addressChoice'], $userId);
                (isset($address[SecurityCardAddressForm::NAME_FIELD_KEY]) && $address[SecurityCardAddressForm::NAME_FIELD_KEY] != 'Home') ? $addressData['vtsName'] = $address[SecurityCardAddressForm::NAME_FIELD_KEY] : $addressData['vtsName'] = '';
                $addressData['address1'] = $address['addressLine1'];
                (isset($address['addressLine2'])) ? $addressData['address2'] = $address['addressLine2'] : $addressData['address2'] = '';
                (isset($address['addressLine3'])) ? $addressData['address3'] = $address['addressLine3'] : $addressData['address3'] = '';
                $addressData['townOrCity'] = $address['town'];
                $addressData['postcode'] = strtoupper($address['postcode']);
                $addressData['addressChoice'] = $values['addressChoice'];
            } else {
                $addressData['vtsName'] = '';
                $addressData['address1'] = $values[AddressInputFilter::FIELD_ADDRESS_1];
                $addressData['address2'] = $values[AddressInputFilter::FIELD_ADDRESS_2];
                $addressData['address3'] = $values[AddressInputFilter::FIELD_ADDRESS_3];
                $addressData['townOrCity'] = $values[AddressInputFilter::FIELD_TOWN_OR_CITY];
                $addressData['postcode'] = strtoupper($values[AddressInputFilter::FIELD_POSTCODE]);
                $addressData['addressChoice'] = $values['addressChoice'];
            }

            $sessionData = $this->sessionService->loadByGuid($userId);
            $sessionData[OrderNewSecurityCardSessionService::ADDRESS_STEP_STORE] = $addressData;
            $this->sessionService->saveToGuid($userId, $sessionData);
        }
    }

    /**
     * @param SecurityCardAddressForm $form
     * @param $userId
     * @return SecurityCardAddressForm
     */
    private function populateForm(SecurityCardAddressForm $form, $userId)
    {
        $addressData = $this->sessionService->loadByGuid($userId)[OrderNewSecurityCardSessionService::ADDRESS_STEP_STORE];

        if ($addressData != null) {
            if ($addressData[SecurityCardAddressForm::ADDRESS_RADIOS] === SecurityCardAddressForm::CUSTOM_ADDRESS_VALUE) {
                $form->setData($addressData);
            } else {
                $form->getAddressRadios()->setValue($addressData['addressChoice']);
            }
        }
        return $form;
    }

    /**
     * @param $indexKey
     * @return mixed
     * @throws \Exception
     */
    private function getAddressInformationByAddressChoice($indexKey, $userId) {
        $addresses = $this->sessionService->loadByGuid($userId)[OrderNewSecurityCardSessionService::ADDRESS_SESSION_STORE];

        foreach($addresses as $key => $address) {
            if($indexKey == $key) {
                return $address;
            }
        }
        throw new \Exception('Address option of ' . $indexKey . ' not found in session data');
    }
}