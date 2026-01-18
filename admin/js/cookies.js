




const CookieHelper = {
    


    set: function(name, value, days = 7) {
        const expires = new Date();
        expires.setTime(expires.getTime() + (days * 24 * 60 * 60 * 1000));
        document.cookie = name + '=' + encodeURIComponent(value) + 
                         ';expires=' + expires.toUTCString() + 
                         ';path=/;SameSite=Lax';
    },
    
    


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
    
    


    delete: function(name) {
        document.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:00 UTC;path=/;';
    },
    
    


    savePreference: function(key, value) {
        try {
            const preferences = JSON.parse(this.get('user_preferences', '{}'));
            preferences[key] = value;
            this.set('user_preferences', JSON.stringify(preferences), 365);
        } catch (e) {
            console.error('Error saving preference:', e);
        }
    },
    
    


    getPreference: function(key, defaultValue = null) {
        try {
            const preferences = JSON.parse(this.get('user_preferences', '{}'));
            return preferences[key] !== undefined ? preferences[key] : defaultValue;
        } catch (e) {
            return defaultValue;
        }
    },
    
    


    trackVisit: function(page, metadata = {}) {
        try {
            const visits = JSON.parse(this.get('visit_history', '[]'));
            visits.push({
                page: page,
                timestamp: Date.now(),
                metadata: metadata
            });
            
            
            if (visits.length > 50) {
                visits = visits.slice(-50);
            }
            
            this.set('visit_history', JSON.stringify(visits), 7);
        } catch (e) {
            console.error('Error tracking visit:', e);
        }
    },
    
    


    getFormData: function(formName) {
        try {
            const cookieName = 'form_data_' + formName;
            const data = this.get(cookieName);
            return data ? JSON.parse(data) : null;
        } catch (e) {
            return null;
        }
    },
    
    


    saveFormData: function(formName, data) {
        try {
            const cookieName = 'form_data_' + formName;
            this.set(cookieName, JSON.stringify(data), 7);
        } catch (e) {
            console.error('Error saving form data:', e);
        }
    },
    
    


    clearFormData: function(formName) {
        const cookieName = 'form_data_' + formName;
        this.delete(cookieName);
    }
};


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
