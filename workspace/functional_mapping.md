# PerfexCRM Functional Mapping

## Core System Architecture

### 1. Application Structure
```
application/
├── core/                  # Core framework extensions
│   ├── AdminController.php        # Base for admin controllers
│   ├── App_Controller.php         # Main application controller
│   ├── ClientsController.php      # Base for client area controllers
│   ├── CRM_Controller.php         # CRM-specific controller
│   └── App_Model.php              # Base model class
├── controllers/           # Main application controllers
│   ├── admin/            # Admin area controllers
│   └── gateways/         # Payment gateway controllers
├── models/               # Data layer
├── libraries/            # Core libraries and services
│   ├── App_modules.php           # Module management system
│   ├── App_menu.php              # Menu system
│   ├── gateways/                 # Payment gateway libraries
│   └── mails/                    # Email template libraries
├── helpers/              # Helper functions
│   ├── modules_helper.php        # Module-specific functions
│   ├── admin_helper.php          # Admin area helpers
│   └── general_helper.php        # General utility functions
└── hooks/                # System hooks and initialization
    ├── InitModules.php           # Module initialization
    └── InitHook.php              # Core hook initialization
```

## Module System Integration Points

### 1. Module Lifecycle Management
**Primary File**: `application/libraries/App_modules.php`
- **Activation**: `activate($name)` - Triggers `activate_{module}_module` hook
- **Deactivation**: `deactivate($name)` - Triggers `deactivate_{module}_module` hook
- **Uninstallation**: `uninstall($name)` - Calls uninstall.php or hook
- **Version Management**: Database upgrade handling
- **State Tracking**: Active/inactive module status

**Helper Functions**: `application/helpers/modules_helper.php`
- `register_activation_hook()` - Register activation callback
- `register_deactivation_hook()` - Register deactivation callback
- `register_uninstall_hook()` - Register uninstall callback

### 2. Menu System Integration
**Primary File**: `application/libraries/App_menu.php`

**Admin Area Menus**:
- Function: `add_sidebar_menu_item($id, $config)`
- Hook: `admin_init` 
- Default positions: Dashboard(1), Customers(5), Sales(10), etc.

**Client Area Menus**:
- Function: `add_theme_menu_item($id, $config)`
- Hook: `clients_init`
- Integration with theme system

### 3. Permissions & Roles System
**Primary Files**: 
- `application/controllers/admin/Roles.php` - Role management
- `application/models/Roles_model.php` - Role data layer

**Module Integration**:
- Function: `register_staff_capabilities($feature_id, $config, $name)`
- Hook: `staff_permissions` filter
- Permission Check: `staff_can($capability, $feature)`

### 4. Database Integration
**Table Prefix**: Always use `db_prefix()` function
- Default prefix: `tbl`
- Module tables: `db_prefix() . 'module_table_name'`

**Migration System**:
- File: `application/libraries/App_module_migration.php`
- Module migrations in: `modules/{module_name}/migrations/`

### 5. Cron Job System
**Primary File**: `application/controllers/Cron.php`

**Module Integration**:
- Function: `register_cron_task($function)`
- Hook: `after_cron_run`
- Feature counting: `numbers_of_features_using_cron_job` filter

### 6. Email System
**Primary Files**:
- `application/libraries/App_Email.php` - Email handling
- `application/libraries/mails/` - Email templates

**Module Integration**:
- Create email templates in `modules/{module}/libraries/mails/`
- Extend `App_mail_template` class
- Register merge fields with `register_merge_fields()`

### 7. Payment Gateway System
**Primary Files**:
- `application/libraries/gateways/App_gateway.php` - Base gateway class
- `application/controllers/gateways/` - Gateway controllers

**Module Integration**:
- Function: `register_payment_gateway($id, $module)`
- Library: Create `{Module}_gateway.php` extending `App_gateway`
- Controller: Handle payment flows and webhooks

### 8. Global Search System
**Integration Points**:
- Hook: `global_search_result_query` filter - Add search queries
- Hook: `global_search_result_output` filter - Format results
- Implementation: Add to module init file

### 9. Dashboard Widget System
**Integration Point**:
- Hook: `get_dashboard_widgets` filter
- Widget Path: `modules/{module}/views/widget.php`
- Container positioning: left/right columns

### 10. Theme Integration
**Client Area Themes**:
- Hook: `app_client_assets` - Load CSS/JS
- Function: `register_theme_assets_hook($function)`
- Theme files: `application/views/themes/`

## Core Feature Mapping

### 1. Customer Management
**Controllers**: 
- `application/controllers/admin/Clients.php` - Admin management
- `application/controllers/Clients.php` - Client area

**Models**: 
- `application/models/Clients_model.php` - Customer data
- `application/models/Client_groups_model.php` - Customer groups

**Integration Hooks**:
- `before_client_added` - Before customer creation
- `after_client_added` - After customer creation
- `client_updated` - Customer data updated

### 2. Invoice Management
**Controllers**:
- `application/controllers/admin/Invoices.php` - Admin management
- `application/controllers/Invoice.php` - Client area

**Models**:
- `application/models/Invoices_model.php` - Invoice data
- `application/models/Invoice_items_model.php` - Invoice items

**Integration Hooks**:
- `before_invoice_added` - Before invoice creation
- `after_invoice_added` - After invoice creation
- `invoice_status_changed` - Status updates

### 3. Project Management
**Controllers**:
- `application/controllers/admin/Projects.php` - Admin management

**Models**:
- `application/models/Projects_model.php` - Project data

**Integration Hooks**:
- `project_created` - New project
- `project_status_changed` - Status updates
- `project_file_uploaded` - File attachments

### 4. Task Management
**Controllers**:
- `application/controllers/admin/Tasks.php` - Task management

**Models**:
- `application/models/Tasks_model.php` - Task data

**Integration Hooks**:
- `task_created` - New task
- `task_status_changed` - Status updates
- `task_assigned` - Task assignment

### 5. Lead Management
**Controllers**:
- `application/controllers/admin/Leads.php` - Lead management

**Models**:
- `application/models/Leads_model.php` - Lead data

**Integration Hooks**:
- `lead_created` - New lead
- `lead_status_changed` - Status updates
- `lead_assigned` - Lead assignment

## Module Development Checklist

### Required Files
- [ ] `{module_name}.php` - Init file with headers
- [ ] `controllers/{Module_name}.php` - Main controller
- [ ] `install.php` - Installation script (if needed)
- [ ] `index.html` - Security files in all directories

### Core Integrations
- [ ] Register activation/deactivation hooks
- [ ] Add admin menu items (if needed)
- [ ] Register language files (if using translations)
- [ ] Set up permissions (if needed)
- [ ] Add database tables (if needed)
- [ ] Register cron tasks (if needed)

### Optional Integrations
- [ ] Global search integration
- [ ] Dashboard widgets
- [ ] Email templates
- [ ] Payment gateway (if applicable)
- [ ] Client area menus
- [ ] Custom fields integration
- [ ] Export functionality

## Security Considerations

### File Security
- Add `defined('BASEPATH') or exit('No direct script access allowed');` to all PHP files
- Include empty `index.html` files in all directories
- Use proper file permissions

### Data Security
- Use CodeIgniter input class: `$this->input->post()`, `$this->input->get()`
- Validate and sanitize all user inputs
- Use parameterized database queries
- Implement proper permission checks

### CSRF Protection
- Exclude webhook URLs in `config/csrf_exclude_uris.php`
- Use CSRF tokens in forms
- Validate requests properly