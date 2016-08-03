<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Controller\AbstractAuthActionController;
use Zend\View\Model\ViewModel;

class EditDefectController extends AbstractAuthActionController
{
    /**
     * Handles the screen for editing a defect.
     *
     * Heroku screen not available yet.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');
        $categoryId = $this->params()->fromRoute('categoryId', 'N/A');
        $defectItemId = $this->params()->fromRoute('defectItemId');

        $this->enableGdsLayout('', '');

        return $this->createViewModel('defects/edit-defect.twig', [
            'motTestNumber' => $motTestNumber,
            'categoryId' => $categoryId,
            'defectId' => 'N/A',
            'defectItemId' => $defectItemId,
            'type' => 'N/A',
            'storyId' => 'BL-2406',
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
