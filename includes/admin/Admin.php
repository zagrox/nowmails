<?php
namespace NowMails\Admin;

class Admin {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            NOWMAILS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            NOWMAILS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'nowmailsAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nowmails_admin_nonce')
        ));
    }

    public function add_plugin_admin_menu() {
        // Add main menu
        add_menu_page(
            __('NowMails', 'nowmails'),
            __('NowMails', 'nowmails'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_dashboard_page'),
            'dashicons-email',
            30
        );

        // Add submenus
        add_submenu_page(
            $this->plugin_name,
            __('Dashboard', 'nowmails'),
            __('Dashboard', 'nowmails'),
            'manage_options',
            $this->plugin_name,
            array($this, 'display_plugin_dashboard_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Subaccounts', 'nowmails'),
            __('Subaccounts', 'nowmails'),
            'manage_options',
            $this->plugin_name . '-subaccounts',
            array($this, 'display_subaccounts_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Branding', 'nowmails'),
            __('Branding', 'nowmails'),
            'manage_options',
            $this->plugin_name . '-branding',
            array($this, 'display_branding_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('API Settings', 'nowmails'),
            __('API Settings', 'nowmails'),
            'manage_options',
            $this->plugin_name . '-api',
            array($this, 'display_api_settings_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Billing', 'nowmails'),
            __('Billing', 'nowmails'),
            'manage_options',
            $this->plugin_name . '-billing',
            array($this, 'display_billing_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Verification', 'nowmails'),
            __('Verification', 'nowmails'),
            'manage_options',
            $this->plugin_name . '-verification',
            array($this, 'display_verification_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Analytics', 'nowmails'),
            __('Analytics', 'nowmails'),
            'manage_options',
            $this->plugin_name . '-analytics',
            array($this, 'display_analytics_page')
        );

        add_submenu_page(
            $this->plugin_name,
            __('Settings', 'nowmails'),
            __('Settings', 'nowmails'),
            'manage_options',
            $this->plugin_name . '-settings',
            array($this, 'display_settings_page')
        );
    }

    public function add_action_links($links) {
        $settings_link = array(
            '<a href="' . admin_url('admin.php?page=' . $this->plugin_name . '-settings') . '">' . __('Settings', 'nowmails') . '</a>',
        );
        return array_merge($settings_link, $links);
    }

    public function register_settings() {
        register_setting($this->plugin_name, 'nowmails_api_key');
        register_setting($this->plugin_name, 'nowmails_smtp_host');
        register_setting($this->plugin_name, 'nowmails_smtp_port');
        register_setting($this->plugin_name, 'nowmails_smtp_username');
        register_setting($this->plugin_name, 'nowmails_smtp_password');
        register_setting($this->plugin_name, 'nowmails_branding_logo');
        register_setting($this->plugin_name, 'nowmails_branding_company_name');
        register_setting($this->plugin_name, 'nowmails_branding_primary_color');
    }

    // Page display methods
    public function display_plugin_dashboard_page() {
        include_once NOWMAILS_PLUGIN_DIR . 'includes/admin/partials/dashboard.php';
    }

    public function display_subaccounts_page() {
        include_once NOWMAILS_PLUGIN_DIR . 'includes/admin/partials/subaccounts.php';
    }

    public function display_branding_page() {
        include_once NOWMAILS_PLUGIN_DIR . 'includes/admin/partials/branding.php';
    }

    public function display_api_settings_page() {
        include_once NOWMAILS_PLUGIN_DIR . 'includes/admin/partials/api-settings.php';
    }

    public function display_billing_page() {
        include_once NOWMAILS_PLUGIN_DIR . 'includes/admin/partials/billing.php';
    }

    public function display_verification_page() {
        include_once NOWMAILS_PLUGIN_DIR . 'includes/admin/partials/verification.php';
    }

    public function display_analytics_page() {
        include_once NOWMAILS_PLUGIN_DIR . 'includes/admin/partials/analytics.php';
    }

    public function display_settings_page() {
        include_once NOWMAILS_PLUGIN_DIR . 'includes/admin/partials/settings.php';
    }
} 