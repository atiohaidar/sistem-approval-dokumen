# Security Best Practices

Security guidelines and best practices for the Sistem Approval Dokumen.

## Table of Contents
1. [Application Security](#application-security)
2. [Authentication & Authorization](#authentication--authorization)
3. [Data Protection](#data-protection)
4. [Input Validation](#input-validation)
5. [File Upload Security](#file-upload-security)
6. [API Security](#api-security)
7. [Database Security](#database-security)
8. [Server Security](#server-security)
9. [Monitoring & Logging](#monitoring--logging)
10. [Incident Response](#incident-response)

## Application Security

### Environment Configuration

1. **Never expose sensitive data**:
   - Keep `.env` files out of version control
   - Use strong, unique `APP_KEY`
   - Set `APP_DEBUG=false` in production
   - Use HTTPS in production

2. **Secure cookie settings**:
```env
SESSION_SECURE_COOKIE=true    # Only send over HTTPS
SESSION_HTTP_ONLY=true        # Prevent JavaScript access
SESSION_SAME_SITE=lax         # CSRF protection
SESSION_ENCRYPT=true          # Encrypt session data
```

3. **Configure CORS properly**:
```env
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.com
```

### Security Headers

The application implements these security headers:
- `X-Content-Type-Options: nosniff` - Prevent MIME sniffing
- `X-XSS-Protection: 1; mode=block` - XSS protection
- `X-Frame-Options: SAMEORIGIN` - Clickjacking protection
- `Strict-Transport-Security` - Force HTTPS
- `Referrer-Policy: strict-origin-when-cross-origin`
- `Content-Security-Policy` - Control resource loading

## Authentication & Authorization

### Password Security

1. **Strong password requirements**:
   - Minimum 8 characters
   - Use bcrypt with 12 rounds (configured in `.env`)
   - Require password confirmation on registration

2. **Account security**:
   - Implement password reset functionality
   - Consider 2FA for admin accounts
   - Lock accounts after failed login attempts (use Fail2ban)

### Token Management

1. **Sanctum tokens**:
   - Tokens stored in HTTP-only cookies (XSS protection)
   - Short session lifetime (120 minutes default)
   - Automatic token expiration
   - Single device logout supported

2. **Token best practices**:
   - Rotate tokens on sensitive actions
   - Invalidate tokens on password change
   - Clear tokens on logout

### Role-Based Access Control (RBAC)

```php
// Admin-only routes
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    // Admin endpoints
});

// Check permissions in controllers
if (!$request->user()->isAdmin()) {
    abort(403, 'Unauthorized');
}
```

## Data Protection

### Sensitive Data

1. **Encrypt sensitive data**:
   - Use Laravel's encryption for sensitive fields
   - Enable database encryption when possible
   - Encrypt backups

2. **Data minimization**:
   - Only collect necessary data
   - Delete data when no longer needed
   - Implement data retention policies

### Personal Information

1. **GDPR/Privacy compliance**:
   - Get user consent for data collection
   - Provide data export functionality
   - Implement right to deletion
   - Log data access

2. **Data anonymization**:
   - Anonymize logs and analytics
   - Use pseudonyms for user identification in logs

## Input Validation

### Server-Side Validation

Always validate on the server, never trust client input:

```php
$request->validate([
    'email' => 'required|email|max:255',
    'file' => 'required|file|mimes:pdf|max:10240',
    'approvers' => 'required|array|min:1|max:10',
]);
```

### Input Sanitization

The application uses `SanitizeInputMiddleware`:
- Removes null bytes
- Strips HTML tags from inputs
- Prevents XSS attacks

### SQL Injection Prevention

- Use Laravel's query builder and Eloquent ORM
- Never concatenate user input into SQL queries
- Use parameter binding for raw queries

```php
// Good
DB::table('users')->where('email', $email)->first();

// Bad - DON'T DO THIS
DB::select("SELECT * FROM users WHERE email = '$email'");
```

## File Upload Security

### File Validation

1. **Validate file type**:
```php
$request->validate([
    'file' => 'required|file|mimes:pdf|max:10240',
]);
```

2. **Check MIME type**:
```php
$mimeType = $file->getMimeType();
if ($mimeType !== 'application/pdf') {
    throw new \Exception('Invalid file type');
}
```

3. **Limit file size**:
   - Backend: 10MB max (configurable)
   - Nginx: `client_max_body_size 20M;`
   - PHP: `upload_max_filesize=20M`

### File Storage

1. **Secure storage location**:
   - Store files outside web root
   - Use Laravel storage (`storage/app/public`)
   - Set proper permissions (755 for directories, 644 for files)

2. **Generate unique filenames**:
```php
$fileName = time() . '_' . uniqid() . '.' . $extension;
```

3. **Serve files securely**:
   - Use controller methods to serve files
   - Check user permissions before serving
   - Add watermarks to sensitive documents

### Virus Scanning (Recommended)

Consider integrating ClamAV for virus scanning:
```bash
sudo apt install clamav clamav-daemon
sudo freshclam
```

## API Security

### Rate Limiting

Rate limits are configured per endpoint:
```php
// Login: 10 requests per minute
Route::middleware('throttle:10,1')->post('/auth/login');

// API: 60 requests per minute
Route::middleware('throttle:60,1')->get('/documents');
```

### API Authentication

1. **Require authentication**:
   - All sensitive endpoints require authentication
   - Public endpoints limited to read-only access

2. **Validate API tokens**:
   - Sanctum validates tokens automatically
   - Tokens expire after inactivity

### CSRF Protection

- CSRF protection enabled for stateful API requests
- Frontend must fetch CSRF token before making requests
- Sanctum handles CSRF validation automatically

## Database Security

### Connection Security

1. **Secure credentials**:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1           # Use localhost when possible
DB_DATABASE=approval_system
DB_USERNAME=approval_user    # Don't use root
DB_PASSWORD=strong_password  # Use strong, unique password
```

2. **Database user permissions**:
```sql
-- Create dedicated user with limited permissions
CREATE USER 'approval_user'@'localhost' IDENTIFIED BY 'password';
GRANT SELECT, INSERT, UPDATE, DELETE ON approval_system.* TO 'approval_user'@'localhost';
```

### Query Security

1. **Use prepared statements**:
   - Eloquent and Query Builder use prepared statements
   - Prevents SQL injection

2. **Limit query results**:
   - Use pagination
   - Set reasonable defaults (15 items per page)

### Database Backups

1. **Regular backups**:
```bash
# Daily backup
0 2 * * * mysqldump -u user -p'password' database > backup_$(date +\%Y\%m\%d).sql
```

2. **Secure backup storage**:
   - Encrypt backups
   - Store offsite
   - Test restore procedures

## Server Security

### Operating System

1. **Keep system updated**:
```bash
sudo apt update && sudo apt upgrade -y
```

2. **Enable firewall**:
```bash
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS
sudo ufw enable
```

3. **Disable unnecessary services**:
```bash
sudo systemctl disable <service-name>
```

### SSH Security

1. **Use key-based authentication**:
```bash
# Generate SSH key
ssh-keygen -t ed25519

# Copy to server
ssh-copy-id user@server
```

2. **Disable password authentication** (`/etc/ssh/sshd_config`):
```
PasswordAuthentication no
PermitRootLogin no
```

3. **Use Fail2ban**:
```bash
sudo apt install fail2ban
sudo systemctl enable fail2ban
```

### Web Server

1. **Nginx security**:
```nginx
# Hide version
server_tokens off;

# Limit request size
client_max_body_size 20M;

# Prevent directory listing
autoindex off;

# Block access to hidden files
location ~ /\.(?!well-known).* {
    deny all;
}
```

2. **SSL/TLS configuration**:
```nginx
ssl_protocols TLSv1.2 TLSv1.3;
ssl_ciphers HIGH:!aNULL:!MD5;
ssl_prefer_server_ciphers on;
```

### PHP Security

1. **PHP configuration** (`php.ini`):
```ini
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
memory_limit = 256M
```

2. **Disable dangerous functions**:
```ini
disable_functions = exec,passthru,shell_exec,system,proc_open,popen,curl_exec,curl_multi_exec,parse_ini_file,show_source
```

## Monitoring & Logging

### Application Logs

1. **Configure logging** (`.env`):
```env
LOG_CHANNEL=stack
LOG_LEVEL=warning          # Don't log debug in production
LOG_STACK=daily            # Rotate logs daily
```

2. **Log important events**:
   - Authentication attempts
   - Authorization failures
   - File uploads/downloads
   - Approval actions
   - Admin actions

3. **Protect log files**:
```bash
chmod 640 storage/logs/*.log
chown www-data:www-data storage/logs
```

### Security Monitoring

1. **Monitor failed logins**:
   - Track failed authentication attempts
   - Alert on suspicious activity
   - Implement account lockout

2. **File integrity monitoring**:
```bash
# Install AIDE
sudo apt install aide
sudo aideinit
```

3. **Monitor system resources**:
   - Disk usage
   - Memory usage
   - CPU usage
   - Network traffic

### Audit Logs

Implement audit logging for sensitive operations:
```php
Log::info('Document approved', [
    'user_id' => $user->id,
    'document_id' => $document->id,
    'ip_address' => $request->ip(),
    'user_agent' => $request->userAgent(),
]);
```

## Incident Response

### Preparation

1. **Security contact**:
   - Designate security officer
   - Create security email (security@domain.com)
   - Document escalation procedures

2. **Backup verification**:
   - Test restore procedures regularly
   - Maintain offline backups
   - Document recovery procedures

### Detection

1. **Monitor for indicators**:
   - Unusual login patterns
   - Unexpected file modifications
   - Increased error rates
   - Unusual network traffic

2. **Set up alerts**:
   - Failed authentication alerts
   - Server resource alerts
   - Application error alerts

### Response

1. **Immediate actions**:
   - Isolate affected systems
   - Preserve evidence
   - Assess scope of incident
   - Notify stakeholders

2. **Investigation**:
   - Review logs
   - Identify attack vector
   - Document findings
   - Patch vulnerabilities

3. **Recovery**:
   - Restore from clean backups
   - Reset credentials
   - Update security measures
   - Monitor for recurrence

### Post-Incident

1. **Document lessons learned**:
   - What happened
   - How it was detected
   - Response actions taken
   - Improvements needed

2. **Update procedures**:
   - Improve monitoring
   - Update security policies
   - Conduct security training

## Security Checklist

### Development
- [ ] No hardcoded credentials
- [ ] Input validation on all endpoints
- [ ] Output encoding to prevent XSS
- [ ] SQL injection prevention
- [ ] CSRF protection enabled
- [ ] Authentication required for sensitive operations
- [ ] Authorization checks implemented
- [ ] Secure file upload handling

### Deployment
- [ ] `APP_DEBUG=false`
- [ ] Strong `APP_KEY` generated
- [ ] HTTPS enabled
- [ ] Secure cookie settings
- [ ] CORS configured correctly
- [ ] Rate limiting enabled
- [ ] Security headers configured
- [ ] Error pages don't expose sensitive info

### Server
- [ ] Firewall enabled and configured
- [ ] SSH key authentication only
- [ ] Fail2ban installed
- [ ] System fully updated
- [ ] Unnecessary services disabled
- [ ] Web server hardened
- [ ] PHP hardened
- [ ] Database secured

### Monitoring
- [ ] Logging configured
- [ ] Log rotation enabled
- [ ] Security monitoring active
- [ ] Backup strategy implemented
- [ ] Backup testing scheduled
- [ ] Alert system configured
- [ ] Incident response plan documented

## Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [Laravel Security Best Practices](https://laravel.com/docs/security)
- [PHP Security Guide](https://www.php.net/manual/en/security.php)
- [CIS Benchmarks](https://www.cisecurity.org/cis-benchmarks/)

## Support

For security issues:
- Email: security@your-domain.com
- Do not disclose publicly until patched

---

**Last Updated**: 2025-10-29
**Version**: 1.0.0
