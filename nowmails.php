<?php
/**
 * Plugin Name: NowMails
 * Plugin URI: https://nowmails.com
 * Description: A powerful email marketing solution integrated with ElasticEmail API v2 for resellers
 * Version: 1.0.0
 * Author: ZAGROX
 * Author URI: https://zagrox.com
 * Text Domain: nowmails
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('NOWMAILS_VERSION', '1.0.0');
define('NOWMAILS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NOWMAILS_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NOWMAILS_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Autoloader for plugin classes
spl_autoload_register(function ($class) {
    $prefix = 'NowMails\\';
    $base_dir = NOWMAILS_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Initialize the plugin
function nowmails_init() {
    // Load text domain
    load_plugin_textdomain('nowmails', false, dirname(NOWMAILS_PLUGIN_BASENAME) . '/languages');

    // Initialize main plugin class
    if (class_exists('NowMails\\Plugin')) {
        $plugin = new NowMails\Plugin();
        $plugin->init();
    }
}
add_action('plugins_loaded', 'nowmails_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    require_once NOWMAILS_PLUGIN_DIR . 'includes/Activator.php';
    NowMails\Activator::activate();
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    require_once NOWMAILS_PLUGIN_DIR . 'includes/Deactivator.php';
    NowMails\Deactivator::deactivate();
}); 