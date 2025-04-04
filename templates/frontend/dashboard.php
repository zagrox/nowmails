<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nowmails-dashboard">
    <div class="dashboard-header">
        <h2><?php _e('Dashboard Overview', 'nowmails'); ?></h2>
        <div class="dashboard-actions">
            <button class="button button-primary" id="refresh-stats">
                <span class="dashicons dashicons-update"></span>
                <?php _e('Refresh Stats', 'nowmails'); ?>
            </button>
            <button class="button" id="create-campaign">
                <span class="dashicons dashicons-plus"></span>
                <?php _e('New Campaign', 'nowmails'); ?>
            </button>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card card-emails">
            <div class="card-header">
                <h3 class="card-title"><?php _e('Emails Sent', 'nowmails'); ?></h3>
                <span class="card-icon dashicons dashicons-email"></span>
            </div>
            <div class="card-content">
                <p class="card-value" id="emails-sent">0</p>
                <p class="card-trend positive">
                    <span class="dashicons dashicons-arrow-up"></span>
                    <span class="trend-value">0%</span>
                    <?php _e('vs last month', 'nowmails'); ?>
                </p>
            </div>
        </div>

        <div class="dashboard-card card-opens">
            <div class="card-header">
                <h3 class="card-title"><?php _e('Open Rate', 'nowmails'); ?></h3>
                <span class="card-icon dashicons dashicons-visibility"></span>
            </div>
            <div class="card-content">
                <p class="card-value" id="open-rate">0%</p>
                <p class="card-trend positive">
                    <span class="dashicons dashicons-arrow-up"></span>
                    <span class="trend-value">0%</span>
                    <?php _e('vs last month', 'nowmails'); ?>
                </p>
            </div>
        </div>

        <div class="dashboard-card card-clicks">
            <div class="card-header">
                <h3 class="card-title"><?php _e('Click Rate', 'nowmails'); ?></h3>
                <span class="card-icon dashicons dashicons-admin-links"></span>
            </div>
            <div class="card-content">
                <p class="card-value" id="click-rate">0%</p>
                <p class="card-trend positive">
                    <span class="dashicons dashicons-arrow-up"></span>
                    <span class="trend-value">0%</span>
                    <?php _e('vs last month', 'nowmails'); ?>
                </p>
            </div>
        </div>

        <div class="dashboard-card card-contacts">
            <div class="card-header">
                <h3 class="card-title"><?php _e('Total Contacts', 'nowmails'); ?></h3>
                <span class="card-icon dashicons dashicons-groups"></span>
            </div>
            <div class="card-content">
                <p class="card-value" id="total-contacts">0</p>
                <p class="card-trend positive">
                    <span class="dashicons dashicons-arrow-up"></span>
                    <span class="trend-value">0</span>
                    <?php _e('new this month', 'nowmails'); ?>
                </p>
            </div>
        </div>
    </div>

    <div class="dashboard-charts">
        <div class="chart-container">
            <h3><?php _e('Email Activity', 'nowmails'); ?></h3>
            <canvas id="email-activity-chart"></canvas>
        </div>
        <div class="chart-container">
            <h3><?php _e('Campaign Performance', 'nowmails'); ?></h3>
            <canvas id="campaign-performance-chart"></canvas>
        </div>
    </div>

    <div class="dashboard-recent">
        <div class="recent-campaigns">
            <h3><?php _e('Recent Campaigns', 'nowmails'); ?></h3>
            <div class="table-responsive">
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'nowmails'); ?></th>
                            <th><?php _e('Status', 'nowmails'); ?></th>
                            <th><?php _e('Sent', 'nowmails'); ?></th>
                            <th><?php _e('Opens', 'nowmails'); ?></th>
                            <th><?php _e('Clicks', 'nowmails'); ?></th>
                            <th><?php _e('Date', 'nowmails'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="recent-campaigns-list">
                        <tr>
                            <td colspan="6" class="loading"><?php _e('Loading...', 'nowmails'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="recent-activity">
            <h3><?php _e('Recent Activity', 'nowmails'); ?></h3>
            <div class="activity-list" id="recent-activity-list">
                <div class="loading"><?php _e('Loading...', 'nowmails'); ?></div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Initialize charts
    var emailActivityCtx = document.getElementById('email-activity-chart').getContext('2d');
    var campaignPerformanceCtx = document.getElementById('campaign-performance-chart').getContext('2d');

    var emailActivityChart = new Chart(emailActivityCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: '<?php _e('Sent', 'nowmails'); ?>',
                data: [],
                borderColor: '#2271b1',
                tension: 0.1
            }, {
                label: '<?php _e('Opens', 'nowmails'); ?>',
                data: [],
                borderColor: '#00a32a',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });

    var campaignPerformanceChart = new Chart(campaignPerformanceCtx, {
        type: 'bar',
        data: {
            labels: [],
            datasets: [{
                label: '<?php _e('Open Rate', 'nowmails'); ?>',
                data: [],
                backgroundColor: '#2271b1'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });

    // Load dashboard data
    function loadDashboardData() {
        $.ajax({
            url: nowmailsFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'nowmails_get_dashboard_stats',
                nonce: nowmailsFrontend.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateDashboardStats(response.data);
                }
            }
        });
    }

    // Update dashboard statistics
    function updateDashboardStats(data) {
        $('#emails-sent').text(data.emails_sent);
        $('#open-rate').text(data.open_rate + '%');
        $('#click-rate').text(data.click_rate + '%');
        $('#total-contacts').text(data.total_contacts);
    }

    // Load recent campaigns
    function loadRecentCampaigns() {
        $.ajax({
            url: nowmailsFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'nowmails_get_campaigns',
                nonce: nowmailsFrontend.nonce,
                limit: 5
            },
            success: function(response) {
                if (response.success) {
                    updateRecentCampaigns(response.data);
                }
            }
        });
    }

    // Update recent campaigns list
    function updateRecentCampaigns(campaigns) {
        var html = '';
        if (campaigns.length > 0) {
            campaigns.forEach(function(campaign) {
                html += '<tr>';
                html += '<td>' + campaign.name + '</td>';
                html += '<td><span class="status-' + campaign.status + '">' + campaign.status + '</span></td>';
                html += '<td>' + campaign.sent + '</td>';
                html += '<td>' + campaign.opens + '</td>';
                html += '<td>' + campaign.clicks + '</td>';
                html += '<td>' + campaign.created_at + '</td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="6"><?php _e('No campaigns found', 'nowmails'); ?></td></tr>';
        }
        $('#recent-campaigns-list').html(html);
    }

    // Load recent activity
    function loadRecentActivity() {
        $.ajax({
            url: nowmailsFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'nowmails_get_activity_logs',
                nonce: nowmailsFrontend.nonce,
                limit: 5
            },
            success: function(response) {
                if (response.success) {
                    updateRecentActivity(response.data);
                }
            }
        });
    }

    // Update recent activity list
    function updateRecentActivity(events) {
        var html = '';
        if (events.length > 0) {
            events.forEach(function(event) {
                html += '<div class="activity-item">';
                html += '<span class="activity-icon dashicons ' + getEventIcon(event.event_type) + '"></span>';
                html += '<div class="activity-content">';
                html += '<p class="activity-text">' + getEventText(event) + '</p>';
                html += '<p class="activity-time">' + event.created_at + '</p>';
                html += '</div>';
                html += '</div>';
            });
        } else {
            html = '<div class="no-activity"><?php _e('No recent activity', 'nowmails'); ?></div>';
        }
        $('#recent-activity-list').html(html);
    }

    // Get event icon based on event type
    function getEventIcon(eventType) {
        var icons = {
            'email_sent': 'dashicons-email',
            'email_opened': 'dashicons-visibility',
            'email_clicked': 'dashicons-admin-links',
            'email_bounced': 'dashicons-warning',
            'email_complained': 'dashicons-flag',
            'email_unsubscribed': 'dashicons-no'
        };
        return icons[eventType] || 'dashicons-info';
    }

    // Get event text based on event data
    function getEventText(event) {
        var eventData = JSON.parse(event.event_data);
        var texts = {
            'email_sent': '<?php _e('Email sent to', 'nowmails'); ?> ' + eventData.recipient,
            'email_opened': '<?php _e('Email opened by', 'nowmails'); ?> ' + eventData.recipient,
            'email_clicked': '<?php _e('Link clicked by', 'nowmails'); ?> ' + eventData.recipient,
            'email_bounced': '<?php _e('Email bounced for', 'nowmails'); ?> ' + eventData.recipient,
            'email_complained': '<?php _e('Complaint received from', 'nowmails'); ?> ' + eventData.recipient,
            'email_unsubscribed': '<?php _e('Unsubscribe from', 'nowmails'); ?> ' + eventData.recipient
        };
        return texts[event.event_type] || '<?php _e('Unknown event', 'nowmails'); ?>';
    }

    // Event handlers
    $('#refresh-stats').on('click', function() {
        loadDashboardData();
        loadRecentCampaigns();
        loadRecentActivity();
    });

    $('#create-campaign').on('click', function() {
        window.location.href = '<?php echo admin_url('admin.php?page=nowmails-campaigns&action=new'); ?>';
    });

    // Initial load
    loadDashboardData();
    loadRecentCampaigns();
    loadRecentActivity();
});
</script> 