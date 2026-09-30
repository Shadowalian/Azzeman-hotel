# Email Configuration Guide

## Admin Email Configuration

The system is configured to send booking notifications to multiple admin email addresses using **TWO different SMTP servers**.

### Current Configuration

**Admin Emails (will receive all booking notifications):**
- `reservation@azzemanhotel.com` (Custom domain) - Uses Custom Domain SMTP
- `admin@azzemanhotel.com` (Custom domain) - Uses Custom Domain SMTP

### SMTP Settings

**Primary SMTP (Gmail) - for Gmail addresses:**
- Host: `smtp.gmail.com`
- Port: `587`
- User: `azzemanhotel17@gmail.com`
- Password: `#AZZEMAN1717`
- From Email: `azzemanhotel17@gmail.com`
- Encryption: `STARTTLS`

**Secondary SMTP (Custom Domain) - for azzemanhotel.com addresses:**
- Host: `mail.azzemanhotel.com`
- Port: `465`
- User: `admin@azzemanhotel.com`
- Password: `V%FUw3jT-ZoI,1*y`
- From Email: `admin@azzemanhotel.com`
- Encryption: `SSL`

### How It Works

The system **automatically routes emails** to the appropriate SMTP server based on the recipient's email domain:

- **Gmail addresses** (e.g., `azzemanhotel17@gmail.com`) → Uses Gmail SMTP (`smtp.gmail.com:587`)
- **Custom domain addresses** (e.g., `admin@azzemanhotel.com`) → Uses Custom Domain SMTP (`mail.azzemanhotel.com:465`)

When a booking is made:
1. Admin notification to `azzemanhotel17@gmail.com` → Sent via Gmail SMTP
2. Admin notification to `admin@azzemanhotel.com` → Sent via Custom Domain SMTP
3. Customer confirmation → Sent via Gmail SMTP (default)

### Configuration in config.php

Make sure your `config.php` has:

```php
// Email Configuration
// Primary SMTP settings (Gmail) - for sending to Gmail addresses
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'azzemanhotel17@gmail.com');
define('SMTP_PASS', '#AZZEMAN1717');
define('SMTP_FROM_EMAIL', 'azzemanhotel17@gmail.com');
define('SMTP_FROM_NAME', 'Azzeman Hotel');

// Secondary SMTP settings (Custom Domain) - for sending to custom domain addresses
define('SMTP2_HOST', 'mail.azzemanhotel.com');
define('SMTP2_PORT', 465);
define('SMTP2_USER', 'admin@azzemanhotel.com');
define('SMTP2_PASS', 'V%FUw3jT-ZoI,1*y');
define('SMTP2_FROM_EMAIL', 'admin@azzemanhotel.com');
define('SMTP2_FROM_NAME', 'Azzeman Hotel');

// Admin email (single - for backward compatibility)
define('ADMIN_EMAIL', 'reservation@azzemanhotel.com');

// Multiple admin emails (comma-separated) - all will receive booking notifications
// System will automatically use the appropriate SMTP server for each email
define('ADMIN_EMAILS', 'reservation@azzemanhotel.com,admin@azzemanhotel.com');
```

### Email Routing Logic

The system automatically determines which SMTP server to use:

1. **Extracts the domain** from the recipient email address
2. **If domain is `azzemanhotel.com`** → Uses Custom Domain SMTP (SMTP2)
3. **For all other domains** → Uses Gmail SMTP (SMTP1)

This ensures that:
- Emails to `azzemanhotel17@gmail.com` are sent via Gmail SMTP
- Emails to `admin@azzemanhotel.com` are sent via Custom Domain SMTP
- Customer emails (any domain) are sent via Gmail SMTP (default)

### Testing

To verify emails are being sent:
1. Make a test booking
2. Check both email inboxes:
   - `azzemanhotel17@gmail.com`
   - `admin@azzemanhotel.com`
3. Both should receive the admin notification email

### Troubleshooting

If emails are not being received:
1. Check spam/junk folders
2. Verify SMTP credentials are correct
3. Check PHP error logs: `logs/php_errors.log`
4. Ensure Gmail account has "Less secure app access" enabled or use App Password
5. Verify `APP_URL` is set correctly in `config.php` (for logo URLs in emails)

### Gmail App Password Setup

If using Gmail, you may need to:
1. Enable 2-Step Verification on your Google account
2. Generate an App Password (not your regular password)
3. Use the App Password in `SMTP_PASS`

The current password `#AZZEMAN1717` should work if it's an App Password.

