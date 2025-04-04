<?php
if (!defined('ABSPATH')) {
    exit;
}

// Handle form submission
if (isset($_POST['action']) && $_POST['action'] === 'update_branding') {
    check_admin_referer('nowmails_update_branding');
    
    // Handle logo upload
    if (!empty($_FILES['branding_logo']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('branding_logo', 0);
        
        if (!is_wp_error($attachment_id)) {
            update_option('nowmails_branding_logo', $attachment_id);
        }
    }

    // Update other branding options
    update_option('nowmails_branding_company_name', sanitize_text_field($_POST['company_name']));
    update_option('nowmails_branding_primary_color', sanitize_hex_color($_POST['primary_color']));
    update_option('nowmails_branding_custom_css', sanitize_textarea_field($_POST['custom_css']));
    update_option('nowmails_branding_custom_js', sanitize_textarea_field($_POST['custom_js']));
    update_option('nowmails_branding_favicon', sanitize_text_field($_POST['favicon']));
    update_option('nowmails_branding_login_logo', sanitize_text_field($_POST['login_logo']));
    update_option('nowmails_branding_admin_footer', sanitize_text_field($_POST['admin_footer']));

    add_settings_error(
        'nowmails_messages',
        'nowmails_branding_updated',
        __('Branding settings updated successfully.', 'nowmails'),
        'updated'
    );
}

// Get current branding settings
$company_name = get_option('nowmails_branding_company_name', get_bloginfo('name'));
$primary_color = get_option('nowmails_branding_primary_color', '#0073aa');
$custom_css = get_option('nowmails_branding_custom_css', '');
$custom_js = get_option('nowmails_branding_custom_js', '');
$favicon = get_option('nowmails_branding_favicon', '');
$login_logo = get_option('nowmails_branding_login_logo', '');
$admin_footer = get_option('nowmails_branding_admin_footer', '');
$branding_logo = get_option('nowmails_branding_logo', '');
?>

<div class="wrap nowmails-branding">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php settings_errors('nowmails_messages'); ?>

    <form method="post" action="" enctype="multipart/form-data">
        <?php wp_nonce_field('nowmails_update_branding'); ?>
        <input type="hidden" name="action" value="update_branding">

        <!-- Company Information -->
        <div class="nowmails-section">
            <h2><?php _e('Company Information', 'nowmails'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="company_name"><?php _e('Company Name', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="company_name" name="company_name" class="regular-text" 
                               value="<?php echo esc_attr($company_name); ?>">
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="branding_logo"><?php _e('Company Logo', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <?php if ($branding_logo): ?>
                            <div class="current-logo">
                                <?php echo wp_get_attachment_image($branding_logo, 'thumbnail'); ?>
                            </div>
                        <?php endif; ?>
                        <input type="file" id="branding_logo" name="branding_logo" accept="image/*">
                        <p class="description"><?php _e('Recommended size: 200x50 pixels', 'nowmails'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="primary_color"><?php _e('Primary Color', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <input type="color" id="primary_color" name="primary_color" 
                               value="<?php echo esc_attr($primary_color); ?>">
                    </td>
                </tr>
            </table>
        </div>

        <!-- Login Page Customization -->
        <div class="nowmails-section">
            <h2><?php _e('Login Page Customization', 'nowmails'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="login_logo"><?php _e('Login Page Logo', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="login_logo" name="login_logo" class="regular-text" 
                               value="<?php echo esc_attr($login_logo); ?>">
                        <p class="description"><?php _e('Enter the URL of your login page logo', 'nowmails'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="favicon"><?php _e('Favicon', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="favicon" name="favicon" class="regular-text" 
                               value="<?php echo esc_attr($favicon); ?>">
                        <p class="description"><?php _e('Enter the URL of your favicon', 'nowmails'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Custom Code -->
        <div class="nowmails-section">
            <h2><?php _e('Custom Code', 'nowmails'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="custom_css"><?php _e('Custom CSS', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <textarea id="custom_css" name="custom_css" rows="10" class="large-text code"><?php echo esc_textarea($custom_css); ?></textarea>
                        <p class="description"><?php _e('Add custom CSS styles to override default styles', 'nowmails'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="custom_js"><?php _e('Custom JavaScript', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <textarea id="custom_js" name="custom_js" rows="10" class="large-text code"><?php echo esc_textarea($custom_js); ?></textarea>
                        <p class="description"><?php _e('Add custom JavaScript code', 'nowmails'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Admin Footer -->
        <div class="nowmails-section">
            <h2><?php _e('Admin Footer', 'nowmails'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="admin_footer"><?php _e('Footer Text', 'nowmails'); ?></label>
                    </th>
                    <td>
                        <input type="text" id="admin_footer" name="admin_footer" class="regular-text" 
                               value="<?php echo esc_attr($admin_footer); ?>">
                        <p class="description"><?php _e('Custom text to display in the admin footer', 'nowmails'); ?></p>
                    </td>
                </tr>
            </table>
        </div>

        <?php submit_button(__('Save Branding Settings', 'nowmails')); ?>
    </form>
</div>

<style>
.nowmails-branding {
    margin: 20px;
}

.nowmails-section {
    background: #fff;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.nowmails-section h2 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.current-logo {
    margin-bottom: 10px;
}

.current-logo img {
    max-width: 200px;
    height: auto;
}

input[type="color"] {
    width: 100px;
    height: 30px;
    padding: 0;
    border: none;
    border-radius: 3px;
    cursor: pointer;
}

textarea.code {
    font-family: monospace;
    font-size: 14px;
    line-height: 1.4;
}

.description {
    color: #666;
    font-style: italic;
    margin-top: 5px;
}
</style> 