# PerfexCRM Module Development - Complete Guide

## Table of Contents
1. [Module Architecture Overview](#module-architecture-overview)
2. [Module Structure & Files](#module-structure--files)
3. [Core Integration Systems](#core-integration-systems)
4. [Development Patterns](#development-patterns)
5. [Security Guidelines](#security-guidelines)
6. [Testing & Deployment](#testing--deployment)
7. [Advanced Features](#advanced-features)

## Module Architecture Overview

PerfexCRM uses a sophisticated module system built on top of CodeIgniter that provides:
- **Hook-based Integration**: Event-driven architecture with action and filter hooks
- **Permission System**: Granular role-based access control
- **Database Abstraction**: Prefix-aware database operations with migration support
- **Menu Integration**: Both admin and client area menu systems
- **Theme Support**: Client area theme integration
- **Multi-language**: Complete localization support

### Core Principles
1. **Modular Design**: Each module is self-contained with its own MVC structure
2. **Hook-Driven**: Modules integrate through hooks rather than core modifications
3. **Security First**: Built-in protection against common vulnerabilities
4. **Backward Compatibility**: Version management and migration support

## Module Structure & Files

### Required Structure
```
modules/
└── [module_name]/
    ├── [module_name].php          # Init file (REQUIRED)
    ├── install.php                # Installation script
    ├── uninstall.php             # Uninstallation script
    ├── controllers/               # Module controllers
    │   ├── index.html            # Security file
    │   └── [Module_name].php     # Main controller
    ├── models/                    # Module models
    │   ├── index.html            # Security file
    │   └── [Module_name]_model.php
    ├── views/                     # Module views
    │   ├── index.html            # Security file
    │   ├── manage.php            # List/management view
    │   ├── [entity].php          # Single entity view
    │   └── widget.php            # Dashboard widget (optional)
    ├── libraries/                 # Custom libraries
    │   ├── index.html            # Security file
    │   └── [Module_name]_module.php
    ├── helpers/                   # Custom helpers
    │   ├── index.html            # Security file
    │   └── [module_name]_helper.php
    ├── language/                  # Localization files
    │   ├── index.html            # Security file
    │   └── [language]/
    │       └── [module_name]_lang.php
    ├── assets/                    # Static assets
    │   ├── css/
    │   ├── js/
    │   └── images/
    ├── migrations/               # Database migrations
    │   └── 001_initial_setup.php
    ├── config/                    # Module configuration
    │   └── csrf_exclude_uris.php # CSRF exclusions
    ├── vendor/                    # Composer dependencies
    ├── composer.json             # Composer configuration
    └── index.html                # Security file
```

### Module Init File Template
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Your Module Name
Description: Brief description of what your module does
Version: 1.0.0
Requires at least: 2.3.*
Author: Your Name
Author URI: https://yourwebsite.com
Module URI: https://module-website.com
*/

// Module constants
define('YOUR_MODULE_NAME', 'your_module');
define('YOUR_MODULE_VERSION', '1.0.0');

// Load dependencies if using Composer
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once(__DIR__ . '/vendor/autoload.php');
}

// Core hooks registration
hooks()->add_action('admin_init', 'your_module_init_admin_menu');
hooks()->add_action('clients_init', 'your_module_init_client_menu');

// Module lifecycle hooks
register_activation_hook(YOUR_MODULE_NAME, 'your_module_activation_hook');
register_deactivation_hook(YOUR_MODULE_NAME, 'your_module_deactivation_hook');
register_uninstall_hook(YOUR_MODULE_NAME, 'your_module_uninstall_hook');

// Language files registration
register_language_files(YOUR_MODULE_NAME, [YOUR_MODULE_NAME]);

// Permission system integration
hooks()->add_action('admin_init', 'your_module_permissions');

// Additional integrations
hooks()->add_filter('get_dashboard_widgets', 'your_module_dashboard_widget');
hooks()->add_filter('global_search_result_query', 'your_module_global_search', 10, 3);
hooks()->add_filter('global_search_result_output', 'your_module_search_output', 10, 2);

// Implementation functions below...
```

## Core Integration Systems

### 1. Hook System
PerfexCRM uses WordPress-style hooks for extensibility:

**Action Hooks** - Execute code at specific points:
```php
// Register an action
hooks()->add_action('hook_name', 'callback_function', $priority, $accepted_args);

// Trigger an action
hooks()->do_action('hook_name', $arg1, $arg2);
```

**Filter Hooks** - Modify data:
```php
// Register a filter
hooks()->add_filter('filter_name', 'callback_function', $priority, $accepted_args);

// Apply a filter
$result = hooks()->apply_filters('filter_name', $value, $additional_params);
```

**Key Hook Points**:
- `admin_init` - Admin area initialization
- `clients_init` - Client area initialization
- `before_[entity]_added` - Before entity creation
- `after_[entity]_added` - After entity creation
- `[entity]_updated` - Entity updated
- `staff_permissions` - Permission system filter

### 2. Permission System Integration

**Register Custom Permissions**:
```php
function your_module_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
        'help' => [
            'view' => 'Help text for view permission',
        ],
    ];

    register_staff_capabilities('your_module', $capabilities, _l('your_module'));
}
```

**Check Permissions**:
```php
// In controllers
if (!staff_can('view', 'your_module')) {
    access_denied('Your Module');
}

// Check specific permissions
if (staff_can('create', 'your_module')) {
    // Allow creation
}
```

### 3. Menu System Integration

**Admin Area Menus**:
```php
function your_module_init_admin_menu()
{
    $CI = &get_instance();
    
    // Simple menu item
    $CI->app_menu->add_sidebar_menu_item('your-module', [
        'name'     => _l('your_module'),
        'href'     => admin_url('your_module'),
        'position' => 55,
        'icon'     => 'fa fa-cog',
    ]);
    
    // Collapsible menu with sub-items
    $CI->app_menu->add_sidebar_menu_item('your-module-parent', [
        'name'     => _l('your_module'),
        'collapse' => true,
        'position' => 55,
        'icon'     => 'fa fa-cog',
    ]);
    
    $CI->app_menu->add_sidebar_children_item('your-module-parent', [
        'slug'     => 'your-module-list',
        'name'     => _l('list_items'),
        'href'     => admin_url('your_module'),
        'position' => 1,
    ]);
}
```

**Client Area Menus**:
```php
function your_module_init_client_menu()
{
    // All clients
    add_theme_menu_item('your-module', [
        'name'     => _l('your_module'),
        'href'     => site_url('your_module'),
        'position' => 50,
        'icon'     => 'fa fa-cog',
    ]);
    
    // Only logged-in clients
    if (is_client_logged_in()) {
        add_theme_menu_item('your-module-private', [
            'name'     => _l('private_section'),
            'href'     => site_url('your_module/private'),
            'position' => 51,
        ]);
    }
}
```

### 4. Database Integration

**Table Creation in install.php**:
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

// Check if table exists before creating
if (!$CI->db->table_exists(db_prefix() . 'your_module_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . 'your_module_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(255) NOT NULL,
        `description` text,
        `staff_id` int(11) NOT NULL,
        `client_id` int(11) DEFAULT NULL,
        `status` varchar(50) DEFAULT "active",
        `created_at` datetime NOT NULL,
        `updated_at` datetime DEFAULT NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`),
        KEY `client_id` (`client_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
}

// Add module options
add_option('your_module_enabled', '1');
add_option('your_module_setting', 'default_value');
```

**Model Implementation**:
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Your_module_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function get($id = '', $where = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'your_module_items');
        
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get()->row();
        }
        
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        return $this->db->get()->result_array();
    }
    
    public function add($data)
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['staff_id'] = get_staff_user_id();
        
        $this->db->insert(db_prefix() . 'your_module_items', $data);
        $insert_id = $this->db->insert_id();
        
        if ($insert_id) {
            hooks()->do_action('after_your_module_item_added', $insert_id);
            return $insert_id;
        }
        
        return false;
    }
    
    public function update($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'your_module_items', $data);
        
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('your_module_item_updated', $id, $data);
            return true;
        }
        
        return false;
    }
    
    public function delete($id)
    {
        hooks()->do_action('before_your_module_item_deleted', $id);
        
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'your_module_items');
        
        if ($this->db->affected_rows() > 0) {
            hooks()->do_action('your_module_item_deleted', $id);
            return true;
        }
        
        return false;
    }
}
```

### 5. Controller Implementation

**Admin Controller**:
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Your_module extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('your_module/your_module_model');
    }
    
    public function index()
    {
        if (!staff_can('view', 'your_module')) {
            access_denied('Your Module');
        }
        
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('your_module', 'table'));
        }
        
        $data['title'] = _l('your_module');
        $this->load->view('your_module/manage', $data);
    }
    
    public function item($id = '')
    {
        if ($this->input->post()) {
            if (!staff_can('create', 'your_module') && !staff_can('edit', 'your_module')) {
                access_denied('Your Module');
            }
            
            $data = $this->input->post();
            
            if ($id == '') {
                $id = $this->your_module_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('your_module_item')));
                }
            } else {
                $success = $this->your_module_model->update($id, $data);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('your_module_item')));
                }
            }
            
            redirect(admin_url('your_module'));
        }
        
        if ($id != '') {
            $data['item'] = $this->your_module_model->get($id);
        }
        
        $data['title'] = $id != '' ? _l('edit_your_module_item') : _l('new_your_module_item');
        $this->load->view('your_module/item', $data);
    }
    
    public function delete($id)
    {
        if (!staff_can('delete', 'your_module')) {
            access_denied('Your Module');
        }
        
        $response = $this->your_module_model->delete($id);
        
        if ($response) {
            set_alert('success', _l('deleted', _l('your_module_item')));
        }
        
        redirect(admin_url('your_module'));
    }
}
```

