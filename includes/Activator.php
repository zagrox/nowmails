<?php
namespace NowMails;

class Activator {
    public static function activate() {
        self::create_tables();
        self::create_default_options();
        self::create_capabilities();
        self::schedule_cron_jobs();
    }

    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Events table
        $table_name = $wpdb->prefix . 'nowmails_events';
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_data longtext NOT NULL,
            created_at datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY event_type (event_type),
            KEY created_at (created_at)
        ) $charset_collate;";

        // Subaccounts table
        $table_name = $wpdb->prefix . 'nowmails_subaccounts';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            public_account_id varchar(50) NOT NULL,
            name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            api_key varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY public_account_id (public_account_id),
            KEY status (status)
        ) $charset_collate;";

        // Templates table
        $table_name = $wpdb->prefix . 'nowmails_templates';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            template_id varchar(50) NOT NULL,
            name varchar(255) NOT NULL,
            content longtext NOT NULL,
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY template_id (template_id)
        ) $charset_collate;";

        // Webhooks table
        $table_name = $wpdb->prefix . 'nowmails_webhooks';
        $sql .= "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            webhook_id varchar(50) NOT NULL,
            url varchar(255) NOT NULL,
            events text NOT NULL,
            secret varchar(255) NOT NULL,
            status varchar(20) NOT NULL DEFAULT 'active',
            created_at datetime NOT NULL,
            updated_at datetime NOT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY webhook_id (webhook_id),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    private static function create_default_options() {
        // API Settings
        add_option('nowmails_api_key', '');
        add_option('nowmails_smtp_host', 'smtp.elasticemail.com');
        add_option('nowmails_smtp_port', '2525');
        add_option('nowmails_smtp_username', '');
        add_option('nowmails_smtp_password', '');

        // Branding Settings
        add_option('nowmails_branding_logo', '');
        add_option('nowmails_branding_company_name', get_bloginfo('name'));
        add_option('nowmails_branding_primary_color', '#0073aa');

        // Webhook Settings
        add_option('nowmails_webhook_secret', wp_generate_password(32, false));

        // Default Subaccount
        add_option('nowmails_default_subaccount_id', '');

        // Email Settings
        add_option('nowmails_default_from_name', get_bloginfo('name'));
        add_option('nowmails_default_from_email', get_bloginfo('admin_email'));
        add_option('nowmails_default_reply_to', get_bloginfo('admin_email'));
    }

    private static function create_capabilities() {
        // Add custom capabilities
        $role = get_role('administrator');
        $capabilities = array(
            'manage_nowmails',
            'manage_nowmails_subaccounts',
            'manage_nowmails_templates',
            'manage_nowmails_webhooks',
            'manage_nowmails_settings',
            'view_nowmails_analytics',
            'manage_nowmails_billing'
        );

        foreach ($capabilities as $cap) {
            $role->add_cap($cap);
        }
    }

    private static function schedule_cron_jobs() {
        // Schedule daily cleanup of old events
        if (!wp_next_scheduled('nowmails_cleanup_events')) {
            wp_schedule_event(time(), 'daily', 'nowmails_cleanup_events');
        }

        // Schedule daily sync of statistics
        if (!wp_next_scheduled('nowmails_sync_statistics')) {
            wp_schedule_event(time(), 'daily', 'nowmails_sync_statistics');
        }

        // Schedule hourly verification of email status
        if (!wp_next_scheduled('nowmails_verify_email_status')) {
            wp_schedule_event(time(), 'hourly', 'nowmails_verify_email_status');
        }
    }
} 