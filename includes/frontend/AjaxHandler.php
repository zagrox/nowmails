<?php
namespace NowMails\Frontend;

use NowMails\Database;
use NowMails\Api\ElasticEmail;

class AjaxHandler {
    private $db;
    private $api;

    public function __construct() {
        $this->db = Database::get_instance();
        $this->api = new ElasticEmail();
        $this->register_actions();
    }

    private function register_actions() {
        // Dashboard actions
        add_action('wp_ajax_nowmails_get_dashboard_stats', array($this, 'get_dashboard_stats'));
        
        // Campaign actions
        add_action('wp_ajax_nowmails_get_campaigns', array($this, 'get_campaigns'));
        add_action('wp_ajax_nowmails_create_campaign', array($this, 'create_campaign'));
        add_action('wp_ajax_nowmails_update_campaign', array($this, 'update_campaign'));
        add_action('wp_ajax_nowmails_delete_campaign', array($this, 'delete_campaign'));
        
        // Template actions
        add_action('wp_ajax_nowmails_get_templates', array($this, 'get_templates'));
        add_action('wp_ajax_nowmails_create_template', array($this, 'create_template'));
        add_action('wp_ajax_nowmails_update_template', array($this, 'update_template'));
        add_action('wp_ajax_nowmails_delete_template', array($this, 'delete_template'));
        
        // Form actions
        add_action('wp_ajax_nowmails_get_forms', array($this, 'get_forms'));
        add_action('wp_ajax_nowmails_create_form', array($this, 'create_form'));
        add_action('wp_ajax_nowmails_update_form', array($this, 'update_form'));
        add_action('wp_ajax_nowmails_delete_form', array($this, 'delete_form'));
        
        // Activity actions
        add_action('wp_ajax_nowmails_get_activity_logs', array($this, 'get_activity_logs'));
        
        // Verification actions
        add_action('wp_ajax_nowmails_verify_email', array($this, 'verify_email'));
        add_action('wp_ajax_nowmails_check_spam_score', array($this, 'check_spam_score'));
        
        // Settings actions
        add_action('wp_ajax_nowmails_update_settings', array($this, 'update_settings'));
        
        // Top-up actions
        add_action('wp_ajax_nowmails_get_balance', array($this, 'get_balance'));
        add_action('wp_ajax_nowmails_process_payment', array($this, 'process_payment'));
    }

    public function get_dashboard_stats() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $user_data = $this->db->get_user_data($user_id);
        if (!$user_data) {
            wp_send_json_error('User data not found');
        }

        // Get statistics from ElasticEmail API
        $stats = $this->api->get_statistics($user_data->elastic_email_id);

