<?php
namespace NowMails\Api;

class ElasticEmail {
    private $api_key;
    private $api_url = 'https://api.elasticemail.com/v2/';

    public function __construct($api_key = null) {
        $this->api_key = $api_key ?: get_option('nowmails_api_key');
    }

    public function set_api_key($api_key) {
        $this->api_key = $api_key;
    }

    private function make_request($endpoint, $method = 'GET', $data = null) {
        $url = $this->api_url . $endpoint;
        
        $args = array(
            'method' => $method,
            'headers' => array(
                'X-ElasticEmail-ApiKey' => $this->api_key,
                'Content-Type' => 'application/json'
            ),
            'timeout' => 30
        );

        if ($data) {
            $args['body'] = json_encode($data);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        return array(
            'success' => true,
            'data' => $data
        );
    }

    // Subaccount Management
    public function create_subaccount($data) {
        return $this->make_request('subaccount/add', 'POST', $data);
    }

    public function get_subaccounts() {
        return $this->make_request('subaccount/list');
    }

    public function update_subaccount($data) {
        return $this->make_request('subaccount/update', 'POST', $data);
    }

    public function delete_subaccount($publicAccountID) {
        return $this->make_request('subaccount/delete', 'POST', array('publicAccountID' => $publicAccountID));
    }

    // Email Sending
    public function send_email($data) {
        return $this->make_request('email/send', 'POST', $data);
    }

    public function get_email_status($messageID) {
        return $this->make_request('email/status', 'GET', array('messageID' => $messageID));
    }

    // Statistics
    public function get_statistics($from = null, $to = null) {
        $params = array();
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('statistics/load', 'GET', $params);
    }

    public function get_subaccount_statistics($publicAccountID, $from = null, $to = null) {
        $params = array('publicAccountID' => $publicAccountID);
        if ($from) $params['from'] = $from;
        if ($to) $params['to'] = $to;
        
        return $this->make_request('statistics/load', 'GET', $params);
    }

    // Email Verification
    public function verify_email($email) {
        return $this->make_request('email/verify', 'POST', array('email' => $email));
    }

    public function bulk_verify_emails($emails) {
        return $this->make_request('email/verify', 'POST', array('emails' => $emails));
    }

    // Template Management
    public function create_template($data) {
        return $this->make_request('template/add', 'POST', $data);
    }

    public function get_templates() {
        return $this->make_request('template/list');
    }

    public function update_template($data) {
        return $this->make_request('template/update', 'POST', $data);
    }

    public function delete_template($templateID) {
        return $this->make_request('template/delete', 'POST', array('templateID' => $templateID));
    }

    // Webhook Management
    public function create_webhook($data) {
        return $this->make_request('webhook/add', 'POST', $data);
    }

    public function get_webhooks() {
        return $this->make_request('webhook/list');
    }

    public function update_webhook($data) {
        return $this->make_request('webhook/update', 'POST', $data);
    }

    public function delete_webhook($webhookID) {
        return $this->make_request('webhook/delete', 'POST', array('webhookID' => $webhookID));
    }

    // Account Management
    public function get_account_info() {
        return $this->make_request('account/load');
    }

    public function update_account_info($data) {
        return $this->make_request('account/update', 'POST', $data);
    }

    public function get_api_keys() {
        return $this->make_request('account/api-keys');
    }

    public function create_api_key($data) {
        return $this->make_request('account/api-keys/add', 'POST', $data);
    }

    public function delete_api_key($apiKey) {
        return $this->make_request('account/api-keys/delete', 'POST', array('apiKey' => $apiKey));
    }
} 