# Additional PerfexCRM Module Development Insights

## Important Details from Official Plugin Documentation

### Module Creation Step-by-Step Process

#### Creating Your First Module
1. **Navigate** to PerfexCRM installation directory
2. **Open** the `modules` directory  
3. **Create** new directory named after your module (e.g., `sample_module`)
4. **Create** PHP file with same name as directory (e.g., `sample_module.php`)
5. **Add** module headers in PHP block comment
6. **Enable** development mode to see errors and deprecation warnings

#### Critical Naming Requirements
- **Folder name** and **init file name** must be identical
- Module won't appear in modules list if names don't match
- Always add the "Module Name" header (required)

### Enhanced Module Headers

#### Complete Header Example
```php
<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Sample Perfex CRM Module
Description: Sample module description.
Version: 2.3.0
Requires at least: 2.3.*
Module URI: https://module-website.com
Author: Your Name
Author URI: https://yourwebsite.com
*/
```

#### Available Headers
- **Module Name** (required)
- **Module URI** - Module website URL
- **Version** - Current module version
- **Description** - What the module does
- **Author** - Module author name
- **Author URI** - Author website URL
- **Requires at least** - Minimum PerfexCRM version

### Advanced Database Integration

#### Goals Module Table Example
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'goals')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "goals` (
        `id` int(11) NOT NULL,
        `subject` varchar(191) NOT NULL,
        `description` text NOT NULL,
        `start_date` date NOT NULL,
        `end_date` date NOT NULL,
        `goal_type` int(11) NOT NULL,
        `contract_type` int(11) NOT NULL DEFAULT '0',
        `achievement` int(11) NOT NULL,
        `notify_when_fail` tinyint(1) NOT NULL DEFAULT '1',
        `notify_when_achieve` tinyint(1) NOT NULL DEFAULT '1',
        `notified` int(11) NOT NULL DEFAULT '0',
        `staff_id` int(11) NOT NULL DEFAULT '0'
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
```

### Enhanced Options System

#### Options Best Practices
```php
// Add option with autoload control
add_option($name, $value, $autoload);

// Parameters explained:
// $name - Required string, must be unique (prefix with module name)
// $value - The option value (string)
// $autoload - 1 for frequently used options, 0 for rarely used

// Examples:
add_option('my_module_enabled', '1', 1);     // Autoloaded
add_option('my_module_api_key', 'key', 0);   // Not autoloaded

// Get option
$value = get_option('my_module_enabled');

// Update option (creates if doesn't exist since v2.3.3)
update_option('my_module_enabled', '0');
```

### Payment Gateway Development

#### Complete Gateway Implementation
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Example_gateway extends App_gateway
{
    public function __construct()
    {
        /**
         * Call App_gateway __construct function
         */
        parent::__construct();

        /**
         * Gateway unique id - REQUIRED
         * 
         * The ID must be alphanumeric
         * Filename: Example_gateway.php (first letter uppercase)
         * Class name: Example_gateway (first letter uppercase)
         * ID: "example" (lowercase)
         */
        $this->setId('example');

        /**
         * REQUIRED - Gateway name
         */
        $this->setName('Example');

        /**
         * Gateway settings configuration
         * Available field types:
         * - 'type'=>'yes_no'
         * - 'type'=>'input'  
         * - 'type'=>'textarea'
         */
        $this->setSettings([
            [
                'name' => 'api_secret_key',
                'encrypted' => true,
                'label' => 'API KEY',
                'type' => 'input',
            ],
            [
                'name' => 'api_publishable_key',
                'label' => 'SECRET KEY',
                'type' => 'input'
            ],
            [
                'name' => 'currencies',
                'label' => 'settings_paymentmethod_currencies',
                'default_value' => 'USD,CAD'
            ],
        ]);
    }

    /**
     * Process payment when customer clicks PAY NOW
     * @param array $data Contains total amount and invoice information
     * @return mixed
     */
    public function process_payment($data)
    {
        // Implement payment processing logic
        // Options: show forms, redirect to gateway, redirect to controller
        var_dump($data);
        die;
    }
}
```

#### Gateway Registration
```php
// In module init file
register_payment_gateway('example_gateway', 'module_name');
```

#### CSRF Exclusion for Webhooks
Create `config/csrf_exclude_uris.php` in your module:
```php
<?php
defined('BASEPATH') or exit('No direct script access allowed');

$config['csrf_exclude_uris'] = [
    'module_name/webhook',
    'module_name/gateway_callback'
];
```

