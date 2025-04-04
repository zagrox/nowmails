jQuery(document).ready(function($) {
    // Constants
    const REFRESH_INTERVAL = 300000; // 5 minutes
    const NOTIFICATION_DURATION = 3000;
    const ITEMS_PER_PAGE = 20;

    // Cache DOM elements
    const $navItems = $('.nav-item');
    const $submenus = $('.submenu');
    const $content = $('.nowmails-content');
    const $notifications = $('.notifications');

    // State management
    const state = {
        currentSection: 'start',
        currentPage: 1,
        totalPages: 1,
        filters: {
            search: '',
            status: '',
            event_type: '',
            date: ''
        }
    };

    // Navigation handling
    $('.nav-link').on('click', function(e) {
        e.preventDefault();
        const $item = $(this).closest('.nav-item');
        const section = $(this).attr('href').substring(1);
        
        // Update UI
        $navItems.not($item).removeClass('active');
        $item.toggleClass('active');
        
        // Load content
        loadSection(section);
    });

    $('.submenu a').on('click', function(e) {
        e.preventDefault();
        const section = $(this).attr('href').substring(1);
        loadSection(section);
    });

    // Section loading
    async function loadSection(section) {
        try {
            $content.html('<div class="loading">Loading...</div>');
            state.currentSection = section;

            const response = await $.ajax({
                url: nowmailsFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'nowmails_load_section',
                    nonce: nowmailsFrontend.nonce,
                    section: section
                }
            });

            if (response.success) {
                $content.html(response.data);
                initializeSectionHandlers(section);
            } else {
                throw new Error(response.data || 'Failed to load content');
            }
        } catch (error) {
            $content.html(`<div class="error">${error.message || 'An error occurred'}</div>`);
            console.error('Section load error:', error);
        }
    }

    // Section initialization
    const sectionHandlers = {
        start: initializeDashboard,
        activity: initializeActivityLogs,
        audience: initializeAudience,
        campaigns: initializeCampaigns,
        verifications: initializeVerifications,
        settings: initializeSettings,
        'top-up': initializeTopUp
    };

    function initializeSectionHandlers(section) {
        const handler = sectionHandlers[section];
        if (handler) {
            handler();
        }
    }

    // Dashboard
    function initializeDashboard() {
        // Initial load
        loadDashboardData();
        
        // Setup periodic refresh
        setInterval(loadDashboardData, REFRESH_INTERVAL);
    }

    async function loadDashboardData() {
        try {
            const response = await $.ajax({
                url: nowmailsFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'nowmails_get_dashboard_stats',
                    nonce: nowmailsFrontend.nonce
                }
            });

            if (response.success) {
                updateDashboardCards(response.data);
            }
        } catch (error) {
            console.error('Dashboard data load error:', error);
            showNotification('error', 'Failed to load dashboard data');
        }
    }

    function updateDashboardCards(stats) {
        Object.entries(stats).forEach(([key, value]) => {
            $(`.card-${key} .card-value`).text(value);
        });
    }

    // Activity logs
    function initializeActivityLogs() {
        if ($.fn.DataTable) {
            $('.nowmails-table').DataTable({
                order: [[0, 'desc']],
                pageLength: 25,
                language: {
                    processing: 'Loading...',
                    emptyTable: 'No data available'
                }
            });
        }
    }

    // Audience management
    function initializeAudience() {
        // Contact import
        $('.import-contacts').on('click', function(e) {
            e.preventDefault();
            $('#import-modal').show();
        });

        // Form creation
        $('.create-form').on('click', function(e) {
            e.preventDefault();
            $('#form-modal').show();
        });
    }

    // Campaigns
    function initializeCampaigns() {
        // Campaign creation
        $('.create-campaign').on('click', function(e) {
            e.preventDefault();
            $('#campaign-modal').show();
        });

        // Template selection
        $('.select-template').on('click', async function(e) {
            e.preventDefault();
            const templateId = $(this).data('template-id');
            await loadTemplatePreview(templateId);
        });
    }

    // Verifications
    function initializeVerifications() {
        // Email verification
        $('.verify-email').on('click', async function(e) {
            e.preventDefault();
            const email = $(this).data('email');
            await verifyEmail(email);
        });

        // Spam score check
        $('.check-spam-score').on('click', async function(e) {
            e.preventDefault();
            const content = $('#email-content').val();
            await checkSpamScore(content);
        });
    }

    // Settings
    function initializeSettings() {
        // Profile update
        $('#profile-form').on('submit', async function(e) {
            e.preventDefault();
            await updateProfile($(this));
        });

        // API settings update
        $('#api-settings-form').on('submit', async function(e) {
            e.preventDefault();
            await updateApiSettings($(this));
        });
    }

    // Top-up
    function initializeTopUp() {
        // Credit purchase
        $('.purchase-credit').on('click', async function(e) {
            e.preventDefault();
            const amount = $(this).data('amount');
            await initiatePurchase(amount);
        });

        // Subscription upgrade
        $('.upgrade-subscription').on('click', async function(e) {
            e.preventDefault();
            const plan = $(this).data('plan');
            await upgradeSubscription(plan);
        });
    }

    // Utility functions
    async function verifyEmail(email) {
        try {
            const response = await $.ajax({
                url: nowmailsFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'nowmails_verify_email',
                    nonce: nowmailsFrontend.nonce,
                    email: email
                }
            });

            if (response.success) {
                showNotification('success', 'Email verified successfully');
            } else {
                throw new Error(response.data);
            }
        } catch (error) {
            showNotification('error', error.message || 'Failed to verify email');
            console.error('Email verification error:', error);
        }
    }

    async function checkSpamScore(content) {
        try {
            const response = await $.ajax({
                url: nowmailsFrontend.ajaxurl,
                type: 'POST',
                data: {
                    action: 'nowmails_check_spam_score',
                    nonce: nowmailsFrontend.nonce,
                    content: content
                }
            });

            if (response.success) {
                displaySpamScore(response.data);
            } else {
                throw new Error(response.data);
            }
        } catch (error) {
            showNotification('error', error.message || 'Failed to check spam score');
            console.error('Spam score check error:', error);
        }
    }

    function showNotification(type, message) {
        const $notification = $('<div>')
            .addClass(`notification ${type}`)
            .text(message)
            .appendTo($notifications);
        
        setTimeout(() => {
            $notification.fadeOut(() => $notification.remove());
        }, NOTIFICATION_DURATION);
    }

    // Error handling
    $(document).ajaxError(function(event, jqXHR, settings, error) {
        console.error('AJAX error:', error);
        showNotification('error', 'An error occurred while processing your request');
    });

    // Initial load
    loadSection(state.currentSection);
}); 