## Development Patterns

### 1. Module Library Pattern
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Your_module_module
{
    private $ci;
    
    public function __construct()
    {
        $this->ci = &get_instance();
        $this->ci->load->model('your_module/your_module_model');
    }
    
    public function process_something($data)
    {
        // Business logic here
        return $this->ci->your_module_model->add($data);
    }
    
    public function get_statistics()
    {
        // Complex calculations
        return [
            'total' => $this->ci->your_module_model->get_total(),
            'active' => $this->ci->your_module_model->get_active_count(),
        ];
    }
}
```

### 2. Dashboard Widget Integration
```php
function your_module_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'your_module/widget',
        'container' => 'right-4', // or 'left-4'
    ];
    
    return $widgets;
}
```

**Widget View** (`views/widget.php`):
```php
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('your_module_widget'); ?>">
    <div class="widget-dragger"></div>
    <div class="tw-flex tw-items-center tw-justify-between tw-p-1.5">
        <p class="tw-font-medium tw-flex tw-items-center tw-mb-0 tw-space-x-1.5 rtl:tw-space-x-reverse">
            <svg>...</svg>
            <span class="tw-text-neutral-700">
                <?php echo _l('your_module_widget'); ?>
            </span>
        </p>
        <div class="tw-space-x-1 rtl:tw-space-x-reverse">
            <a href="<?php echo admin_url('your_module'); ?>" class="tw-text-neutral-500 hover:tw-text-neutral-700">
                <i class="fa-regular fa-circle-question" data-toggle="tooltip" title="<?php echo _l('view_all'); ?>"></i>
            </a>
        </div>
    </div>
    <div class="widget-content">
        <?php
        $CI = &get_instance();
        $CI->load->model('your_module/your_module_model');
        $stats = $CI->your_module_model->get_stats();
        ?>
        <div class="tw-grid tw-grid-cols-2 tw-gap-2 tw-mb-3">
            <div class="tw-text-center">
                <span class="tw-text-lg tw-font-semibold tw-text-neutral-700"><?php echo $stats['total']; ?></span>
                <p class="tw-text-sm tw-text-neutral-500 tw-mb-0"><?php echo _l('total'); ?></p>
            </div>
            <div class="tw-text-center">
                <span class="tw-text-lg tw-font-semibold tw-text-success"><?php echo $stats['active']; ?></span>
                <p class="tw-text-sm tw-text-neutral-500 tw-mb-0"><?php echo _l('active'); ?></p>
            </div>
        </div>
    </div>