### Enhanced Menu Integration

#### Admin Area Menu Examples
```php
hooks()->add_action('admin_init', 'my_module_init_menu_items');

function my_module_init_menu_items()
{
    $CI = &get_instance();

    // Simple menu item
    $CI->app_menu->add_sidebar_menu_item('custom-menu-unique-id', [
        'name'     => 'Custom Menu Item',
        'href'     => 'https://perfexcrm.com/',
        'position' => 10,
        'icon'     => 'fa fa-question-circle',
    ]);
}

// Collapsible menu with sub-items
function my_module_menu_item_collapsible()
{
    $CI = &get_instance();

    $CI->app_menu->add_sidebar_menu_item('custom-menu-unique-id', [
        'name'     => 'Parent Item',
        'collapse' => true,
        'position' => 10,
        'icon'     => 'fa fa-question-circle',
    ]);

    $CI->app_menu->add_sidebar_children_item('custom-menu-unique-id', [
        'slug'     => 'child-to-custom-menu-item',
        'name'     => 'Sub Menu',
        'href'     => 'https://perfexcrm.com/',
        'position' => 5,
        'icon'     => 'fa fa-exclamation',
    ]);
}
```

#### Client Area Menu Examples
```php
hooks()->add_action('clients_init', 'my_module_clients_area_menu_items');

function my_module_clients_area_menu_items()
{   
    // Item for all clients
    add_theme_menu_item('unique-item-id', [
        'name'     => 'Custom Clients Area',
        'href'     => site_url('my_module/acme'),
        'position' => 10,
    ]);

    // Show menu item only if client is logged in
    if (is_client_logged_in()) {
        add_theme_menu_item('unique-logged-in-item-id', [
            'name'     => 'Only Logged In',
            'href'     => site_url('my_module/only_logged_in'),
            'position' => 15,
        ]);
    }
}
```

### Security Implementation Details

#### Input Data Gathering
```php
// Secure data gathering from requests
// GET request
$data = $this->input->get();
$client_id = $this->input->get('client_id');

// POST request  
$data = $this->input->post();
$client_id = $this->input->post('client_id');
```

#### File Security Requirements
1. **PHP Security Header**: Add to ALL PHP files
   ```php
   defined('BASEPATH') or exit('No direct script access allowed');
   ```

2. **Directory Protection**: Add empty `index.html` files in ALL directories
   - `modules/[your-module]/index.html`
   - `modules/[your-module]/views/index.html`
   - `modules/[your-module]/controllers/index.html`
   - etc.

### Hook System Enhancement

#### Hook Registration Examples
```php
// Actions (execute code)
hooks()->add_action($tag, $function_to_add, $priority = 10, $accepted_args = 1);
hooks()->add_filter($tag, $function_to_add, $priority = 10, $accepted_args = 1);

// Trigger hooks
hooks()->do_action($tag, $arg = '');
hooks()->apply_filters($tag, $value, $additionalParams);
```

#### Creating Custom Hooks
You can add your own filters and actions for other modules to interact with your module:
```php
// In your module
hooks()->do_action('my_module_item_created', $item_id);
hooks()->apply_filters('my_module_process_data', $data);
```

### Version Compatibility Notes

#### Breaking Changes Warning
- PerfexCRM is actively developing the modular system
- Expect breaking changes in major updates
- Module developers responsible for maintaining compatibility
- Always test modules with new PerfexCRM versions

#### Support Limitations
- **No Development Support**: PerfexCRM team doesn't provide module development help
- **Bug Reports Only**: Can report actual bugs via https://my.perfexcrm.com
- **Self-Learning Required**: Must explore codebase independently

### CodeCanyon Distribution Rules

#### Exclusivity Agreement
- Can ONLY sell modules on CodeCanyon
- Cannot present PerfexCRM as partner
- Developer owns all copyright
- Responsible for pricing, support, marketing
- Must maintain version compatibility

### Development Environment Requirements

#### Essential Setup
1. **Development Mode**: Enable to see errors and deprecation warnings
2. **CodeIgniter Knowledge**: Essential for effective module development
3. **Security Awareness**: Follow CodeIgniter security practices
4. **Function Prefixing**: Always prefix custom functions to prevent conflicts

This document captures the crucial details that enhance the comprehensive module development knowledge base.