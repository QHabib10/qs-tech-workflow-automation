/**
 * Universal Alert Handler
 * Handles auto-hide functionality for all alert components
 */
(function() {
    'use strict';
    
    // Initialize alerts when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        initializeAlerts();
        cleanupUrlParameters();
    });
    
    function initializeAlerts() {
        const alerts = document.querySelectorAll('.alert[data-auto-hide="true"]');
        
        alerts.forEach(function(alert) {
            const duration = parseInt(alert.getAttribute('data-duration')) || 2500;
            
            // Auto-hide after specified duration
            setTimeout(function() {
                if (alert && alert.parentNode) {
                    alert.style.display = 'none';
                }
            }, duration);
        });
    }
    
    function cleanupUrlParameters() {
        try {
            const url = new URL(window.location.href);
            const paramsToRemove = ['success', 'email', 'error', 'info'];
            let hasChanges = false;
            
            paramsToRemove.forEach(function(param) {
                if (url.searchParams.has(param)) {
                    url.searchParams.delete(param);
                    hasChanges = true;
                }
            });
            
            if (hasChanges) {
                window.history.replaceState({}, '', url.toString());
            }
        } catch(e) {
            // Silently fail if URL manipulation isn't supported
        }
    }
})();