        wp_send_json_success(array(
            'emails_sent' => $stats['emails_sent'] ?? 0,
            'open_rate' => $stats['open_rate'] ?? 0,
            'click_rate' => $stats['click_rate'] ?? 0,
            'total_contacts' => $stats['total_contacts'] ?? 0,
            'credits' => $user_data->credits
        ));
    }

    public function get_campaigns() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : null;
        $campaigns = $this->db->get_user_campaigns($user_id, $status);

        wp_send_json_success($campaigns);
    }

    public function create_campaign() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $data = array(
            'user_id' => $user_id,
            'name' => sanitize_text_field($_POST['name']),
            'subject' => sanitize_text_field($_POST['subject']),
            'content' => wp_kses_post($_POST['content']),
            'status' => 'draft'
        );

        if (isset($_POST['schedule'])) {
            $data['schedule'] = sanitize_text_field($_POST['schedule']);
        }

        $result = $this->db->create_campaign($data);

        if ($result) {
            wp_send_json_success('Campaign created successfully');
        } else {
            wp_send_json_error('Failed to create campaign');
        }
    }

    public function update_campaign() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $campaign_id = intval($_POST['id']);
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'subject' => sanitize_text_field($_POST['subject']),
            'content' => wp_kses_post($_POST['content'])
        );

        if (isset($_POST['status'])) {
            $data['status'] = sanitize_text_field($_POST['status']);
        }

        if (isset($_POST['schedule'])) {
            $data['schedule'] = sanitize_text_field($_POST['schedule']);
        }

        $result = $this->db->update_campaign($campaign_id, $data);

        if ($result) {
            wp_send_json_success('Campaign updated successfully');
        } else {
            wp_send_json_error('Failed to update campaign');
        }
    }

    public function delete_campaign() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $campaign_id = intval($_POST['id']);
        $result = $this->db->delete_campaign($campaign_id);

        if ($result) {
            wp_send_json_success('Campaign deleted successfully');
        } else {
            wp_send_json_error('Failed to delete campaign');
        }
    }

    public function get_templates() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $templates = $this->db->get_user_templates($user_id);
        wp_send_json_success($templates);
    }

    public function create_template() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $data = array(
            'user_id' => $user_id,
            'name' => sanitize_text_field($_POST['name']),
            'content' => wp_kses_post($_POST['content']),
            'is_public' => isset($_POST['is_public']) ? 1 : 0
        );

        $result = $this->db->create_template($data);

        if ($result) {
            wp_send_json_success('Template created successfully');
        } else {
            wp_send_json_error('Failed to create template');
        }
    }

    public function update_template() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $template_id = intval($_POST['id']);
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'content' => wp_kses_post($_POST['content']),
            'is_public' => isset($_POST['is_public']) ? 1 : 0
        );

        $result = $this->db->update_template($template_id, $data);

        if ($result) {
            wp_send_json_success('Template updated successfully');
        } else {
            wp_send_json_error('Failed to update template');
        }
    }

    public function delete_template() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $template_id = intval($_POST['id']);
        $result = $this->db->delete_template($template_id);

        if ($result) {
            wp_send_json_success('Template deleted successfully');
        } else {
            wp_send_json_error('Failed to delete template');
        }
    }

    public function get_forms() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $forms = $this->db->get_user_forms($user_id);
        wp_send_json_success($forms);
    }

    public function create_form() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $data = array(
            'user_id' => $user_id,
            'name' => sanitize_text_field($_POST['name']),
            'fields' => wp_json_encode($_POST['fields']),
            'settings' => wp_json_encode($_POST['settings'])
        );

        $result = $this->db->create_form($data);

        if ($result) {
            wp_send_json_success('Form created successfully');
        } else {
            wp_send_json_error('Failed to create form');
        }
    }

    public function update_form() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $form_id = intval($_POST['id']);
        $data = array(
            'name' => sanitize_text_field($_POST['name']),
            'fields' => wp_json_encode($_POST['fields']),
            'settings' => wp_json_encode($_POST['settings'])
        );

        $result = $this->db->update_form($form_id, $data);

        if ($result) {
            wp_send_json_success('Form updated successfully');
        } else {
            wp_send_json_error('Failed to update form');
        }
    }

    public function delete_form() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $form_id = intval($_POST['id']);
        $result = $this->db->delete_form($form_id);

        if ($result) {
            wp_send_json_success('Form deleted successfully');
        } else {
            wp_send_json_error('Failed to delete form');
        }
    }

    public function get_activity_logs() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 10;
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;

        $events = $this->db->get_user_events($user_id, $limit, $offset);
        wp_send_json_success($events);
    }

    public function verify_email() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $email = sanitize_email($_POST['email']);
        if (!is_email($email)) {
            wp_send_json_error('Invalid email address');
        }

        $result = $this->api->verify_email($email);
        wp_send_json_success($result);
    }

    public function check_spam_score() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $content = wp_kses_post($_POST['content']);
        if (empty($content)) {
            wp_send_json_error('Content is required');
        }

        $result = $this->api->check_spam_score($content);
        wp_send_json_success($result);
    }

    public function update_settings() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $settings = array(
            'notifications' => isset($_POST['notifications']) ? $_POST['notifications'] : array(),
            'security' => isset($_POST['security']) ? $_POST['security'] : array()
        );

        $result = $this->db->update_user_data($user_id, array(
            'settings' => wp_json_encode($settings)
        ));

        if ($result) {
            wp_send_json_success('Settings updated successfully');
        } else {
            wp_send_json_error('Failed to update settings');
        }
    }

    public function get_balance() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        $user_data = $this->db->get_user_data($user_id);
        if (!$user_data) {
            wp_send_json_error('User data not found');
        }

        wp_send_json_success(array(
            'credits' => $user_data->credits
        ));
    }

    public function process_payment() {
        check_ajax_referer('nowmails_frontend_nonce', 'nonce');
        
        $user_id = get_current_user_id();
        if (!$user_id) {
            wp_send_json_error('User not logged in');
        }

        // Implement payment processing logic here
        // This is a placeholder for the actual payment integration
        $amount = floatval($_POST['amount']);
        $payment_method = sanitize_text_field($_POST['payment_method']);

        // Process payment and update credits
        $result = $this->db->update_user_data($user_id, array(
            'credits' => $amount
        ));

        if ($result) {
            wp_send_json_success('Payment processed successfully');
        } else {
            wp_send_json_error('Failed to process payment');
        }
    }
} 