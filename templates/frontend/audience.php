<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nowmails-audience">
    <div class="audience-header">
        <h2><?php _e('Audience Management', 'nowmails'); ?></h2>
        <div class="audience-actions">
            <button class="button button-primary" id="import-contacts">
                <span class="dashicons dashicons-upload"></span>
                <?php _e('Import Contacts', 'nowmails'); ?>
            </button>
            <button class="button" id="create-form">
                <span class="dashicons dashicons-plus"></span>
                <?php _e('Create Form', 'nowmails'); ?>
            </button>
            <button class="button" id="create-landing">
                <span class="dashicons dashicons-welcome-write-blog"></span>
                <?php _e('Create Landing Page', 'nowmails'); ?>
            </button>
        </div>
    </div>

    <div class="audience-stats">
        <div class="stat-card">
            <h3><?php _e('Total Contacts', 'nowmails'); ?></h3>
            <p class="stat-value" id="total-contacts">0</p>
        </div>
        <div class="stat-card">
            <h3><?php _e('Active Subscribers', 'nowmails'); ?></h3>
            <p class="stat-value" id="active-subscribers">0</p>
        </div>
        <div class="stat-card">
            <h3><?php _e('Unsubscribed', 'nowmails'); ?></h3>
            <p class="stat-value" id="unsubscribed">0</p>
        </div>
        <div class="stat-card">
            <h3><?php _e('Bounced', 'nowmails'); ?></h3>
            <p class="stat-value" id="bounced">0</p>
        </div>
    </div>

    <div class="audience-tabs">
        <nav class="nav-tab-wrapper">
            <a href="#contacts" class="nav-tab nav-tab-active"><?php _e('Contacts', 'nowmails'); ?></a>
            <a href="#forms" class="nav-tab"><?php _e('Forms', 'nowmails'); ?></a>
            <a href="#landing-pages" class="nav-tab"><?php _e('Landing Pages', 'nowmails'); ?></a>
        </nav>

        <div class="tab-content">
            <!-- Contacts Tab -->
            <div id="contacts" class="tab-pane active">
                <div class="contacts-filters">
                    <input type="text" id="contact-search" placeholder="<?php esc_attr_e('Search contacts...', 'nowmails'); ?>">
                    <select id="contact-status">
                        <option value=""><?php _e('All Status', 'nowmails'); ?></option>
                        <option value="active"><?php _e('Active', 'nowmails'); ?></option>
                        <option value="unsubscribed"><?php _e('Unsubscribed', 'nowmails'); ?></option>
                        <option value="bounced"><?php _e('Bounced', 'nowmails'); ?></option>
                    </select>
                    <button class="button" id="apply-contact-filters">
                        <?php _e('Apply Filters', 'nowmails'); ?>
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th class="check-column">
                                    <input type="checkbox" id="select-all-contacts">
                                </th>
                                <th><?php _e('Email', 'nowmails'); ?></th>
                                <th><?php _e('Name', 'nowmails'); ?></th>
                                <th><?php _e('Status', 'nowmails'); ?></th>
                                <th><?php _e('Subscribed Date', 'nowmails'); ?></th>
                                <th><?php _e('Last Activity', 'nowmails'); ?></th>
                                <th><?php _e('Actions', 'nowmails'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="contacts-list">
                            <tr>
                                <td colspan="7" class="loading"><?php _e('Loading...', 'nowmails'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="pagination">
                    <button class="button" id="prev-contacts-page" disabled>
                        <span class="dashicons dashicons-arrow-left-alt2"></span>
                        <?php _e('Previous', 'nowmails'); ?>
                    </button>
                    <span class="page-info">
                        <?php _e('Page', 'nowmails'); ?> <span id="current-contacts-page">1</span> 
                        <?php _e('of', 'nowmails'); ?> <span id="total-contacts-pages">1</span>
                    </span>
                    <button class="button" id="next-contacts-page" disabled>
                        <?php _e('Next', 'nowmails'); ?>
                        <span class="dashicons dashicons-arrow-right-alt2"></span>
                    </button>
                </div>
            </div>

            <!-- Forms Tab -->
            <div id="forms" class="tab-pane">
                <div class="forms-header">
                    <button class="button button-primary" id="create-new-form">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Create New Form', 'nowmails'); ?>
                    </button>
                </div>

                <div class="forms-grid" id="forms-list">
                    <div class="loading"><?php _e('Loading...', 'nowmails'); ?></div>
                </div>
            </div>

            <!-- Landing Pages Tab -->
            <div id="landing-pages" class="tab-pane">
                <div class="landing-pages-header">
                    <button class="button button-primary" id="create-new-landing">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Create New Landing Page', 'nowmails'); ?>
                    </button>
                </div>

                <div class="landing-pages-grid" id="landing-pages-list">
                    <div class="loading"><?php _e('Loading...', 'nowmails'); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    var currentTab = 'contacts';
    var currentContactsPage = 1;
    var totalContactsPages = 1;
    var itemsPerPage = 20;
    var contactFilters = {
        search: '',
        status: ''
    };

    // Tab switching
    $('.nav-tab').on('click', function(e) {
        e.preventDefault();
        var tab = $(this).attr('href').substring(1);
        switchTab(tab);
    });

    function switchTab(tab) {
        currentTab = tab;
        $('.nav-tab').removeClass('nav-tab-active');
        $('.nav-tab[href="#' + tab + '"]').addClass('nav-tab-active');
        $('.tab-pane').removeClass('active');
        $('#' + tab).addClass('active');

        switch(tab) {
            case 'contacts':
                loadContacts();
                break;
            case 'forms':
                loadForms();
                break;
            case 'landing-pages':
                loadLandingPages();
                break;
        }
    }

    // Contacts management
    function loadContacts(page = 1) {
        $.ajax({
            url: nowmailsFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'nowmails_get_contacts',
                nonce: nowmailsFrontend.nonce,
                page: page,
                limit: itemsPerPage,
                filters: contactFilters
            },
            success: function(response) {
                if (response.success) {
                    updateContactsList(response.data);
                    updateContactsPagination(response.total_pages);
                    updateContactStats(response.stats);
                }
            }
        });
    }

    function updateContactsList(contacts) {
        var html = '';
        if (contacts.length > 0) {
            contacts.forEach(function(contact) {
                html += '<tr>';
                html += '<td><input type="checkbox" class="contact-select" value="' + contact.id + '"></td>';
                html += '<td>' + contact.email + '</td>';
                html += '<td>' + contact.name + '</td>';
                html += '<td><span class="status-' + contact.status + '">' + contact.status + '</span></td>';
                html += '<td>' + contact.subscribed_date + '</td>';
                html += '<td>' + contact.last_activity + '</td>';
                html += '<td>';
                html += '<button class="button button-small edit-contact" data-id="' + contact.id + '">' +
                        '<span class="dashicons dashicons-edit"></span></button> ';
                html += '<button class="button button-small delete-contact" data-id="' + contact.id + '">' +
                        '<span class="dashicons dashicons-trash"></span></button>';
                html += '</td>';
                html += '</tr>';
            });
        } else {
            html = '<tr><td colspan="7"><?php _e('No contacts found', 'nowmails'); ?></td></tr>';
        }
        $('#contacts-list').html(html);
    }

    function updateContactsPagination(total) {
        totalContactsPages = Math.ceil(total / itemsPerPage);
        $('#current-contacts-page').text(currentContactsPage);
        $('#total-contacts-pages').text(totalContactsPages);
        $('#prev-contacts-page').prop('disabled', currentContactsPage === 1);
        $('#next-contacts-page').prop('disabled', currentContactsPage === totalContactsPages);
    }

    function updateContactStats(stats) {
        $('#total-contacts').text(stats.total);
        $('#active-subscribers').text(stats.active);
        $('#unsubscribed').text(stats.unsubscribed);
        $('#bounced').text(stats.bounced);
    }

    // Forms management
    function loadForms() {
        $.ajax({
            url: nowmailsFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'nowmails_get_forms',
                nonce: nowmailsFrontend.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateFormsList(response.data);
                }
            }
        });
    }

    function updateFormsList(forms) {
        var html = '';
        if (forms.length > 0) {
            forms.forEach(function(form) {
                html += '<div class="form-card">';
                html += '<div class="form-header">';
                html += '<h3>' + form.name + '</h3>';
                html += '<div class="form-actions">';
                html += '<button class="button button-small edit-form" data-id="' + form.id + '">' +
                        '<span class="dashicons dashicons-edit"></span></button> ';
                html += '<button class="button button-small delete-form" data-id="' + form.id + '">' +
                        '<span class="dashicons dashicons-trash"></span></button>';
                html += '</div>';
                html += '</div>';
                html += '<div class="form-stats">';
                html += '<p><?php _e('Submissions:', 'nowmails'); ?> ' + form.submissions + '</p>';
                html += '<p><?php _e('Conversion Rate:', 'nowmails'); ?> ' + form.conversion_rate + '%</p>';
                html += '</div>';
                html += '<div class="form-preview">';
                html += '<code>' + form.shortcode + '</code>';
                html += '</div>';
                html += '</div>';
            });
        } else {
            html = '<div class="no-forms"><?php _e('No forms found', 'nowmails'); ?></div>';
        }
        $('#forms-list').html(html);
    }

    // Landing pages management
    function loadLandingPages() {
        $.ajax({
            url: nowmailsFrontend.ajaxurl,
            type: 'POST',
            data: {
                action: 'nowmails_get_landing_pages',
                nonce: nowmailsFrontend.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateLandingPagesList(response.data);
                }
            }
        });
    }

    function updateLandingPagesList(pages) {
        var html = '';
        if (pages.length > 0) {
            pages.forEach(function(page) {
                html += '<div class="landing-page-card">';
                html += '<div class="landing-page-header">';
                html += '<h3>' + page.title + '</h3>';
                html += '<div class="landing-page-actions">';
                html += '<button class="button button-small edit-landing" data-id="' + page.id + '">' +
                        '<span class="dashicons dashicons-edit"></span></button> ';
                html += '<button class="button button-small delete-landing" data-id="' + page.id + '">' +
                        '<span class="dashicons dashicons-trash"></span></button>';
                html += '</div>';
                html += '</div>';
                html += '<div class="landing-page-stats">';
                html += '<p><?php _e('Visitors:', 'nowmails'); ?> ' + page.visitors + '</p>';
                html += '<p><?php _e('Conversions:', 'nowmails'); ?> ' + page.conversions + '</p>';
                html += '<p><?php _e('Conversion Rate:', 'nowmails'); ?> ' + page.conversion_rate + '%</p>';
                html += '</div>';
                html += '<div class="landing-page-preview">';
                html += '<a href="' + page.url + '" target="_blank"><?php _e('View Page', 'nowmails'); ?></a>';
                html += '</div>';
                html += '</div>';
            });
        } else {
            html = '<div class="no-landing-pages"><?php _e('No landing pages found', 'nowmails'); ?></div>';
        }
        $('#landing-pages-list').html(html);
    }

    // Event handlers
    $('#select-all-contacts').on('change', function() {
        $('.contact-select').prop('checked', $(this).prop('checked'));
    });

    $('#apply-contact-filters').on('click', function() {
        contactFilters.search = $('#contact-search').val();
        contactFilters.status = $('#contact-status').val();
        currentContactsPage = 1;
        loadContacts(currentContactsPage);
    });

    $('#prev-contacts-page').on('click', function() {
        if (currentContactsPage > 1) {
            currentContactsPage--;
            loadContacts(currentContactsPage);
        }
    });

    $('#next-contacts-page').on('click', function() {
        if (currentContactsPage < totalContactsPages) {
            currentContactsPage++;
            loadContacts(currentContactsPage);
        }
    });

    $('#create-new-form').on('click', function() {
        window.location.href = '<?php echo admin_url('admin.php?page=nowmails-forms&action=new'); ?>';
    });

    $('#create-new-landing').on('click', function() {
        window.location.href = '<?php echo admin_url('admin.php?page=nowmails-landing-pages&action=new'); ?>';
    });

    // Contact actions
    $(document).on('click', '.edit-contact', function() {
        var contactId = $(this).data('id');
        window.location.href = '<?php echo admin_url('admin.php?page=nowmails-contacts&action=edit&id='); ?>' + contactId;
    });

    $(document).on('click', '.delete-contact', function() {
        if (confirm('<?php _e('Are you sure you want to delete this contact?', 'nowmails'); ?>')) {
            var contactId = $(this).data('id');
            $.ajax({
                url: nowmailsFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'nowmails_delete_contact',
                    nonce: nowmailsFrontend.nonce,
                    contact_id: contactId
                },
                success: function(response) {
                    if (response.success) {
                        loadContacts(currentContactsPage);
                    }
                }
            });
        }
    });

    // Form actions
    $(document).on('click', '.edit-form', function() {
        var formId = $(this).data('id');
        window.location.href = '<?php echo admin_url('admin.php?page=nowmails-forms&action=edit&id='); ?>' + formId;
    });

    $(document).on('click', '.delete-form', function() {
        if (confirm('<?php _e('Are you sure you want to delete this form?', 'nowmails'); ?>')) {
            var formId = $(this).data('id');
            $.ajax({
                url: nowmailsFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'nowmails_delete_form',
                    nonce: nowmailsFrontend.nonce,
                    form_id: formId
                },
                success: function(response) {
                    if (response.success) {
                        loadForms();
                    }
                }
            });
        }
    });

    // Landing page actions
    $(document).on('click', '.edit-landing', function() {
        var pageId = $(this).data('id');
        window.location.href = '<?php echo admin_url('admin.php?page=nowmails-landing-pages&action=edit&id='); ?>' + pageId;
    });

    $(document).on('click', '.delete-landing', function() {
        if (confirm('<?php _e('Are you sure you want to delete this landing page?', 'nowmails'); ?>')) {
            var pageId = $(this).data('id');
            $.ajax({
                url: nowmailsFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'nowmails_delete_landing_page',
                    nonce: nowmailsFrontend.nonce,
                    page_id: pageId
                },
                success: function(response) {
                    if (response.success) {
                        loadLandingPages();
                    }
                }
            });
        }
    });

    // Initial load
    loadContacts();
});
</script> 