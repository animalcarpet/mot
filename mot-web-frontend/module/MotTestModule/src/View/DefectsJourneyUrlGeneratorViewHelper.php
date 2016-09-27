<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\View;

use Dvsa\Mot\Frontend\MotTestModule\Exception\RouteNotAllowedInContextException;
use Zend\View\Helper\AbstractHelper;

/**
 * Makes the DefectsJourneyUrlGenerator service available in the view layer.
 */
class DefectsJourneyUrlGeneratorViewHelper extends AbstractHelper
{
    /**
     * @var DefectsJourneyUrlGenerator
     */
    private $defectsJourneyUrlGenerator;

    /**
     * @var DefectsJourneyContextProvider
     */
    private $contextProvider;

    /**
     * DefectsJourneyUrlGeneratorViewHelper constructor.
     *
     * @param DefectsJourneyUrlGenerator    $defectsJourneyUrlGenerator
     * @param DefectsJourneyContextProvider $contextProvider
     */
    public function __construct(DefectsJourneyUrlGenerator $defectsJourneyUrlGenerator,
                                DefectsJourneyContextProvider $contextProvider)
    {
        $this->defectsJourneyUrlGenerator = $defectsJourneyUrlGenerator;
        $this->contextProvider = $contextProvider;
    }

    /**
     * @return $this
     */
    public function __invoke()
    {
        return $this;
    }

    /**
     * Get defects journey context for current location.
     *
     * @return string
     */
    public function getContext()
    {
        return $this->contextProvider->getContext();
    }

    /**
     * Get url to addDefect action.
     *
     * @param int|string $defectId
     * @param string     $defectType
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toAddDefect($defectId, $defectType)
    {
        return $this->defectsJourneyUrlGenerator->toAddDefect($defectId, $defectType);
    }

    /**
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toAddManualAdvisory()
    {
        return $this->defectsJourneyUrlGenerator->toAddManualAdvisory();
    }

    /**
     * @param int|string $identifiedDefectId
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toEditDefect($identifiedDefectId)
    {
        return $this->defectsJourneyUrlGenerator->toEditDefect($identifiedDefectId);
    }

    /**
     * @param int|string $identifiedDefectId
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toRemoveDefect($identifiedDefectId)
    {
        return $this->defectsJourneyUrlGenerator->toRemoveDefect($identifiedDefectId);
    }

    /**
     * @param int|string $identifiedDefectId
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toRepairDefect($identifiedDefectId)
    {
        return $this->defectsJourneyUrlGenerator->toRepairDefect($identifiedDefectId);
    }

    /**
     * @param int|string $identifiedDefectId
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function toUndoRepairDefect($identifiedDefectId)
    {
        return $this->defectsJourneyUrlGenerator->toUndoRepairDefect($identifiedDefectId);
    }

    /**
     * get "back" url from add/add manual advisory/edit/remove defect actions.
     *
     * @throws RouteNotAllowedInContextException
     *
     * @return string
     */
    public function goBack()
    {
        return $this->defectsJourneyUrlGenerator->goBack();
    }
}
