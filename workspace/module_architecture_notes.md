# PerfexCRM Module Architecture - Master Documentation

## Key Insights from Plugin Document

### Module Basics
- **Framework**: Built on CodeIgniter PHP framework
- **Location**: All modules in `/modules/` folder in root directory
- **Structure**: Each module needs unique folder name + init file with same name
- **Headers**: Required metadata in PHP block comment in init file

### Essential Module Components

#### 1. Module Init File Structure
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: [Required]
Description: [Optional]
Version: [Optional]
Requires at least: [Optional - Perfex version]
Author: [Optional]
Author URI: [Optional]
Module URI: [Optional]
*/

// Module implementation code here
```

#### 2. Required Hooks
- `register_activation_hook()` - When module is activated
- `register_deactivation_hook()` - When module is deactivated  
- `register_uninstall_hook()` - When module is uninstalled

#### 3. Core Functions Available
- `hooks()->add_action()` - Add action hooks
- `hooks()->add_filter()` - Add filter hooks
- `db_prefix()` - Get database table prefix (default: 'tbl')
- `add_option()`, `get_option()`, `update_option()` - Module settings
- `module_dir_url()`, `module_dir_path()` - Module paths

#### 4. CodeIgniter Instance Access
```php
$CI = &get_instance();
$CI->load->helper('module_name/helper_name');
$CI->load->library('module_name/library_name');
```

### Security Best Practices
1. Always add `defined('BASEPATH') or exit('No direct script access allowed');` to all PHP files
2. Use CodeIgniter input class for data gathering: `$this->input->post()`, `$this->input->get()`
3. Add empty `index.html` files in all module directories
4. Prefix all custom functions to prevent conflicts

### Database Integration
- Use `db_prefix()` for table names
- Example: `db_prefix() . 'goals'` becomes `tblgoals`
- Create tables in activation hook

### Menu Integration
- **Admin Area**: Use `$CI->app_menu->add_sidebar_menu_item()`
- **Client Area**: Use `add_theme_menu_item()`
- Support for collapsible menus with sub-items

### Payment Gateway Integration
- Create library in `modules/[module_name]/libraries/`
- Class must end with `_gateway.php`
- Register with `register_payment_gateway()`
- Extend `App_gateway` class

### Module File Structure (Confirmed from Real Modules)
```
modules/
└── [module_name]/
    ├── [module_name].php          # Init file (REQUIRED)
    ├── install.php                # Installation script (optional)
    ├── uninstall.php             # Uninstallation script (optional)
    ├── controllers/               # CodeIgniter controllers
    │   └── [Module_name].php     # Main controller (extends AdminController)
    ├── models/                    # CodeIgniter models
    │   └── [Module_name]_model.php
    ├── views/                     # CodeIgniter views
    │   ├── index.html            # Security (empty file)
    │   └── [view_files].php
    ├── libraries/                 # Custom libraries
    │   ├── index.html            # Security (empty file)
    │   └── [Module_name]_module.php
    ├── helpers/                   # Custom helpers
    │   ├── index.html            # Security (empty file)
    │   └── [module_name]_helper.php
    ├── language/                  # Language files
    │   ├── index.html            # Security (empty file)
    │   └── [language]/
    │       └── [module_name]_lang.php
    ├── assets/                    # CSS, JS, images
    │   └── [static_files]
    ├── config/                    # Module-specific configs
    │   └── csrf_exclude_uris.php # CSRF exclusions for webhooks
    ├── vendor/                    # Composer dependencies (if any)
    ├── composer.json             # Composer config (if using dependencies)
    └── index.html                # Security (empty file)
```

## Real Module Implementation Patterns

### 1. Backup Module Analysis
**File**: `modules/backup/backup.php`

Key patterns observed:
- Uses constants for module identification: `define('BACKUP_MODULE_NAME', 'backup')`
- Composer autoloader integration: `require(__DIR__ . '/vendor/autoload.php')`
- Multiple hook registrations for different events
- Cron job integration with `hooks()->add_action('after_cron_run', 'backup_perform')`
- Admin menu integration with `hooks()->add_action('admin_init', 'backup_module_init_menu_items')`
- Language file registration: `register_language_files(BACKUP_MODULE_NAME, [BACKUP_MODULE_NAME])`
- Installation hook: `register_activation_hook(BACKUP_MODULE_NAME, 'backup_module_activation_hook')`

### 2. Goals Module Analysis  
**File**: `modules/goals/goals.php`

Key patterns observed:
- Staff permissions integration: `register_staff_capabilities('goals', $capabilities, _l('goals'))`
- Global search integration with filters
- Dashboard widget integration: `hooks()->add_filter('get_dashboard_widgets', 'goals_add_dashboard_widget')`
- Database migration support: `hooks()->add_filter('migration_tables_to_replace_old_links')`
- Staff deletion handling: `hooks()->add_action('staff_member_deleted', 'goals_staff_member_deleted')`

### 3. Controller Pattern
**Base Class**: `AdminController` (extends `App_Controller`)

Module controllers must:
- Extend `AdminController` for admin area
- Call `parent::__construct()` in constructor
- Implement permission checks (e.g., `is_admin()`)
- Use `access_denied()` for unauthorized access

### 4. Installation Pattern
**File**: `modules/backup/install.php`

Installation scripts typically:
- Add module options with `add_option()`
- Create necessary directories
- Initialize module libraries
- Set up default configurations

## Core Infrastructure Files

### 1. App_modules Library
**File**: `application/libraries/App_modules.php`

Key responsibilities:
- Module activation/deactivation/uninstallation
- Module version management
- Database upgrade handling
- Module state tracking
- Hook execution for module lifecycle events

### 2. Modules Helper
**File**: `application/helpers/modules_helper.php`

Provides key functions:
- `register_activation_hook()`, `register_deactivation_hook()`, `register_uninstall_hook()`
- `register_staff_capabilities()` - Permission system integration
- `register_payment_gateway()` - Payment gateway registration
- `register_merge_fields()` - Email template integration
- `add_module_support()`, `module_supports()` - Feature detection
- Module path functions: `module_views_path()`, `module_dir_path()`, etc.

## Integration Points

### 1. Permissions System
- Use `register_staff_capabilities()` to add custom permissions
- Check permissions with `staff_can('view', 'module_name')`
- Integrate with core role management

### 2. Menu System
- Admin menus: `$CI->app_menu->add_sidebar_menu_item()`
- Client menus: `add_theme_menu_item()`
- Support for nested menus and positioning

### 3. Database Integration
- Always use `db_prefix()` for table names
- Follow CodeIgniter Active Record patterns
- Handle upgrades with module migration system

### 4. Cron Integration
- Register with `register_cron_task()` or `hooks()->add_action('after_cron_run')`
- Add cron feature counting for admin info

### 5. Global Search
- Integrate with `hooks()->add_filter('global_search_result_query')`
- Provide output formatting with `hooks()->add_filter('global_search_result_output')`

### 6. Dashboard Widgets
- Add widgets with `hooks()->add_filter('get_dashboard_widgets')`
- Specify container positioning

## Next Steps Required
1. ~~Analyze tree_directory.txt for complete file structure~~ ✓
2. ~~Study existing modules in codebase~~ ✓ 
3. Understand core CodeIgniter files relevant to modules
4. Map permissions and roles integration
5. Understand theme integration
6. Study database schema and migration patterns