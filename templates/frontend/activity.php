<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nowmails-activity">
    <div class="activity-header">
        <h2><?php _e('Activity Logs', 'nowmails'); ?></h2>
        <div class="activity-filters">
            <select id="event-type-filter">
                <option value=""><?php _e('All Events', 'nowmails'); ?></option>
                <option value="email_sent"><?php _e('Sent', 'nowmails'); ?></option>
                <option value="email_opened"><?php _e('Opened', 'nowmails'); ?></option>
                <option value="email_clicked"><?php _e('Clicked', 'nowmails'); ?></option>
                <option value="email_bounced"><?php _e('Bounced', 'nowmails'); ?></option>
                <option value="email_complained"><?php _e('Complaints', 'nowmails'); ?></option>
                <option value="email_unsubscribed"><?php _e('Unsubscribed', 'nowmails'); ?></option>
            </select>
            <input type="date" id="date-filter" class="datepicker">
            <button class="button button-primary" id="apply-filters">
                <span class="dashicons dashicons-filter"></span>
                <?php _e('Apply Filters', 'nowmails'); ?>
            </button>
            <button class="button" id="export-logs">
                <span class="dashicons dashicons-download"></span>
                <?php _e('Export Logs', 'nowmails'); ?>
            </button>
        </div>
    </div>

    <div class="activity-stats">
        <div class="stat-card">
            <h3><?php _e('Total Events', 'nowmails'); ?></h3>
            <p class="stat-value" id="total-events">0</p>
        </div>
        <div class="stat-card">
            <h3><?php _e('Sent Today', 'nowmails'); ?></h3>
            <p class="stat-value" id="sent-today">0</p>
        </div>
        <div class="stat-card">
            <h3><?php _e('Opened Today', 'nowmails'); ?></h3>
            <p class="stat-value" id="opened-today">0</p>
        </div>
        <div class="stat-card">
            <h3><?php _e('Bounced Today', 'nowmails'); ?></h3>
            <p class="stat-value" id="bounced-today">0</p>
        </div>
    </div>

    <div class="activity-chart">
        <canvas id="activity-trend-chart"></canvas>
    </div>

    <div class="activity-list-container">
        <div class="table-responsive">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Date', 'nowmails'); ?></th>
                        <th><?php _e('Event Type', 'nowmails'); ?></th>
                        <th><?php _e('Recipient', 'nowmails'); ?></th>
                        <th><?php _e('Details', 'nowmails'); ?></th>
                        <th><?php _e('Campaign', 'nowmails'); ?></th>
                    </tr>
                </thead>
                <tbody id="activity-list">
                    <tr>
                        <td colspan="5" class="loading"><?php _e('Loading...', 'nowmails'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <button class="button" id="prev-page" disabled>
                <span class="dashicons dashicons-arrow-left-alt2"></span>
                <?php _e('Previous', 'nowmails'); ?>
            </button>
            <span class="page-info">
                <?php _e('Page', 'nowmails'); ?> <span id="current-page">1</span> 
                <?php _e('of', 'nowmails'); ?> <span id="total-pages">1</span>
            </span>
            <button class="button" id="next-page" disabled>
                <?php _e('Next', 'nowmails'); ?>
                <span class="dashicons dashicons-arrow-right-alt2"></span>
            </button>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var currentPage = 1;
    var totalPages = 1;
    var itemsPerPage = 20;
    var filters = {
        event_type: '',
        date: ''
    };

    // Initialize chart
    var activityChartCtx = document.getElementById('activity-trend-chart').getContext('2d');
    var activityChart = new Chart(activityChartCtx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: '<?php _e('Events', 'nowmails'); ?>',
                data: [],
                borderColor: '#2271b1',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Load activity data
    function loadActivityData(page = 1) {
        $.ajax({
            url: nowmailsFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'nowmails_get_activity_logs',
                nonce: nowmailsFrontend.nonce,
                page: page,
                limit: itemsPerPage,
                filters: filters
            },
            success: function(response) {
                if (response.success) {
                    updateActivityList(response.data);
                    updatePagination(response.total_pages);
                }
            }
        });
    }

    // Update activity list
    function updateActivityList(events) {
        var html = '';
        if (events.length > 0) {
            events.forEach(function(event) {
                var eventData = JSON.parse(event.event_data);
                html += '<tr>';
                html += '<td>' + event.created_at + '</td>';
                html += '<td><span class="event-type ' + event.event_type + '">' + getEventTypeLabel(event.event_type) + '</span></td>';
                html += '<td>' + eventData.recipient + '</td>';
                html += '<td>' + getEventDetails(event) + '</td>';
                html += '<td>' + (eventData.campaign_name || '-') + '</td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="5"><?php _e('No events found', 'nowmails'); ?></td></tr>';
        }
        $('#activity-list').html(html);
    }

    // Update pagination
    function updatePagination(total) {
        totalPages = Math.ceil(total / itemsPerPage);
        $('#current-page').text(currentPage);
        $('#total-pages').text(totalPages);
        $('#prev-page').prop('disabled', currentPage === 1);
        $('#next-page').prop('disabled', currentPage === totalPages);
    }

    // Get event type label
    function getEventTypeLabel(eventType) {
        var labels = {
            'email_sent': '<?php _e('Sent', 'nowmails'); ?>',
            'email_opened': '<?php _e('Opened', 'nowmails'); ?>',
            'email_clicked': '<?php _e('Clicked', 'nowmails'); ?>',
            'email_bounced': '<?php _e('Bounced', 'nowmails'); ?>',
            'email_complained': '<?php _e('Complaint', 'nowmails'); ?>',
            'email_unsubscribed': '<?php _e('Unsubscribed', 'nowmails'); ?>'
        };
        return labels[eventType] || eventType;
    }

    // Get event details
    function getEventDetails(event) {
        var eventData = JSON.parse(event.event_data);
        var details = {
            'email_sent': '<?php _e('Sent to', 'nowmails'); ?> ' + eventData.recipient,
            'email_opened': '<?php _e('Opened by', 'nowmails'); ?> ' + eventData.recipient,
            'email_clicked': '<?php _e('Clicked link:', 'nowmails'); ?> ' + eventData.link,
            'email_bounced': '<?php _e('Bounced for', 'nowmails'); ?> ' + eventData.recipient + ' (' + eventData.reason + ')',
            'email_complained': '<?php _e('Complaint from', 'nowmails'); ?> ' + eventData.recipient,
            'email_unsubscribed': '<?php _e('Unsubscribed by', 'nowmails'); ?> ' + eventData.recipient
        };
        return details[event.event_type] || '';
    }

    // Event handlers
    $('#apply-filters').on('click', function() {
        filters.event_type = $('#event-type-filter').val();
        filters.date = $('#date-filter').val();
        currentPage = 1;
        loadActivityData(currentPage);
    });

    $('#prev-page').on('click', function() {
        if (currentPage > 1) {
            currentPage--;
            loadActivityData(currentPage);
        }
    });

    $('#next-page').on('click', function() {
        if (currentPage < totalPages) {
            currentPage++;
            loadActivityData(currentPage);
        }
    });

    $('#export-logs').on('click', function() {
        window.location.href = '<?php echo admin_url('admin-ajax.php'); ?>?action=nowmails_export_logs&nonce=' + nowmailsFrontend.nonce;
    });

    // Initialize datepicker
    $('#date-filter').datepicker({
        dateFormat: 'yy-mm-dd',
        changeMonth: true,
        changeYear: true
    });

    // Initial load
    loadActivityData();
});
</script> 