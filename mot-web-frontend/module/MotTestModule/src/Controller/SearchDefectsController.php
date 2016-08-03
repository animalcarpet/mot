<?php
/**
 * This file is part of the DVSA MOT Frontend project.
 *
 * @link https://gitlab.motdev.org.uk/mot/mot
 */

namespace Dvsa\Mot\Frontend\MotTestModule\Controller;

use Core\Controller\AbstractAuthActionController;
use Zend\View\Model\ViewModel;

class SearchDefectsController extends AbstractAuthActionController
{
    /**
     * Handles the root categories view when the search functionality is enabled. No category is selected.
     *
     * See https://mot-rfr-production.herokuapp.com/rfr/search
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $motTestNumber = $this->params()->fromRoute('motTestNumber');

        $this->enableGdsLayout('Search for a defect', '');

        return $this->createViewModel('defects/categories.twig', [
            'motTestNumber' => $motTestNumber,
            'storyId' => 'BL-50',
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
