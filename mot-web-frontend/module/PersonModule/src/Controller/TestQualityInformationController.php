<?php
namespace Dvsa\Mot\Frontend\PersonModule\Controller;

use Dvsa\Mot\Frontend\PersonModule\Action\TestQualityComponentBreakdownAction;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\TestQualityBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\View\ContextProvider;
use DvsaCommon\Auth\MotIdentityProviderInterface;
use Core\Controller\AbstractDvsaActionController;
use Dvsa\Mot\Frontend\PersonModule\Action\TestQualityAction;
use Dvsa\Mot\Frontend\PersonModule\Breadcrumbs\PersonProfileBreadcrumbs;
use Dvsa\Mot\Frontend\PersonModule\View\PersonProfileUrlGenerator;
use DvsaCommon\Constants\FeatureToggle;
use DvsaCommon\Factory\AutoWire\AutoWireableInterface;

class TestQualityInformationController extends AbstractDvsaActionController implements AutoWireableInterface
{
    /** @var  TestQualityComponentBreakdownAction */
    private $componentBreakdownAction;
    private $contextProvider;
    private $testQualityBreadcrumbs;

    /** @var  TestQualityAction $testQualityAction */
    private $testQualityAction;

    /** @var PersonProfileBreadcrumbs $personProfileBreadcrumbs */
    private $personProfileBreadcrumbs;

    /** @var MotIdentityProviderInterface identityProvider */
    private $identityProvider;

    public function __construct(
        TestQualityComponentBreakdownAction $componentBreakdownAction,
        TestQualityAction $testQualityAction,
        TestQualityBreadcrumbs $testQualityBreadcrumbs,
        ContextProvider $contextProvider,
        PersonProfileBreadcrumbs $personProfileBreadcrumbs,
        PersonProfileUrlGenerator $personProfileUrlGenerator,
        MotIdentityProviderInterface $identityProvider
    ) {
        $this->componentBreakdownAction = $componentBreakdownAction;
        $this->testQualityAction = $testQualityAction;
        $this->contextProvider = $contextProvider;
        $this->testQualityBreadcrumbs = $testQualityBreadcrumbs;
        $this->personProfileBreadcrumbs = $personProfileBreadcrumbs;
        $this->personProfileUrlGenerator = $personProfileUrlGenerator;
        $this->identityProvider = $identityProvider;
    }

    public function componentBreakdownAction()
    {
        $testerId = $this->getTesterId();
        $group = $this->params('group');
        $month = (int)$this->params('month');
        $year = (int)$this->params('year');

        $actionResult = $this->componentBreakdownAction->execute($testerId, $group, $month, $year,
            $this->url(), $this->params()->fromRoute());

        return $this->applyActionResult($actionResult);
    }

    private function getTesterId()
    {
        return $this->contextProvider->getContext() == ContextProvider::YOUR_PROFILE_CONTEXT ?
            (int)$this->identityProvider->getIdentity()->getUserId() : (int)$this->params("id");
    }

    public function testQualityInformationAction()
    {
        $targetPersonId = (int)($this->params()->fromRoute('id') ? : $this->identityProvider->getIdentity()->getUserId());
        $month = (int)$this->params()->fromRoute('month');
        $year = (int)$this->params()->fromRoute('year');

        return $this->applyActionResult(
            $this->testQualityAction->execute(
                $targetPersonId,
                $month,
                $year,
                $this->url(),
                $this->params()->fromRoute()
            )
        );
    }
}
