<?php
namespace NowMails;

class Database {
    private static $instance = null;
    private $wpdb;

    private function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->create_tables();
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function create_tables() {
        $charset_collate = $this->wpdb->get_charset_collate();

        // Table for storing user-specific settings and data
        $sql = "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}nowmails_users (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            elastic_email_id varchar(255) NOT NULL,
            credits int(11) DEFAULT 0,
            settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";

        // Table for storing email events
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}nowmails_events (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            event_type varchar(50) NOT NULL,
            event_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY event_type (event_type)
        ) $charset_collate;";

        // Table for storing email campaigns
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}nowmails_campaigns (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            subject varchar(255) NOT NULL,
            content longtext NOT NULL,
            status varchar(50) DEFAULT 'draft',
            schedule datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id),
            KEY status (status)
        ) $charset_collate;";

        // Table for storing email templates
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}nowmails_templates (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            content longtext NOT NULL,
            is_public tinyint(1) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        // Table for storing email forms
        $sql .= "CREATE TABLE IF NOT EXISTS {$this->wpdb->prefix}nowmails_forms (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            name varchar(255) NOT NULL,
            fields longtext NOT NULL,
            settings longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function get_user_data($user_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}nowmails_users WHERE user_id = %d",
                $user_id
            )
        );
    }

    public function update_user_data($user_id, $data) {
        $existing = $this->get_user_data($user_id);
        
        if ($existing) {
            return $this->wpdb->update(
                $this->wpdb->prefix . 'nowmails_users',
                $data,
                array('user_id' => $user_id)
            );
        } else {
            $data['user_id'] = $user_id;
            return $this->wpdb->insert(
                $this->wpdb->prefix . 'nowmails_users',
                $data
            );
        }
    }

    public function get_user_events($user_id, $limit = 10, $offset = 0) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}nowmails_events 
                WHERE user_id = %d 
                ORDER BY created_at DESC 
                LIMIT %d OFFSET %d",
                $user_id,
                $limit,
                $offset
            )
        );
    }

    public function get_user_campaigns($user_id, $status = null) {
        $query = "SELECT * FROM {$this->wpdb->prefix}nowmails_campaigns WHERE user_id = %d";
        $params = array($user_id);

        if ($status) {
            $query .= " AND status = %s";
            $params[] = $status;
        }

        $query .= " ORDER BY created_at DESC";

        return $this->wpdb->get_results(
            $this->wpdb->prepare($query, $params)
        );
    }

    public function get_user_templates($user_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}nowmails_templates 
                WHERE user_id = %d OR is_public = 1 
                ORDER BY created_at DESC",
                $user_id
            )
        );
    }

    public function get_user_forms($user_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->wpdb->prefix}nowmails_forms 
                WHERE user_id = %d 
                ORDER BY created_at DESC",
                $user_id
            )
        );
    }

    public function create_campaign($data) {
        return $this->wpdb->insert(
            $this->wpdb->prefix . 'nowmails_campaigns',
            $data
        );
    }

    public function update_campaign($id, $data) {
        return $this->wpdb->update(
            $this->wpdb->prefix . 'nowmails_campaigns',
            $data,
            array('id' => $id)
        );
    }

    public function delete_campaign($id) {
        return $this->wpdb->delete(
            $this->wpdb->prefix . 'nowmails_campaigns',
            array('id' => $id)
        );
    }

    public function create_template($data) {
        return $this->wpdb->insert(
            $this->wpdb->prefix . 'nowmails_templates',
            $data
        );
    }

    public function update_template($id, $data) {
        return $this->wpdb->update(
            $this->wpdb->prefix . 'nowmails_templates',
            $data,
            array('id' => $id)
        );
    }

    public function delete_template($id) {
        return $this->wpdb->delete(
            $this->wpdb->prefix . 'nowmails_templates',
            array('id' => $id)
        );
    }

    public function create_form($data) {
        return $this->wpdb->insert(
            $this->wpdb->prefix . 'nowmails_forms',
            $data
        );
    }

    public function update_form($id, $data) {
        return $this->wpdb->update(
            $this->wpdb->prefix . 'nowmails_forms',
            $data,
            array('id' => $id)
        );
    }

    public function delete_form($id) {
        return $this->wpdb->delete(
            $this->wpdb->prefix . 'nowmails_forms',
            array('id' => $id)
        );
    }
} 