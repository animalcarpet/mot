<?php

$env = getenv('APPLICATION_ENV') ? : 'production';
$mods = require_once('modules.list.php');

# The ability to load config files from a system controlled location (puppet)
# If the APPLICATION_CONFIG_PATH is set then this location is used to read
# the configs for the application. If this location is not set then the config
# files are loaded from the standard location within the respective vagrant box.
$path = getenv('APPLICATION_CONFIG_PATH')?: '/etc/dvsa/mot-web-frontend';

if ($env === 'development') {
    $mods[] = 'ZendDeveloperTools';
    $mods[] = 'Noiselabs\ZfDebugModule';
}

$appname = rtrim(file_get_contents('config/appname.txt'));

return [
    // This should be an array of module namespaces used in the application.
    'modules'                 => $mods,

    // This is to allow or disallow the view of the release of the application in the footer.
    // View Helper: $this->getReleaseTag()
    'footer_can_render_release_tag' => ($env !== 'production'),

    // These are various options for the listeners attached to the ModuleManager
    'module_listener_options' => [
        // This should be an array of paths in which modules reside.
        // If a string key is provided, the listener will consider that a module
        // namespace, the value of that key the specific path to that module's
        // Module class.
        'module_paths'      => [
            __DIR__ . '/../module',
            __DIR__ . '/../vendor',
        ],

        // An array of paths from which to glob configuration files after
        // modules are loaded. These effectively override configuration
        // provided by modules themselves. Paths may use GLOB_BRACE notation.
        'config_glob_paths' => [
            'config/static/*.php',
            sprintf('%s/{,*.}{global,local,%s,vagrant}.php', $path, $env)
        ],

        // Indicates if the configuration cache should be used or not.
        // This doesn't work for mot-web-frontend at time of writing due to a
        // wayward closure in the ZfcRbac config which can't be serialized by PHP.
        // So, we special-case mot-web-frontend for now.
        'config_cache_enabled'     => $env !== 'development',

        // The configuration cache key
        'config_cache_key'         => $appname,

        // Indicates if the module class map cache should be used or not
        'module_map_cache_enabled' => $env !== 'development',

        // The module class map cache key
        'module_map_cache_key'     => $appname,

        // The cache directory to use
        'cache_dir'                => sys_get_temp_dir(),

        // Indicates if module dependencies should be checked or not
        'check_dependencies'       => $env !== 'production',
    ]

    // Used to create an own service manager. May contain one or more child arrays.
    //'service_listener_options' => array(
    //     array(
    //         'service_manager' => $stringServiceManagerName,
    //         'config_key'      => $stringConfigKey,
    //         'interface'       => $stringOptionalInterface,
    //         'method'          => $stringRequiredMethodName,
    //     ),
    // )

    // Initial configuration with which to seed the ServiceManager.
    // Should be compatible with Zend\ServiceManager\Config.
    //'service_manager' => array(),
];
