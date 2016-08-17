<?php
namespace Dvsa\Mot\Behat\Support\Scope;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;

class BeforeBehatScenarioScope
{
    /**
     * @var BeforeScenarioScope
     */
    private static $scope;

    public static function set(BeforeScenarioScope $scope = null)
    {
        static::$scope = $scope;
    }

    public static function isTransformerDisabled()
    {
        if (static::$scope->getScenario()->hasTag("transform") || static::$scope->getFeature()->hasTag("transform")) {
            return false;
        }

        return true;
    }

    public static function get()
    {
        return static::$scope;
    }
}