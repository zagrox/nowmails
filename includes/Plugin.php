<?php
namespace NowMails;

class Plugin {
    private $loader;
    private $plugin_name;
    private $version;

    public function __construct() {
        $this->plugin_name = 'nowmails';
        $this->version = NOWMAILS_VERSION;
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies() {
        require_once NOWMAILS_PLUGIN_DIR . 'includes/Loader.php';
        require_once NOWMAILS_PLUGIN_DIR . 'includes/i18n.php';
        require_once NOWMAILS_PLUGIN_DIR . 'includes/admin/Admin.php';
        require_once NOWMAILS_PLUGIN_DIR . 'includes/api/ElasticEmail.php';
        require_once NOWMAILS_PLUGIN_DIR . 'includes/frontend/Frontend.php';

        $this->loader = new Loader();
    }

    private function set_locale() {
        $plugin_i18n = new i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks() {
        $plugin_admin = new Admin($this->get_plugin_name(), $this->get_version());

        // Add menu items
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_plugin_admin_menu');

        // Add Settings link to the plugin
        $this->loader->add_filter('plugin_action_links_' . NOWMAILS_PLUGIN_BASENAME, $plugin_admin, 'add_action_links');

        // Register settings
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');

        // Enqueue admin scripts and styles
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    private function define_public_hooks() {
        $plugin_public = new Frontend($this->get_plugin_name(), $this->get_version());

        // Enqueue public scripts and styles
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');
    }

    public function run() {
        $this->loader->run();
    }

    public function get_plugin_name() {
        return $this->plugin_name;
    }

    public function get_version() {
        return $this->version;
    }

    public function init() {
        $this->run();
    }
} 