</div>
```

### 3. Global Search Integration
```php
function your_module_global_search($result, $q, $limit)
{
    $CI = &get_instance();
    
    if (staff_can('view', 'your_module')) {
        $CI->db->select()->from(db_prefix() . 'your_module_items')
               ->like('name', $q)->or_like('description', $q)
               ->limit($limit);
        
        $result[] = [
            'result'         => $CI->db->get()->result_array(),
            'type'           => 'your_module',
            'search_heading' => _l('your_module'),
        ];
    }
    
    return $result;
}

function your_module_search_output($output, $data)
{
    if ($data['type'] == 'your_module') {
        $output = '<a href="' . admin_url('your_module/item/' . $data['result']['id']) . '">' 
                . $data['result']['name'] . '</a>';
    }
    
    return $output;
}
```

### 4. Cron Job Integration
```php
// Register cron task
register_cron_task('your_module_cron_task');

function your_module_cron_task()
{
    $CI = &get_instance();
    $CI->load->library('your_module/your_module_module');
    
    // Perform scheduled tasks
    $CI->your_module_module->process_scheduled_items();
}

// Add to cron feature counting
hooks()->add_filter('numbers_of_features_using_cron_job', 'your_module_cron_count');
hooks()->add_filter('used_cron_features', 'your_module_cron_features');

