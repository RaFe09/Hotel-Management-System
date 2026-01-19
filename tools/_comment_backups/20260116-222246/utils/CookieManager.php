<?php

/**
 * CookieManager - Utility class for managing cookies securely
 * Provides methods for setting, getting, and deleting cookies with security best practices
 */
class CookieManager {
    
    /**
     * Cookie expiration times (in seconds)
     */
    const REMEMBER_ME_EXPIRY = 30 * 24 * 60 * 60; // 30 days
    const FORM_DATA_EXPIRY = 7 * 24 * 60 * 60; // 7 days
    const PREFERENCE_EXPIRY = 365 * 24 * 60 * 60; // 1 year
    const SESSION_EXPIRY = 24 * 60 * 60; // 1 day
    
    /**
     * Set a cookie with secure defaults
     * 
     * @param string $name Cookie name
     * @param mixed $value Cookie value (will be JSON encoded if array/object)
     * @param int $expiry Expiration time in seconds (default: 1 day)
     * @param bool $httpOnly Whether cookie should be HTTP only (default: true)
     * @param bool $secure Whether cookie should only be sent over HTTPS (default: false for local dev)
     * @param string $sameSite SameSite attribute ('Strict', 'Lax', or 'None')
     * @return bool True on success, false on failure
     */
    public static function set($name, $value, $expiry = self::SESSION_EXPIRY, $httpOnly = true, $secure = false, $sameSite = 'Lax') {
        // JSON encode arrays and objects
        if (is_array($value) || is_object($value)) {
            $value = json_encode($value);
        }
        
        // URL encode the value
        $encodedValue = urlencode($value);
        
        // Calculate expiration timestamp
        $expires = time() + $expiry;
        
        // Build cookie string
        $cookieString = $name . '=' . $encodedValue;
        $cookieString .= '; expires=' . gmdate('D, d M Y H:i:s', $expires) . ' GMT';
        $cookieString .= '; path=/';
        $cookieString .= '; SameSite=' . $sameSite;
        
        if ($httpOnly) {
            $cookieString .= '; HttpOnly';
        }
        
        // Only set Secure flag if HTTPS is enabled or in production
        if ($secure || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')) {
            $cookieString .= '; Secure';
        }
        
        return setcookie($name, $encodedValue, $expires, '/', '', $secure, $httpOnly);
    }
    
    /**
     * Get a cookie value
     * 
     * @param string $name Cookie name
     * @param mixed $default Default value if cookie doesn't exist
     * @return mixed Cookie value (JSON decoded if applicable) or default value
     */
    public static function get($name, $default = null) {
        if (!isset($_COOKIE[$name])) {
            return $default;
        }
        
        $value = urldecode($_COOKIE[$name]);
        
        // Try to JSON decode
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }
        
