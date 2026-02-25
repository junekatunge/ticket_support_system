// Notification System JavaScript
class NotificationManager {
    constructor() {
        this.initializeEventListeners();
        this.startPolling();
    }
    
    initializeEventListeners() {
        // Handle notification bell click
        $(document).on('click', '.navbar-notifications', function(e) {
            e.preventDefault();
            // Toggle dropdown will be handled by Bootstrap
        });
        
        // Handle notification item clicks
        $(document).on('click', '.notification-item', function(e) {
            e.preventDefault();
            const href = $(this).attr('href');
            if (href && href !== '#') {
                window.location.href = href;
            }
        });
        
        // Handle dropdown show event
        $('.navbar-notifications').on('shown.bs.dropdown', function() {
            // Optional: Mark notifications as read when dropdown is opened
            // NotificationManager.markAsRead();
        });
    }
    
    // Poll for new notifications every 30 seconds
    startPolling() {
        setInterval(() => {
            this.updateNotificationCount();
        }, 30000); // 30 seconds
    }
    
    updateNotificationCount() {
        $.ajax({
            url: 'api/get-notification-count.php',
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const count = response.count;
                    const badge = $('.notification-badge');
                    
                    if (count > 0) {
                        if (badge.length) {
                            badge.text(count > 99 ? '99+' : count);
                        } else {
                            $('.navbar-notifications i').after(
                                `<span class="notification-badge">${count > 99 ? '99+' : count}</span>`
                            );
                        }
                        
                        // Add pulse animation for new notifications
                        badge.addClass('pulse-animation');
                        setTimeout(() => badge.removeClass('pulse-animation'), 2000);
                    } else {
                        badge.remove();
                    }
                }
            },
            error: function(xhr, status, error) {
                console.log('Error updating notifications:', error);
            }
        });
    }
    
    static markAsRead() {
        $.ajax({
            url: 'api/mark-notifications-read.php',
            method: 'POST',
            success: function(response) {
                if (response.success) {
                    $('.notification-badge').fadeOut();
                }
            }
        });
    }
    
    // Show toast notification for new urgent tickets
    static showToast(message, type = 'info') {
        const toastHtml = `
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-bell me-2"></i>${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        // Create toast container if it doesn't exist
        if (!$('.toast-container').length) {
            $('body').append('<div class="toast-container position-fixed top-0 end-0 p-3"></div>');
        }
        
        const $toast = $(toastHtml);
        $('.toast-container').append($toast);
        
        const toast = new bootstrap.Toast($toast[0]);
        toast.show();
        
        // Remove toast element after it's hidden
        $toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
}

// Initialize notification manager when document is ready
$(document).ready(function() {
    window.notificationManager = new NotificationManager();
    
    // Add CSS for pulse animation
    if (!$('#notification-styles').length) {
        $('head').append(`
            <style id="notification-styles">
                .pulse-animation {
                    animation: notification-pulse 1s ease-in-out 2;
                }
                
                @keyframes notification-pulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.2); }
                    100% { transform: scale(1); }
                }
                
                .toast-container {
                    z-index: 1055;
                }
            </style>
        `);
    }
});