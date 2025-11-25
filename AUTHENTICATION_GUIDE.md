# 🔐 Authentication Guide: Cookies & Sessions

## 📋 Overview
This guide explains the secure "Remember Me" implementation for both admin and customer login systems in the Book Rental project.

## 🎯 Learning Objectives
- Understand the difference between insecure and secure authentication
- Learn how to implement secure "Remember Me" functionality
- Understand session vs cookie authentication
- Learn security best practices for student projects

---

## 🚨 Security Comparison

| Feature | ❌ Insecure (Old Method) | ✅ Secure (New Method) |
|---------|------------------------|------------------------|
| **Cookie Content** | `base64_encode(user_id|email|md5)` | `base64_encode(token|user_type)` |
| **Token Storage** | User ID directly in cookie | Secure random token in database |
| **Token Validation** | None (anyone can edit cookie) | Database lookup + password verification |
| **Token Expiration** | Fixed 30 days | Configurable per user |
| **Session Hijacking** | Easy (change user_id in cookie) | Impossible without valid token |
| **Token Rotation** | None | Automatic regeneration on use |
| **Logout Cleanup** | Only session cleared | Cookies + database tokens cleared |

### 🏠 House Key Analogy
- **Insecure**: Writing your house key number on your door 🏠➡️🔢
- **Secure**: Having a unique temporary access card that expires and can be revoked 🏠➡️💳

---

## 🗄️ Database Structure

```sql
CREATE TABLE remember_tokens (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL,
    user_type VARCHAR(10) NOT NULL,  -- 'admin' or 'customer'
    token_hash VARCHAR(255) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Why This Structure?
- **Separate tokens** for each user type (admin/customer)
- **Hashed tokens** prevent token theft
- **Expiration** prevents permanent access
- **User association** allows revoking all user tokens

---

## 🔧 Implementation Files

### Core Files
```
Admin/
├── auth_functions.php    # Secure authentication functions
├── auth_check.php        # Include this in admin pages
├── login.php            # Updated with "Remember Me" checkbox
└── logout.php           # Updated to clear tokens

Root/
├── auth_functions.php   # Customer authentication functions
├── auth_check.php       # Include this in customer pages
├── SignIn.php           # Updated with "Remember Me" checkbox
└── logout.php           # Updated to clear tokens

Database/
└── create_remember_tokens.sql  # Database setup script
```

---

## 🚀 Step-by-Step Implementation

### Step 1: Database Setup
```bash
# Run the SQL script to create tokens table
psql -d your_database -f Database/create_remember_tokens.sql
```

### Step 2: Include Auth Functions
```php
// In admin pages requiring login
require_once('Admin/auth_check.php');

// In customer pages requiring login  
require_once('auth_check.php');
```

### Step 3: Update Login Forms
```html
<!-- Add "Remember Me" checkbox -->
<div class="form-check">
    <input class="form-check-input" type="checkbox" name="remember_me" value="1" id="rememberMe">
    <label class="form-check-label" for="rememberMe">
        Remember me for 30 days
    </label>
</div>
```

### Step 4: Handle Login Logic
```php
// After successful password verification
if (isRememberMeChecked()) {
    $token = generateRememberToken($userId, $userType);
    if ($token) {
        setRememberCookie($token, $userType);
    }
} else {
    clearRememberCookie($userType);
}
```

---

## 🛡️ Security Features Explained

### 1. Secure Token Generation
```php
$token = bin2hex(random_bytes(32));  // 64-character random string
$tokenHash = password_hash($token, PASSWORD_DEFAULT);  // Hashed storage
```
- **Random bytes**: Unpredictable token generation
- **Password hashing**: Even if database is compromised, tokens remain secure

### 2. Token Validation
```php
function validateRememberCookie($userType) {
    // 1. Check cookie exists
    // 2. Decode and validate token format
    // 3. Database lookup with expiration check
    // 4. Password verification
    // 5. Token rotation (regenerate new token)
}
```

### 3. Token Rotation
```php
// After successful validation
$newToken = generateRememberToken($userId, $userType);
setRememberCookie($newToken, $userType);
// Delete old token from database
```
- **Prevents token fixation attacks**
- **Limits damage if token is compromised**

### 4. Secure Logout
```php
function clearRememberCookie($userType) {
    // 1. Clear browser cookie
    setcookie($cookieName, '', time() - 3600, "/", "", false, true);
    
    // 2. Remove from database
    pg_query($con, "DELETE FROM remember_tokens WHERE user_id='$userId' AND user_type='$userType'");
}
```

---

## 📱 Usage Examples

### Admin Page Protection
```php
<?php
require_once('Admin/auth_check.php');
// Admin is now authenticated (session or cookie)
?>
<h1>Welcome Admin!</h1>
```

### Customer Page Protection
```php
<?php
require_once('auth_check.php');
// Customer is now authenticated (session or cookie)
?>
<h1>Welcome <?php echo $_SESSION['USER_NAME']; ?>!</h1>
```

### Manual Token Check (Advanced)
```php
<?php
require_once('auth_functions.php');

