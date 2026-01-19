/**
 * Client-side Cookie Helper for Hotel Management System
 * Provides utilities for managing cookies from JavaScript
 */

const CookieHelper = {
    /**
     * Set a cookie
     */
    set: function(name, value, days = 7) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + encodeURIComponent(value) + 
                         ';expires=' + expires.toUTCString() + 
                         ';path=/;SameSite=Lax';
    },
    
    /**
     * Get a cookie value
     */
    get: function(name, defaultValue = null) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) {
                return decodeURIComponent(c.substring(nameEQ.length, c.length));
            }
        }
        return defaultValue;
    },
    
    /**
     * Delete a cookie
     */
    delete: function(name) {
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
    },
    
    /**
     * Save user preference
     */
    savePreference: function(key, value) {
        try {
            const preferences = JSON.parse(this.get('user_preferences', '{}'));
            preferences[key] = value;
            this.set('user_preferences', JSON.stringify(preferences), 365);
        } catch (e) {
            console.error('Error saving preference:', e);
        }
    },
    
    /**
     * Get user preference
     */
    getPreference: function(key, defaultValue = null) {
        try {
            const preferences = JSON.parse(this.get('user_preferences', '{}'));
            return preferences[key] !== undefined ? preferences[key] : defaultValue;
        } catch (e) {
            return defaultValue;
        }
    },
    
    /**
     * Track page visit
     */
    trackVisit: function(page, metadata = {}) {
        try {
            const visits = JSON.parse(this.get('visit_history', '[]'));
            visits.push({
                page: page,
                timestamp: Date.now(),
                metadata: metadata
            });
            
            // Keep only last 50 visits
            if (visits.length > 50) {
                visits = visits.slice(-50);
            }
            
            this.set('visit_history', JSON.stringify(visits), 7);
        } catch (e) {
            console.error('Error tracking visit:', e);
        }
    },
    
    /**
     * Get saved form data
     */
    getFormData: function(formName) {
        try {
            const cookieName = 'form_data_' + formName;
            const data = this.get(cookieName);
            return data ? JSON.parse(data) : null;
        } catch (e) {
            return null;
        }
    },
    
    /**
     * Save form data
     */
    saveFormData: function(formName, data) {
        try {
            const cookieName = 'form_data_' + formName;
            this.set(cookieName, JSON.stringify(data), 7);
        } catch (e) {
            console.error('Error saving form data:', e);
        }
    },
    
    /**
     * Clear saved form data
     */
    clearFormData: function(formName) {
        const cookieName = 'form_data_' + formName;
        this.delete(cookieName);
    }
};

// Auto-track page visits on load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        const pageName = window.location.pathname.split('/').pop() || 'index';
        CookieHelper.trackVisit(pageName, {
            referrer: document.referrer,
            userAgent: navigator.userAgent.substring(0, 50)
        });
    });
} else {
    const pageName = window.location.pathname.split('/').pop() || 'index';
    CookieHelper.trackVisit(pageName, {
        referrer: document.referrer,
        userAgent: navigator.userAgent.substring(0, 50)
    });
}
