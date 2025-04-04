<?php
if (!defined('ABSPATH')) {
    exit;
}

// Get API instance
$api = new \NowMails\Api\ElasticEmail();

// Handle form submission
if (isset($_POST['action']) && $_POST['action'] === 'add_subaccount') {
    check_admin_referer('nowmails_add_subaccount');
    
    $subaccount_data = array(
        'name' => sanitize_text_field($_POST['name']),
        'email' => sanitize_email($_POST['email']),
        'password' => $_POST['password'],
        'sendingLimit' => intval($_POST['sending_limit']),
        'enableContactList' => isset($_POST['enable_contact_list']),
        'enableEmailValidation' => isset($_POST['enable_email_validation']),
        'enableSpamCheck' => isset($_POST['enable_spam_check'])
    );

    $result = $api->create_subaccount($subaccount_data);
    
    if ($result['success']) {
        add_settings_error(
            'nowmails_messages',
            'nowmails_subaccount_created',
            __('Subaccount created successfully.', 'nowmails'),
            'updated'
        );
    } else {
        add_settings_error(
            'nowmails_messages',
            'nowmails_subaccount_error',
            $result['message'],
            'error'
        );
    }
}

// Get existing subaccounts
$subaccounts = $api->get_subaccounts();
?>

<div class="wrap nowmails-subaccounts">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors('nowmails_messages'); ?>

    <!-- Add New Subaccount Form -->
    <div class="nowmails-add-subaccount">
        <h2><?php _e('Add New Subaccount', 'nowmails'); ?></h2>
        <form method="post" action="">
            <?php wp_nonce_field('nowmails_add_subaccount'); ?>
            <input type="hidden" name="action" value="add_subaccount">
            
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="name"><?php _e('Account Name', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="name" name="name" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="email"><?php _e('Email Address', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <input type="email" id="email" name="email" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="password"><?php _e('Password', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <input type="password" id="password" name="password" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="sending_limit"><?php _e('Daily Sending Limit', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <input type="number" id="sending_limit" name="sending_limit" class="regular-text" required>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('Features', 'nowmails'); ?></th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="checkbox" name="enable_contact_list" value="1" checked>
                                <?php _e('Enable Contact List', 'nowmails'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="enable_email_validation" value="1" checked>
                                <?php _e('Enable Email Validation', 'nowmails'); ?>
                            </label>
                            <br>
                            <label>
                                <input type="checkbox" name="enable_spam_check" value="1" checked>
                                <?php _e('Enable Spam Check', 'nowmails'); ?>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Add Subaccount', 'nowmails')); ?>
        </form>
    </div>

    <!-- Subaccounts List -->
    <div class="nowmails-subaccounts-list">
        <h2><?php _e('Existing Subaccounts', 'nowmails'); ?></h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Name', 'nowmails'); ?></th>
                    <th><?php _e('Email', 'nowmails'); ?></th>
                    <th><?php _e('Status', 'nowmails'); ?></th>
                    <th><?php _e('Sending Limit', 'nowmails'); ?></th>
                    <th><?php _e('Actions', 'nowmails'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subaccounts['data'] as $subaccount): ?>
                    <tr>
                        <td><?php echo esc_html($subaccount['name']); ?></td>
                        <td><?php echo esc_html($subaccount['email']); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo esc_attr($subaccount['status']); ?>">
                                <?php echo esc_html($subaccount['status']); ?>
                            </span>
                        </td>
                        <td><?php echo number_format($subaccount['sendingLimit']); ?></td>
                        <td>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo admin_url('admin.php?page=nowmails-subaccounts&action=edit&id=' . $subaccount['publicAccountID']); ?>">
                                        <?php _e('Edit', 'nowmails'); ?>
                                    </a> |
                                </span>
                                <span class="delete">
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=nowmails-subaccounts&action=delete&id=' . $subaccount['publicAccountID']), 'delete_subaccount_' . $subaccount['publicAccountID']); ?>" 
                                       onclick="return confirm('<?php esc_attr_e('Are you sure you want to delete this subaccount?', 'nowmails'); ?>')">
                                        <?php _e('Delete', 'nowmails'); ?>
                                    </a>
                                </span>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.nowmails-subaccounts {
    margin: 20px;
}

.nowmails-add-subaccount {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.nowmails-subaccounts-list {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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

.row-actions {
    color: #666;
}

.row-actions span {
    margin: 0 5px;
}

.row-actions span:first-child {
    margin-left: 0;
}

.row-actions a {
    color: #0073aa;
    text-decoration: none;
}

.row-actions a:hover {
    color: #00a0d2;
}
</style> 