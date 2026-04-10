# LUME - Social Learning Platform

LUME is a comprehensive social learning platform built with CodeIgniter 4. It provides a collaborative environment for users to share knowledge, engage in discussions, track learning progress, and build communities around educational content.

## Features

- **User Authentication**: Secure registration and login with email OTP verification
- **Social Posts**: Create, share, and interact with posts and comments
- **Learning Modules**: Structured learning paths and progress tracking
- **Leaderboard**: Gamified learning with rankings and achievements
- **Dashboard**: Personalized user dashboard for activity overview
- **Profile Management**: Customizable user profiles with avatars and college info
- **Search Functionality**: Find posts, users, and content easily
- **Email Notifications**: OTP-based authentication and password resets
- **Responsive Design**: Mobile-friendly interface

## Requirements

- PHP 8.1 or higher
- MySQL 5.7+ or MariaDB 10.1+
- Composer
- Node.js and npm (for frontend assets, if applicable)

### PHP Extensions

- intl
- mbstring
- mysqlnd
- libcurl
- json (enabled by default)

## Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/snakspy80/LUME.git
   cd LUME
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Environment Setup**:
   - Copy `env` to `.env`:
     ```bash
     cp env .env
     ```
   - Edit `.env` and configure:
     - Database settings (hostname, database name, username, password)
     - Base URL: `app.baseURL = 'http://localhost:8080'`
     - Email settings (see Email Configuration below)

4. **Database Setup**:
   ```bash
   php spark migrate
   php spark db:seed  # If seeds are available
   ```

5. **Set up writable directories**:
   ```bash
   chmod -R 755 writable/
   ```

## Running the Application

### Development Server

```bash
php spark serve
```

Access at: http://localhost:8080

### Production Deployment

Configure your web server (Apache/Nginx) to point to the `public/` directory.

**Apache Example** (in .htaccess or virtual host):
```
DocumentRoot /path/to/LUME/public
<Directory /path/to/LUME/public>
    AllowOverride All
    Require all granted
</Directory>
```

**Nginx Example**:
```
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/LUME/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## Email Configuration for OTP

LUME uses email OTP for secure authentication. Configure SMTP settings in `.env`:

```env
# Email Configuration
email.protocol = 'smtp'
email.SMTPHost = 'your-smtp-host.com'
email.SMTPUser = 'your-email@domain.com'
email.SMTPPass = 'your-password'
email.SMTPPort = 587
email.SMTPCrypto = 'tls'
email.fromEmail = 'noreply@lume.com'
email.fromName = 'LUME Platform'
```

### Supported Email Providers

- **Gmail**: Use App Passwords
- **SendGrid**: API key as password
- **Mailgun**: SMTP credentials
- **AWS SES**: SMTP credentials

### Testing Email

Use the built-in command to test email:
```bash
php spark email:test your-email@example.com
```

## Self-Hosting

For local development or personal hosting, see [SELF_HOSTING.md](SELF_HOSTING.md) for detailed instructions on running LUME on your PC, LAN sharing, and basic deployment.

## Maintenance

### Regular Tasks

1. **Update Dependencies**:
   ```bash
   composer update
   ```

2. **Run Migrations** (after updates):
   ```bash
   php spark migrate
   ```

3. **Clear Cache**:
   ```bash
   php spark cache:clear
   ```

4. **Backup Database**:
   ```bash
   mysqldump -u username -p database_name > backup.sql
   ```

### Security

- Keep PHP and dependencies updated
- Use strong passwords
- Enable HTTPS in production
- Regularly review logs in `writable/logs/`
- Monitor failed login attempts

### Performance

- Enable OPcache
- Use a CDN for static assets
- Optimize database queries
- Implement caching where appropriate

## Project Structure

```
app/
├── Config/          # Configuration files
├── Controllers/     # Request handlers
├── Models/          # Database models
├── Views/           # Templates
├── Helpers/         # Utility functions
├── Libraries/       # Custom libraries
├── Filters/         # Request filters
├── Language/        # Localization
└── Database/        # Migrations and seeds

public/              # Web root
├── index.php        # Entry point
├── .htaccess        # Apache config
└── uploads/         # User uploads

writable/            # Application data
├── cache/           # Cache files
├── logs/            # Log files
├── session/         # Session data
└── uploads/         # Generated uploads
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For issues and questions:
- Create an issue on GitHub
- Check the CodeIgniter documentation
- Review application logs in `writable/logs/`
