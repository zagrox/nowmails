<?php
namespace NowMails\Frontend;

class Navigation {
    private $menu_items = [];

    public function __construct() {
        $this->initialize_menu_items();
    }

    private function initialize_menu_items() {
        $this->menu_items = [
            'start' => [
                'title' => __('Start', 'nowmails'),
                'icon' => 'dashicons-dashboard',
                'submenu' => [
                    'overview' => __('Overview', 'nowmails'),
                    'quick-actions' => __('Quick Actions', 'nowmails')
                ]
            ],
            'activity' => [
                'title' => __('Activity', 'nowmails'),
                'icon' => 'dashicons-chart-bar',
                'submenu' => [
                    'sent-logs' => __('Sent Mail Logs', 'nowmails'),
                    'delivery-status' => __('Delivery Status', 'nowmails')
                ]
            ],
            'audience' => [
                'title' => __('Audience', 'nowmails'),
                'icon' => 'dashicons-groups',
                'submenu' => [
                    'contacts' => __('Contacts', 'nowmails'),
                    'forms' => __('Forms', 'nowmails'),
                    'landing-pages' => __('Landing Pages', 'nowmails')
                ]
            ],
            'campaigns' => [
                'title' => __('Campaigns', 'nowmails'),
                'icon' => 'dashicons-megaphone',
                'submenu' => [
                    'all-campaigns' => __('All Campaigns', 'nowmails'),
                    'templates' => __('Email Templates', 'nowmails')
                ]
            ],
            'verifications' => [
                'title' => __('Verifications', 'nowmails'),
                'icon' => 'dashicons-shield',
                'submenu' => [
                    'email-auth' => __('Email Authentication', 'nowmails'),
                    'spam-score' => __('Spam Score Analysis', 'nowmails')
                ]
            ],
            'settings' => [
                'title' => __('Settings', 'nowmails'),
                'icon' => 'dashicons-admin-settings',
                'submenu' => [
                    'profile' => __('Profile', 'nowmails'),
                    'notifications' => __('Notifications', 'nowmails'),
                    'security' => __('Security & API', 'nowmails'),
                    'exports' => __('Exports', 'nowmails')
                ]
            ],
            'top-up' => [
                'title' => __('User Top-up', 'nowmails'),
                'icon' => 'dashicons-cart',
                'submenu' => [
                    'add-credit' => __('Add Credit', 'nowmails'),
                    'subscription' => __('Subscription', 'nowmails'),
                    'payment-history' => __('Payment History', 'nowmails')
                ]
            ]
        ];
    }

    public function render() {
        ob_start();
        ?>
        <nav class="nowmails-frontend-nav">
            <div class="nav-header">
                <h2><?php _e('NowMails', 'nowmails'); ?></h2>
            </div>
            <ul class="nav-menu">
                <?php foreach ($this->menu_items as $key => $item) : ?>
                    <li class="nav-item">
                        <a href="#<?php echo esc_attr($key); ?>" class="nav-link">
                            <span class="dashicons <?php echo esc_attr($item['icon']); ?>"></span>
                            <span class="nav-text"><?php echo esc_html($item['title']); ?></span>
                        </a>
                        <?php if (!empty($item['submenu'])) : ?>
                            <ul class="submenu">
                                <?php foreach ($item['submenu'] as $subkey => $subitem) : ?>
                                    <li>
                                        <a href="#<?php echo esc_attr($key . '-' . $subkey); ?>">
                                            <?php echo esc_html($subitem); ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <?php
        return ob_get_clean();
    }
} 