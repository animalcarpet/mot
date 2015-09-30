<?php

namespace DvsaMotTest\Controller;

use DvsaCommon\Dto\MotTesting\MotTestOptionsDto;
use DvsaCommon\Enum\MotTestTypeCode;
use DvsaCommon\UrlBuilder\UrlBuilder;
use DvsaMotTest\Presenter\MotTestOptionsPresenter;
use Zend\View\Model\ViewModel;

class MotTestOptionsController extends AbstractDvsaMotTestController
{
    const ROUTE_MOT_TEST_OPTIONS = 'mot-test/options';

    const TEMPLATE_MOT_TEST_OPTIONS = 'dvsa-mot-test/mot-test/mot-test-options.phtml';

    const PAGE_TITLE_TEST = 'MOT test started';
    const PAGE_TITLE_RETEST = 'MOT retest started';

    const PAGE_SUB_TITLE_TRAINING = 'Training test';
    const PAGE_SUB_TITLE_TEST = 'MOT testing';

    public function motTestOptionsAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');

        $dto = MotTestOptionsDto::fromArray(
            $this->getRestClient()->get(UrlBuilder::motTestOptions($motTestNumber)->toString())['data']
        );

        $presenter = new MotTestOptionsPresenter($dto);

        $pageTitle = self::PAGE_TITLE_TEST;

        if ($presenter->isMotTestRetest()) {
            $pageTitle = self::PAGE_TITLE_RETEST;
        }

        $this->layout()->setVariable('pageTitle', $pageTitle);

        if ($dto->getMotTestTypeDto()->getCode() === MotTestTypeCode::DEMONSTRATION_TEST_FOLLOWING_TRAINING) {
            $this->layout()->setVariable('pageSubTitle', self::PAGE_SUB_TITLE_TRAINING);
        } else {
            $this->layout()->setVariable('pageSubTitle', self::PAGE_SUB_TITLE_TEST);
        }

        $viewModel = new ViewModel(['presenter' => $presenter]);
        $viewModel->setTemplate(self::TEMPLATE_MOT_TEST_OPTIONS);

        return $viewModel;
    }
}
