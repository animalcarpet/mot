<?php

namespace Vehicle;

/**
 * Class Module.
 */
class Module
{
    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return require __DIR__.'/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
    }
}
