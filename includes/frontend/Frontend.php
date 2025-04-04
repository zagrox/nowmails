<?php
namespace NowMails\Frontend;

class Frontend {
    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            NOWMAILS_PLUGIN_URL . 'assets/css/public.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            NOWMAILS_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            $this->version,
            false
        );

        wp_localize_script($this->plugin_name, 'nowmailsPublic', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('nowmails_public_nonce')
        ));
    }

    // Shortcode for email subscription form
    public function register_shortcodes() {
        add_shortcode('nowmails_subscribe', array($this, 'render_subscription_form'));
    }

    public function render_subscription_form($atts) {
        $atts = shortcode_atts(array(
            'title' => __('Subscribe to our newsletter', 'nowmails'),
            'description' => __('Stay updated with our latest news and updates.', 'nowmails'),
            'button_text' => __('Subscribe', 'nowmails'),
            'success_message' => __('Thank you for subscribing!', 'nowmails'),
            'error_message' => __('Something went wrong. Please try again.', 'nowmails')
        ), $atts);

        ob_start();
        ?>
        <div class="nowmails-subscription-form">
            <h3><?php echo esc_html($atts['title']); ?></h3>
            <p><?php echo esc_html($atts['description']); ?></p>
            <form id="nowmails-subscribe-form" class="nowmails-form">
                <div class="form-group">
                    <input type="email" name="email" required placeholder="<?php esc_attr_e('Enter your email', 'nowmails'); ?>">
                </div>
                <div class="form-group">
                    <button type="submit"><?php echo esc_html($atts['button_text']); ?></button>
                </div>
                <div class="form-message success" style="display: none;"><?php echo esc_html($atts['success_message']); ?></div>
                <div class="form-message error" style="display: none;"><?php echo esc_html($atts['error_message']); ?></div>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    // AJAX handlers for subscription form
    public function handle_subscription() {
        check_ajax_referer('nowmails_public_nonce', 'nonce');

        $email = sanitize_email($_POST['email']);
        
        if (!is_email($email)) {
            wp_send_json_error(__('Invalid email address', 'nowmails'));
        }

        // Get ElasticEmail API instance
        $api = new \NowMails\Api\ElasticEmail();

        // Add subscriber to ElasticEmail
        $result = $api->add_subscriber(array(
            'email' => $email,
            'publicAccountID' => get_option('nowmails_default_subaccount_id')
        ));

        if ($result['success']) {
            wp_send_json_success(__('Successfully subscribed!', 'nowmails'));
        } else {
            wp_send_json_error($result['message']);
        }
    }

    // Webhook handler for email events
    public function handle_webhook() {
        $payload = file_get_contents('php://input');
        $data = json_decode($payload, true);

        if (!$data) {
            wp_send_json_error('Invalid payload');
        }

        // Verify webhook signature
        $signature = $_SERVER['HTTP_X_ELASTICEMAIL_SIGNATURE'] ?? '';
        if (!$this->verify_webhook_signature($signature, $payload)) {
            wp_send_json_error('Invalid signature');
        }

        // Process webhook event
        $this->process_webhook_event($data);

        wp_send_json_success();
    }

    private function verify_webhook_signature($signature, $payload) {
        $webhook_secret = get_option('nowmails_webhook_secret');
        $expected_signature = hash_hmac('sha256', $payload, $webhook_secret);
        return hash_equals($expected_signature, $signature);
    }

    private function process_webhook_event($data) {
        $event_type = $data['eventType'] ?? '';
        $event_data = $data['eventData'] ?? array();

        switch ($event_type) {
            case 'email_sent':
                $this->handle_email_sent($event_data);
                break;
            case 'email_opened':
                $this->handle_email_opened($event_data);
                break;
            case 'email_clicked':
                $this->handle_email_clicked($event_data);
                break;
            case 'email_bounced':
                $this->handle_email_bounced($event_data);
                break;
            case 'email_complained':
                $this->handle_email_complained($event_data);
                break;
            case 'email_unsubscribed':
                $this->handle_email_unsubscribed($event_data);
                break;
        }
    }

    private function handle_email_sent($data) {
        // Log email sent event
        $this->log_event('email_sent', $data);
    }

    private function handle_email_opened($data) {
        // Log email opened event
        $this->log_event('email_opened', $data);
    }

    private function handle_email_clicked($data) {
        // Log email clicked event
        $this->log_event('email_clicked', $data);
    }

    private function handle_email_bounced($data) {
        // Log email bounced event
        $this->log_event('email_bounced', $data);
    }

    private function handle_email_complained($data) {
        // Log email complained event
        $this->log_event('email_complained', $data);
    }

    private function handle_email_unsubscribed($data) {
        // Log email unsubscribed event
        $this->log_event('email_unsubscribed', $data);
    }

    private function log_event($event_type, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'nowmails_events';
        
        $wpdb->insert(
            $table_name,
            array(
                'event_type' => $event_type,
                'event_data' => json_encode($data),
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s')
        );
    }
} 