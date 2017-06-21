<?php

namespace Core\View\Renderer;

use Zend\View\Renderer\PhpRenderer;

class MotPhpRenderer extends PhpRenderer
{
    /**
     * Whole render method is extended to catch PHP7 Errors and throw Exception, Errors are not catched in Zend
     *
     * Processes a view script and returns the output.
     * @param string|\Zend\View\Model\ModelInterface $nameOrModel
     * @param array $values
     * @return string|void
     * @throws \Exception
     */
    public function render($nameOrModel, $values = null)
    {
        try {
            return parent::render($nameOrModel, $values);
        } catch (\Error $er) {
            ob_end_clean();

            throw new \Exception(sprintf(
                "Error (%s) %s on line %s in %s",
                $er->getCode(), $er->getMessage(), $er->getLine(), $er->getFile()
            ));
        }
    }
}