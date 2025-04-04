<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get API instance
$api = new \NowMails\Api\ElasticEmail();

// Get statistics
$stats = $api->get_statistics();
$subaccounts = $api->get_subaccounts();

// Get recent events
global $wpdb;
$table_name = $wpdb->prefix . 'nowmails_events';
$recent_events = $wpdb->get_results(
    "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 10"
);
?>

<div class="wrap nowmails-dashboard">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Overview Cards -->
    <div class="nowmails-overview-cards">
        <div class="card">
            <h3><?php _e('Total Subaccounts', 'nowmails'); ?></h3>
            <p class="number"><?php echo count($subaccounts['data'] ?? array()); ?></p>
        </div>
        <div class="card">
            <h3><?php _e('Emails Sent Today', 'nowmails'); ?></h3>
            <p class="number"><?php echo number_format($stats['data']['sent'] ?? 0); ?></p>
        </div>
        <div class="card">
            <h3><?php _e('Open Rate', 'nowmails'); ?></h3>
            <p class="number"><?php echo number_format(($stats['data']['opens'] / $stats['data']['sent']) * 100, 2); ?>%</p>
        </div>
        <div class="card">
            <h3><?php _e('Click Rate', 'nowmails'); ?></h3>
            <p class="number"><?php echo number_format(($stats['data']['clicks'] / $stats['data']['sent']) * 100, 2); ?>%</p>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="nowmails-recent-activity">
        <h2><?php _e('Recent Activity', 'nowmails'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Event', 'nowmails'); ?></th>
                    <th><?php _e('Details', 'nowmails'); ?></th>
                    <th><?php _e('Time', 'nowmails'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_events as $event): ?>
                    <tr>
                        <td><?php echo esc_html($event->event_type); ?></td>
                        <td><?php echo esc_html(json_encode($event->event_data)); ?></td>
                        <td><?php echo esc_html($event->created_at); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Subaccount Performance -->
    <div class="nowmails-subaccount-performance">
        <h2><?php _e('Subaccount Performance', 'nowmails'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Subaccount', 'nowmails'); ?></th>
                    <th><?php _e('Emails Sent', 'nowmails'); ?></th>
                    <th><?php _e('Open Rate', 'nowmails'); ?></th>
                    <th><?php _e('Click Rate', 'nowmails'); ?></th>
                    <th><?php _e('Status', 'nowmails'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subaccounts['data'] as $subaccount): ?>
                    <?php
                    $subaccount_stats = $api->get_subaccount_statistics($subaccount['publicAccountID']);
                    ?>
                    <tr>
                        <td><?php echo esc_html($subaccount['name']); ?></td>
                        <td><?php echo number_format($subaccount_stats['data']['sent'] ?? 0); ?></td>
                        <td><?php echo number_format(($subaccount_stats['data']['opens'] / $subaccount_stats['data']['sent']) * 100, 2); ?>%</td>
                        <td><?php echo number_format(($subaccount_stats['data']['clicks'] / $subaccount_stats['data']['sent']) * 100, 2); ?>%</td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($subaccount['status']); ?>">
                                <?php echo esc_html($subaccount['status']); ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Quick Actions -->
    <div class="nowmails-quick-actions">
        <h2><?php _e('Quick Actions', 'nowmails'); ?></h2>
        <div class="action-buttons">
            <a href="<?php echo admin_url('admin.php?page=nowmails-subaccounts&action=new'); ?>" class="button button-primary">
                <?php _e('Add New Subaccount', 'nowmails'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=nowmails-templates&action=new'); ?>" class="button button-primary">
                <?php _e('Create New Template', 'nowmails'); ?>
            </a>
            <a href="<?php echo admin_url('admin.php?page=nowmails-analytics'); ?>" class="button button-secondary">
                <?php _e('View Detailed Analytics', 'nowmails'); ?>
            </a>
        </div>
    </div>
</div>

<style>
.nowmails-dashboard {
    margin: 20px;
}

.nowmails-overview-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.card {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card h3 {
    margin: 0 0 10px 0;
    color: #23282d;
}

.card .number {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
    color: #0073aa;
}

.nowmails-recent-activity,
.nowmails-subaccount-performance {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.nowmails-quick-actions {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.action-buttons {
    display: flex;
    gap: 10px;
    margin-top: 15px;
}

.status-badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 500;
}

.status-active {
    background: #e8f5e9;
    color: #2e7d32;
}

.status-inactive {
    background: #ffebee;
    color: #c62828;
}

.status-pending {
    background: #fff3e0;
    color: #ef6c00;
}
</style> 