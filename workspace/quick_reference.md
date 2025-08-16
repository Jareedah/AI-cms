# PerfexCRM Module Development - Quick Reference

## Essential Functions

### Module System Functions
```php
// Module lifecycle
register_activation_hook($module, $function);
register_deactivation_hook($module, $function);
register_uninstall_hook($module, $function);

// Module paths
module_dir_path($module, $concat = '');
module_dir_url($module, $segment = '');
module_views_path($module, $concat = '');

// Module support
add_module_support($module_name, $feature);
module_supports($module_name, $feature);
```

### Hook System
```php
// Actions (execute code)
hooks()->add_action($tag, $function, $priority = 10, $accepted_args = 1);
hooks()->do_action($tag, $arg = '');

// Filters (modify data)
hooks()->add_filter($tag, $function, $priority = 10, $accepted_args = 1);
hooks()->apply_filters($tag, $value, $additionalParams);
```

### Permission System
```php
// Register permissions
register_staff_capabilities($feature_id, $config, $name = null);

// Check permissions
staff_can($capability, $feature);
is_admin();
access_denied($module_name);
```

### Database Functions
```php
// Table prefix
db_prefix(); // Returns 'tbl' by default

// Options system
add_option($name, $value, $autoload = 1);
get_option($option_name);
update_option($option_name, $new_value);
```

### Menu System
```php
// Admin menus
$CI->app_menu->add_sidebar_menu_item($id, $config);
$CI->app_menu->add_sidebar_children_item($parent_id, $config);

// Client menus
add_theme_menu_item($slug, $item);
```

### Language System
```php
// Register language files
register_language_files($module, $languages);

// Use translations
_l('translation_key');
```

## Common Hook Points

### Module Lifecycle
- `activate_{module}_module` - Module activation
- `deactivate_{module}_module` - Module deactivation
- `uninstall_{module}_module` - Module uninstall

### System Initialization
- `admin_init` - Admin area initialization
- `clients_init` - Client area initialization
- `pre_admin_init` - Before admin init
- `app_admin_head` - Admin head section
- `app_admin_footer` - Admin footer section

### Entity Operations
- `before_{entity}_added` - Before creation
- `after_{entity}_added` - After creation
- `{entity}_updated` - After update
- `before_{entity}_deleted` - Before deletion
- `{entity}_deleted` - After deletion

### System Events
- `after_cron_run` - After cron execution
- `staff_member_deleted` - Staff member deleted
- `client_created` - Client created

### Filters
- `staff_permissions` - Modify staff permissions
- `get_dashboard_widgets` - Add dashboard widgets
- `global_search_result_query` - Add to global search
- `global_search_result_output` - Format search results

## Menu Positions Reference

### Admin Menu Default Positions
- Dashboard: 1
- Customers: 5
- Sales: 10
- Subscriptions: 15
- Expenses: 20
- Contracts: 25
- Projects: 30
- Tasks: 35
- Tickets: 40
- Leads: 45
- Knowledge Base: 50
- Utilities: 55
- Reports: 60

### Client Menu Default Positions
- Knowledge Base: 5
- Projects: 10
- Invoices: 15
- Contracts: 20
- Estimates: 25
- Proposals: 30
- Subscriptions: 40
- Support: 45
- Register: 99
- Login: 100

## Database Schema Patterns

### Standard Table Structure
```sql
CREATE TABLE `{prefix}module_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `staff_id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `staff_id` (`staff_id`),
  KEY `client_id` (`client_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### Common Field Types
- `id` - Primary key (auto increment)
- `staff_id` - Staff member reference
- `client_id` - Client reference
- `status` - Entity status
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp
- `sort_order` - Manual sorting
- `active` - Boolean flag (tinyint(1))

## Controller Patterns

### Basic CRUD Controller
```php
class Module_name extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('module_name/module_name_model');
    }
    
    // List view
    public function index() { /* ... */ }
    
    // Create/Edit form
    public function item($id = '') { /* ... */ }
    
    // Delete action
    public function delete($id) { /* ... */ }
    
    // AJAX table data
    public function table() { /* ... */ }
}
```

### Permission Check Patterns
```php
// View permission
if (!staff_can('view', 'module_name')) {
    access_denied('Module Name');
}

// Create/Edit permission
if (!staff_can('create', 'module_name') && !staff_can('edit', 'module_name')) {
    access_denied('Module Name');
}

// Admin only
if (!is_admin()) {
    access_denied('Module Name');
}
```

## Model Patterns

### Standard CRUD Operations
```php
class Module_name_model extends App_Model
{
    public function get($id = '', $where = []) { /* ... */ }
    public function add($data) { /* ... */ }
    public function update($id, $data) { /* ... */ }
    public function delete($id) { /* ... */ }
    public function get_total($where = []) { /* ... */ }
}
```

### Query Patterns
```php
// Basic select
$this->db->select('*');
$this->db->from(db_prefix() . 'table_name');
$this->db->where('active', 1);
return $this->db->get()->result_array();

// With joins
$this->db->select('t1.*, t2.name as related_name');
$this->db->from(db_prefix() . 'table1 t1');
$this->db->join(db_prefix() . 'table2 t2', 't1.related_id = t2.id', 'left');

// With pagination
$this->db->limit($limit, $offset);
```

## View Patterns

### Standard List View Structure
```php
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Content here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
```

### Form Patterns
```php
<?php echo form_open(admin_url('module_name/item' . (isset($item) ? '/' . $item->id : ''))); ?>
<div class="form-group">
    <label for="name"><?php echo _l('name'); ?></label>
    <input type="text" class="form-control" name="name" 
           value="<?php echo isset($item) ? $item->name : ''; ?>" required>
</div>
<button type="submit" class="btn btn-info pull-right">
    <?php echo _l('submit'); ?>
</button>
<?php echo form_close(); ?>
```

## Security Checklist

### File Security
- [ ] `defined('BASEPATH') or exit('No direct script access allowed');`
- [ ] Empty `index.html` in all directories
- [ ] Proper file permissions

### Input Security
- [ ] Use `$this->input->post()` and `$this->input->get()`
- [ ] Validate all inputs
- [ ] Sanitize output with `htmlspecialchars()`
- [ ] Use form validation library

### Database Security
- [ ] Use Active Record (automatic escaping)
- [ ] Use `db_prefix()` for table names
- [ ] Validate data types
- [ ] Check record ownership

### Permission Security
- [ ] Check permissions in all controller methods
- [ ] Verify user can access specific records
- [ ] Use `access_denied()` for unauthorized access
- [ ] Implement proper role-based access

## Common Error Solutions

### Module Not Appearing
1. Check folder name matches init file name
2. Verify module headers format
3. Check for PHP syntax errors
4. Ensure proper file permissions

### Permission Errors
1. Verify permission registration
2. Check permission names match
3. Ensure user has required role
4. Clear any caches

### Database Errors
1. Check table prefix usage
2. Verify table exists
3. Check column names
4. Validate data types

### Menu Issues
1. Verify hook registration
2. Check menu position conflicts
3. Ensure proper permissions
4. Clear menu cache

This quick reference should cover 90% of common module development needs!