// Check if user has active remember tokens
function hasRememberTokens($userId, $userType) {
    global $con;
    $sql = "SELECT COUNT(*) FROM remember_tokens 
            WHERE user_id='$userId' AND user_type='$userType' AND expires_at > NOW()";
    $result = pg_query($con, $sql);
    return pg_fetch_result($result, 0, 0) > 0;
}
?>
```

---

## 🔍 Testing Guide

### 1. Test "Remember Me" Functionality
1. Login with "Remember Me" checked
2. Close browser completely
3. Reopen browser and visit protected page
4. Should be automatically logged in

### 2. Test Security
1. Login with "Remember Me" checked
2. Edit cookie value in browser dev tools
3. Refresh page - should be logged out (invalid token)

### 3. Test Logout
1. Login with "Remember Me" checked
2. Click logout
3. Close browser and reopen
4. Should NOT be automatically logged in

### 4. Test Token Expiration
1. Set token expiration to 1 minute for testing
2. Login with "Remember Me"
3. Wait 1+ minute and refresh page
4. Should be logged out due to expiration

---

## ⚠️ Common Security Mistakes to Avoid

### ❌ NEVER Do This
```php
// INSECURE - User ID in cookie
setcookie('auth', $userId, time() + 86400 * 30);

// INSECURE - Plain password in cookie  
setcookie('auth', $email . '|' . $password, time() + 86400 * 30);

// INSECURE - Predictable tokens
$token = md5($userId . time() . 'secret');
```

### ✅ ALWAYS Do This
```php
// SECURE - Random tokens with database storage
$token = bin2hex(random_bytes(32));
$tokenHash = password_hash($token, PASSWORD_DEFAULT);
// Store $tokenHash in database, $token in cookie

// SECURE - Proper cookie settings
setcookie($name, $value, $expires, "/", "", false, true);
```

---

## 🛠️ Maintenance

### Clean Expired Tokens (Run Weekly)
```php
<?php
require_once('auth_functions.php');
cleanExpiredTokens();
echo "Cleaned " . pg_affected_rows($result) . " expired tokens";
?>
```

### Monitor Token Usage
```sql
-- Check for unusual token activity
SELECT user_id, user_type, COUNT(*) as token_count 
FROM remember_tokens 
WHERE created_at > NOW() - INTERVAL '1 day'
GROUP BY user_id, user_type 
HAVING COUNT(*) > 5;
```

---

## 🎓 Student Learning Points

### Security Concepts
1. **Session vs Cookie**: Sessions expire on browser close, cookies persist
2. **Token-based Auth**: More secure than storing user data in cookies
3. **Hashing**: One-way encryption for sensitive data
4. **Token Rotation**: Prevents long-term token abuse
5. **Secure Cookie Settings**: HttpOnly, Secure flags

### PHP Skills
1. **Random Bytes Generation**: `bin2hex(random_bytes())`
2. **Password Hashing**: `password_hash()` and `password_verify()`
3. **Secure Cookie Handling**: Proper parameters for `setcookie()`
4. **Database Security**: Prepared statements and proper escaping

### Database Design
1. **Token Storage**: Separate table for authentication tokens
2. **Expiration Handling**: TIMESTAMP columns for automatic expiry
3. **Indexing**: Proper indexes for performance
4. **Data Relationships**: Foreign keys to user tables

---

## 📞 Troubleshooting

### Common Issues

**Problem:** "Remember Me" not working
**Solution:** 
1. Check if `auth_functions.php` is included
2. Verify database table exists
3. Check browser cookie settings
4. Look for PHP errors in logs

**Problem:** Auto-login loops to login page
**Solution:**
1. Clear all browser cookies
2. Check `validateRememberCookie()` return value
3. Verify database connection
4. Check token expiration

**Problem:** Security warnings about cookies
**Solution:**
1. Ensure `HttpOnly` and `Secure` flags are set
2. Use HTTPS in production
3. Verify cookie domain and path settings

---

## ✅ Security Checklist

- [ ] Database tokens table created
- [ ] Auth functions included in all protected pages
- [ ] "Remember Me" checkbox added to login forms
- [ ] Logout properly clears tokens
- [ ] Token rotation implemented
- [ ] Expired tokens cleanup scheduled
- [ ] HTTPS used in production
- [ ] Cookie security flags set correctly
- [ ] Input validation and sanitization
- [ ] Error logging for authentication failures

---

**Remember:** Security is about layers. Even with secure tokens, always validate input, use HTTPS, and follow security best practices! 🛡️

*This implementation provides enterprise-level security while remaining simple enough for educational projects.* 🎓