        return $value;
    }
    
    /**
     * Delete a cookie
     * 
     * @param string $name Cookie name
     * @return bool True on success
     */
    public static function delete($name) {
        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
        }
        return setcookie($name, '', time() - 3600, '/');
    }
    
    /**
     * Check if a cookie exists
     * 
     * @param string $name Cookie name
     * @return bool True if cookie exists
     */
    public static function exists($name) {
        return isset($_COOKIE[$name]);
    }
    
    /**
     * Set remember me cookie with secure token
     * 
     * @param string $userId User ID
     * @param string $userType User type (admin, staff, customer)
     * @return string The generated token
     */
    public static function setRememberMe($userId, $userType) {
        // Generate a secure token
        $token = bin2hex(random_bytes(32));
        
        $data = [
            'user_id' => $userId,
            'user_type' => $userType,
            'token' => hash('sha256', $token), // Store hash in cookie
            'created_at' => time()
        ];
        
        // Store the hashed token in cookie (never store plain user ID)
        self::set('remember_me_token', $token . '|' . base64_encode(json_encode($data)), self::REMEMBER_ME_EXPIRY, true, false, 'Strict');
        
        return $token;
    }
    
    /**
     * Get remember me data if valid
     * 
     * @return array|null Array with user_id and user_type, or null if invalid
     */
    public static function getRememberMe() {
        if (!self::exists('remember_me_token')) {
            return null;
        }
        
        $cookieValue = self::get('remember_me_token');
        if (!$cookieValue || !is_string($cookieValue)) {
            return null;
        }
        
        // Parse token|data format
        $parts = explode('|', $cookieValue, 2);
        if (count($parts) !== 2) {
            return null;
        }
        
        $token = $parts[0];
        $dataJson = base64_decode($parts[1]);
        $data = json_decode($dataJson, true);
        
        if (!$data || !isset($data['token']) || !isset($data['user_id']) || !isset($data['user_type'])) {
            return null;
        }
        
        // Verify token hash
        if (hash('sha256', $token) !== $data['token']) {
            return null;
        }
        
        // Check if token is not too old (30 days max)
        if (isset($data['created_at']) && (time() - $data['created_at']) > self::REMEMBER_ME_EXPIRY) {
            self::delete('remember_me_token');
            return null;
        }
        
        return [
            'user_id' => $data['user_id'],
            'user_type' => $data['user_type']
        ];
    }
    
    /**
     * Clear remember me cookie
     */
    public static function clearRememberMe() {
        self::delete('remember_me_token');
    }
    
    /**
     * Save form data to cookie for later restoration
     * 
     * @param string $formName Form identifier (e.g., 'booking_form')
     * @param array $data Form data to save
     * @return bool True on success
     */
    public static function saveFormData($formName, $data) {
        $cookieName = 'form_data_' . $formName;
        return self::set($cookieName, $data, self::FORM_DATA_EXPIRY, false, false, 'Lax');
    }
    
    /**
     * Get saved form data
     * 
     * @param string $formName Form identifier
     * @return array|null Saved form data or null
     */
    public static function getFormData($formName) {
        $cookieName = 'form_data_' . $formName;
        return self::get($cookieName, null);
    }
    
    /**
     * Clear saved form data
     * 
     * @param string $formName Form identifier
     */
    public static function clearFormData($formName) {
        $cookieName = 'form_data_' . $formName;
        self::delete($cookieName);
    }
    
    /**
     * Save user preference
     * 
     * @param string $key Preference key (e.g., 'theme', 'language')
     * @param mixed $value Preference value
     * @return bool True on success
     */
    public static function savePreference($key, $value) {
        $preferences = self::get('user_preferences', []);
        if (!is_array($preferences)) {
            $preferences = [];
        }
        $preferences[$key] = $value;
        return self::set('user_preferences', $preferences, self::PREFERENCE_EXPIRY, false, false, 'Lax');
    }
    
    /**
     * Get user preference
     * 
     * @param string $key Preference key
     * @param mixed $default Default value if preference doesn't exist
     * @return mixed Preference value or default
     */
    public static function getPreference($key, $default = null) {
        $preferences = self::get('user_preferences', []);
        if (is_array($preferences) && isset($preferences[$key])) {
            return $preferences[$key];
        }
        return $default;
    }
    
    /**
     * Get all user preferences
     * 
     * @return array All preferences
     */
    public static function getAllPreferences() {
        return self::get('user_preferences', []);
    }
    
    /**
     * Clear all user preferences
     */
    public static function clearPreferences() {
        self::delete('user_preferences');
    }
    
    /**
     * Track page visit (for analytics)
     * 
     * @param string $page Page identifier
     * @param array $metadata Additional metadata
     */
    public static function trackVisit($page, $metadata = []) {
        $visits = self::get('visit_history', []);
        if (!is_array($visits)) {
            $visits = [];
        }
        
        $visits[] = [
            'page' => $page,
            'timestamp' => time(),
            'metadata' => $metadata
        ];
        
        // Keep only last 50 visits
        if (count($visits) > 50) {
            $visits = array_slice($visits, -50);
        }
        
        self::set('visit_history', $visits, self::SESSION_EXPIRY * 7, false, false, 'Lax');
    }
    
    /**
     * Get visit history
     * 
     * @param int $limit Maximum number of visits to return
     * @return array Visit history
     */
    public static function getVisitHistory($limit = 10) {
        $visits = self::get('visit_history', []);
        if (!is_array($visits)) {
            return [];
        }
        
        // Sort by timestamp (newest first)
        usort($visits, function($a, $b) {
            return ($b['timestamp'] ?? 0) - ($a['timestamp'] ?? 0);
        });
        
        return array_slice($visits, 0, $limit);
    }
    
    /**
     * Clear all cookies (use with caution)
     * 
     * @param array $preserveCookies Array of cookie names to preserve
     */
    public static function clearAll($preserveCookies = []) {
        foreach ($_COOKIE as $name => $value) {
            if (!in_array($name, $preserveCookies)) {
                self::delete($name);
            }
        }
    }
}

?>
