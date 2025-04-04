<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="nowmails-admin-container">
        <div class="nowmails-admin-card">
            <h2><?php _e('API Settings', 'nowmails'); ?></h2>
            <form method="post" action="options.php">
                <?php
                settings_fields('nowmails_options');
                do_settings_sections('nowmails_options');
                ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="nowmails_api_key"><?php _e('API Key', 'nowmails'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="nowmails_api_key" name="nowmails_api_key" 
                                value="<?php echo esc_attr(get_option('nowmails_api_key')); ?>" 
                                class="regular-text">
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="nowmails_webhook_secret"><?php _e('Webhook Secret', 'nowmails'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="nowmails_webhook_secret" name="nowmails_webhook_secret" 
                                value="<?php echo esc_attr(get_option('nowmails_webhook_secret')); ?>" 
                                class="regular-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>

        <div class="nowmails-admin-card">
            <h2><?php _e('Subscription Form Shortcode', 'nowmails'); ?></h2>
            <p><?php _e('Use this shortcode to display the subscription form on any page or post:', 'nowmails'); ?></p>
            <code>[nowmails_subscribe]</code>
            <button class="button button-secondary" onclick="copyShortcode()"><?php _e('Copy Shortcode', 'nowmails'); ?></button>
        </div>

        <div class="nowmails-admin-card">
            <h2><?php _e('Statistics', 'nowmails'); ?></h2>
            <div class="nowmails-stats-grid">
                <?php
                $api = new \NowMails\Api\ElasticEmail();
                $stats = $api->get_statistics();
                
                if ($stats['success']) {
                    $data = $stats['data'];
                    ?>
                    <div class="stat-item">
                        <h3><?php _e('Total Emails Sent', 'nowmails'); ?></h3>
                        <p><?php echo number_format($data['totalEmailsSent'] ?? 0); ?></p>
                    </div>
                    <div class="stat-item">
                        <h3><?php _e('Open Rate', 'nowmails'); ?></h3>
                        <p><?php echo number_format(($data['opens'] ?? 0) / ($data['totalEmailsSent'] ?? 1) * 100, 2); ?>%</p>
                    </div>
                    <div class="stat-item">
                        <h3><?php _e('Click Rate', 'nowmails'); ?></h3>
                        <p><?php echo number_format(($data['clicks'] ?? 0) / ($data['totalEmailsSent'] ?? 1) * 100, 2); ?>%</p>
                    </div>
                    <?php
                } else {
                    echo '<p>' . esc_html__('Unable to load statistics. Please check your API key.', 'nowmails') . '</p>';
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
function copyShortcode() {
    const shortcode = '[nowmails_subscribe]';
    navigator.clipboard.writeText(shortcode).then(function() {
        alert('<?php _e('Shortcode copied to clipboard!', 'nowmails'); ?>');
    }).catch(function(err) {
        console.error('Failed to copy shortcode:', err);
    });
}
</script>

<style>
.nowmails-admin-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.nowmails-admin-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.nowmails-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.stat-item h3 {
    margin: 0 0 10px;
    font-size: 14px;
    color: #666;
}

.stat-item p {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
    color: #0073aa;
}
</style> 