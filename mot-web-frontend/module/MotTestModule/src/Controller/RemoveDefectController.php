<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Controller\AbstractAuthActionController;
use Zend\View\Model\ViewModel;

class RemoveDefectController extends AbstractAuthActionController
{
    /**
     * Handles the screen for removing a defect.
     *
     * See https://mot-rfr-production.herokuapp.com/rfr/removeDefect
     *
     * @return ViewModel
     */
    public function removeAction()
    {
        $motTestNumber = (int) $this->params()->fromRoute('motTestNumber');
        $categoryId = $this->params()->fromRoute('categoryId', 'N/A');
        $defectItemId = (int) $this->params()->fromRoute('defectItemId');

        $this->enableGdsLayout('Remove failure', '');

        return $this->createViewModel('defects/remove-defect.twig', [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $categoryId,
            'defectId' => 'N/A',
            'defectItemId' => $defectItemId,
            'type' => 'N/A',
            'storyId' => 'BL-2405',
        ]);
    }

    /**
     * @param string $template
     * @param array  $variables
     *
     * @return ViewModel
     */
    private function createViewModel($template, array $variables)
    {
        $viewModel = new ViewModel();
        $viewModel->setTemplate($template);
        $viewModel->setVariables($variables);

        return $viewModel;
    }
}