function your_module_cron_count($number)
{
    if (get_option('your_module_cron_enabled') == '1') {
        $number++;
    }
    return $number;
}

function your_module_cron_features($features)
{
    if (get_option('your_module_cron_enabled') == '1') {
        $features[] = 'Your Module Automation';
    }
    return $features;
}
```

## Security Guidelines

### 1. File Security
```php
// Add to ALL PHP files
defined('BASEPATH') or exit('No direct script access allowed');
```

### 2. Input Validation
```php
// Use CodeIgniter input class
$data = $this->input->post();
$id = $this->input->get('id');

// Validate data
$this->form_validation->set_rules('name', 'Name', 'required|max_length[255]');
if (!$this->form_validation->run()) {
    // Handle validation errors
}
```

### 3. Database Security
```php
// Use parameterized queries
$this->db->where('id', $id);
$this->db->where('staff_id', get_staff_user_id());

// Always use db_prefix()
$this->db->from(db_prefix() . 'your_table');
```

### 4. Permission Checks
```php
// Always check permissions
if (!staff_can('view', 'your_module')) {
    access_denied('Your Module');
}

// Check ownership for sensitive operations
if (!is_admin() && $item['staff_id'] != get_staff_user_id()) {
    access_denied('Your Module');
}
```

### 5. CSRF Protection
For webhook endpoints, exclude from CSRF in `config/csrf_exclude_uris.php`:
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['csrf_exclude_uris'] = [
    'your_module/webhook',
    'your_module/api/.*'
];
```

## Testing & Deployment

### 1. Development Mode
Enable development mode to see errors and deprecation warnings:
```php
// In application/config/app-config.php
define('ENVIRONMENT', 'development');
```

### 2. Module Validation Checklist
- [ ] All PHP files have security headers
- [ ] Empty index.html files in all directories
- [ ] Proper permission checks in controllers
- [ ] Database queries use db_prefix()
- [ ] Input validation implemented
- [ ] Language files registered
- [ ] Hooks properly registered
- [ ] Installation/uninstallation scripts work
- [ ] No PHP errors or warnings
- [ ] Responsive design for views

### 3. Version Management
```php
// In init file
define('YOUR_MODULE_VERSION', '1.1.0');

// Check for updates in activation hook
function your_module_activation_hook()
{
    $current_version = get_option('your_module_version');
    if ($current_version != YOUR_MODULE_VERSION) {
        // Perform upgrade tasks
        your_module_upgrade($current_version, YOUR_MODULE_VERSION);
        update_option('your_module_version', YOUR_MODULE_VERSION);
    }
}
```

## Advanced Features

### 1. Email Template Integration
```php
// Register merge fields
register_merge_fields('your_module/merge_fields/your_module_merge_fields');

// Create merge fields class
class Your_module_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [
            [
                'name'      => 'Item Name',
                'key'       => '{item_name}',
                'available' => ['your_module'],
            ],
        ];
    }
    
    public function format($data)
    {
        $fields = [];
        
        if (!$data instanceof stdClass) {
            return $fields;
        }
        
        $fields['{item_name}'] = $data->name;
        
        return $fields;
    }
}
```

### 2. Custom Fields Integration
```php
// Add custom fields support
hooks()->add_action('after_custom_fields_select_options', 'your_module_custom_fields');

function your_module_custom_fields()
{
    echo '<option value="your_module">' . _l('your_module') . '</option>';
}
```

### 3. Export Functionality
```php
// Add to exports module
hooks()->add_filter('exports_available_features', 'your_module_export_feature');

function your_module_export_feature($features)
{
    $features['your_module'] = [
        'name' => _l('your_module'),
        'icon' => 'fa fa-cog',
    ];
    
    return $features;
}
```

This comprehensive guide provides everything needed to develop professional PerfexCRM modules. Always refer back to this documentation when implementing new features or troubleshooting issues.