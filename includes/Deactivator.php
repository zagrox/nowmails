<?php
namespace NowMails;

class Deactivator {
    public static function deactivate() {
        self::remove_capabilities();
        self::clear_scheduled_hooks();
        self::cleanup_options();
    }

    private static function remove_capabilities() {
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
            $role->remove_cap($cap);
        }
    }

    private static function clear_scheduled_hooks() {
        wp_clear_scheduled_hook('nowmails_cleanup_events');
        wp_clear_scheduled_hook('nowmails_sync_statistics');
        wp_clear_scheduled_hook('nowmails_verify_email_status');
    }

    private static function cleanup_options() {
        // Remove all plugin options
        $options = array(
            'nowmails_api_key',
            'nowmails_smtp_host',
            'nowmails_smtp_port',
            'nowmails_smtp_username',
            'nowmails_smtp_password',
            'nowmails_branding_logo',
            'nowmails_branding_company_name',
            'nowmails_branding_primary_color',
            'nowmails_webhook_secret',
            'nowmails_default_subaccount_id',
            'nowmails_default_from_name',
            'nowmails_default_from_email',
            'nowmails_default_reply_to'
        );

        foreach ($options as $option) {
            delete_option($option);
        }
    }
} 