<?php

namespace Core\Action;

use DvsaCommon\Utility\ArrayUtils;

abstract class AbstractActionResult implements ActionResultInterface
{
    private $errorMessages = [];
    private $successMessages = [];
    /** @var FlashMessage[] */
    private $flashMessages = [];

    public function getErrorMessages()
    {
        return $this->errorMessages;
    }

    public function addErrorMessage($error)
    {
        $this->errorMessages[] = $error;

        return $this;
    }

    public function addErrorMessages(array $errors)
    {
        foreach ($errors as $error) {
            $this->addErrorMessage($error);
        }

        return $this;
    }

    public function getSuccessMessages()
    {
        return $this->successMessages;
    }

    public function addSuccessMessage($successMessage)
    {
        $this->successMessages[] = $successMessage;

        return $this;
    }

    public function addSuccessMessages(array $successMessages)
    {
        foreach ($successMessages as $successMessage) {
            $this->addErrorMessage($successMessage);
        }

        return $this;
    }

    public function addFlashMessage(FlashNamespace $namespace, $message)
    {
        $this->flashMessages[] = new FlashMessage($namespace, $message);
    }

    public function getFlashMessages(FlashNamespace $namespaceFilter = null)
    {
        if ($namespaceFilter === null) {
            return $this->flashMessages;
        }

        return ArrayUtils::filter(
            $this->flashMessages,
            function (FlashMessage $message) use ($namespaceFilter) {
                return $message->getNamespace()->equals($namespaceFilter);
            });
    }
}
