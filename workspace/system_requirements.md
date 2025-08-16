# PerfexCRM System Requirements & Environment Setup

## System Requirements

### Minimum Requirements
- **PHP**: >= 8.1+
- **MySQL**: 5.1+ (MySQL 8.x recommended)
- **Web Server**: Apache with mod_rewrite enabled

### Required PHP Extensions
The following PHP extensions are required for PerfexCRM and module development:

#### Core Extensions
- **MySQLi** - Database connectivity
- **PDO** - Database abstraction layer
- **cURL** - HTTP requests and API integrations
- **OpenSSL** - Encryption and secure communications
- **MBString** - Multi-byte string handling
- **iconv** - Character encoding conversion

#### File & Image Processing
- **GD** - Image manipulation and generation
- **Zip** - Archive handling for backups and exports

#### Email & Communication
- **IMAP** - Email server connectivity

#### System Configuration
- **allow_url_fopen** - Must be enabled for external URL access

> **Note**: In most hosting accounts these extensions are enabled by default. However, you should consult with your hosting provider to ensure all requirements are met.

## Development Environment Setup

### Development Mode
Before starting module development, enable development mode to see errors and deprecation warnings:

```php
// In application/config/app-config.php
define('ENVIRONMENT', 'development');
```

### Browser Compatibility
PerfexCRM is compatible with:
- Firefox
- Safari
- Opera
- Chrome
- Edge

### Technology Stack
- **Framework**: CodeIgniter PHP Framework
- **Frontend**: HTML, CSS, JavaScript
- **Backend**: PHP 8.x
- **Database**: MySQL 5.x/8.x
- **File Types**: JS, HTML, CSS, PHP, SQL

## Module Development Prerequisites

### Knowledge Requirements
1. **CodeIgniter Framework**: Essential for module development
   - Visit [CodeIgniter official documentation](https://codeigniter.com/docs)
   - Understand MVC architecture
   - Familiar with Active Record database patterns

2. **PHP 8.x Features**: Modern PHP development practices
3. **MySQL**: Database design and optimization
4. **JavaScript/CSS**: Frontend development for module interfaces

### Development Tools
- **Code Editor**: VS Code, PhpStorm, or similar with PHP support
- **Database Tool**: phpMyAdmin, MySQL Workbench, or similar
- **Version Control**: Git for code management
- **Browser DevTools**: For debugging frontend issues

## Module Compatibility

### Version Compatibility
- **Module System**: Available from PerfexCRM version 2.3.0+
- **Documentation Valid**: From version 2.3.2+
- **Breaking Changes**: Expect breaking changes in major updates
- **Maintenance**: Module developers responsible for compatibility updates

### CodeCanyon Distribution
If planning to sell modules on CodeCanyon:
- **Exclusivity**: Can ONLY sell on CodeCanyon (due to exclusivity agreement)
- **Responsibilities**:
  - Setting appropriate pricing
  - Module presentation and marketing
  - Customer support
  - Version compatibility maintenance
  - Copyright ownership

### Support Limitations
- **No Development Support**: PerfexCRM team doesn't provide module development support
- **Bug Reports**: Can report actual bugs via support ticket at https://my.perfexcrm.com
- **Self-Learning**: Must explore codebase and figure out implementations independently

## Performance Considerations

### Server Resources
- **Memory**: Adequate PHP memory limit for large datasets
- **Execution Time**: Sufficient for complex operations
- **File Permissions**: Proper permissions for module file operations

### Database Optimization
- **Indexes**: Add appropriate indexes for module tables
- **Queries**: Optimize database queries for performance
- **Caching**: Implement caching where beneficial

### Security Considerations
- **File Security**: All PHP files must have security headers
- **Input Validation**: Use CodeIgniter input class for all user data
- **Database Security**: Use Active Record for automatic SQL injection protection
- **Permission Checks**: Implement proper access controls

## Testing Environment

### Local Development
- **XAMPP/WAMP**: Local server environment
- **Database**: Local MySQL instance
- **Error Reporting**: Full error reporting enabled

### Staging Environment
- **Production-like**: Mirror production server configuration
- **Testing Data**: Comprehensive test dataset
- **Performance Testing**: Load testing for complex modules

### Production Deployment
- **Backup**: Always backup before module installation
- **Testing**: Thorough testing in staging environment
- **Monitoring**: Monitor for errors after deployment

This system requirements document ensures your development environment is properly configured for professional PerfexCRM module development.