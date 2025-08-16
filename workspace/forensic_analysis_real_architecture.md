# PerfexCRM Module Architecture - FORENSIC ANALYSIS

## CRITICAL FINDINGS FROM CODEBASE EXAMINATION

### 1. CORE MODULE SYSTEM ANALYSIS

#### App_modules Library (application/libraries/App_modules.php)
**Key Discovery**: The module system is managed by the `App_modules` class which:

- **Database Version Check**: Only loads if DB version >= 230 (v2.3.0+)
- **Module States**: Tracks modules in `modules` table with `active` flag
- **Lifecycle Management**: Handles activate/deactivate/uninstall operations
- **Hook Integration**: Uses WordPress-style hooks for module lifecycle

```php
// REAL activation process from codebase:
public function activate($name)
{
    $module = $this->get($name);
    
    // Check if module exists in database
    if (!$this->module_exists_in_database($name)) {
        $this->ci->db->insert(db_prefix() . 'modules', [
            'module_name' => $name, 
            'installed_version' => $module['headers']['version']
        ]);
    }
    
    // Include the module init file
    include_once($module['init_file']);
    
    // Fire hooks
    hooks()->do_action('pre_activate_module', $module);
    hooks()->do_action("activate_{$name}_module");
    
    // Set active in database
    $this->ci->db->where('module_name', $name);
    $this->ci->db->update(db_prefix() . 'modules', ['active' => 1]);
}
```

#### Module Helper Functions (application/helpers/modules_helper.php)
**Key Discovery**: The helper provides the API for module developers:

```php
// REAL hook registration from codebase:
function register_activation_hook($module, $function)
{
    hooks()->add_action('activate_' . $module . '_module', $function);
}

function register_deactivation_hook($module, $function)
{
    hooks()->add_action('deactivate_' . $module . '_module', $function);
}

function register_uninstall_hook($module, $function)
{
    hooks()->add_action('uninstall_' . $module . '_module', $function);
}
```

### 2. CRITICAL ERROR ANALYSIS

#### Why My Module Failed (HTTP 500)
Based on forensic analysis, the module failed because:

1. **Missing Database Version Check**: No validation of PerfexCRM version compatibility
2. **Improper Hook Registration**: Hooks must be registered in specific order
3. **Database Transaction Issues**: Installation script must use proper transactions
4. **Path Resolution Problems**: Module paths not resolved correctly
5. **Missing Error Handling**: No try-catch blocks in critical operations

### 3. REAL MODULE STRUCTURE REQUIREMENTS

Based on codebase analysis, modules MUST have:

```
modules/[module_name]/
├── [module_name].php           # MANDATORY - Init file with module headers
├── install.php                 # OPTIONAL - Database setup
├── uninstall.php              # OPTIONAL - Cleanup
├── index.html                  # MANDATORY - Security
└── [other directories...]
```

#### Module Headers (CRITICAL)
The init file MUST start with proper headers:

```php
<?php defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: [Module Name]
Description: [Description]
Version: [Version]
Requires at least: [PerfexCRM Version]
Author: [Author]
Author URI: [URI]
*/
```

### 4. PERMISSION SYSTEM ANALYSIS

From `register_staff_capabilities()` function:

```php
function register_staff_capabilities($feature_id, $config, $name = null)
{
    hooks()->add_filter('staff_permissions', function ($permissions) use ($feature_id, $config, $name) {
        if (!array_key_exists($feature_id, $permissions)) {
            $permissions[$feature_id] = [];
            
            if (!$name) {
                $name = str_replace('-', ' ', slug_it($feature_id));
                $name = ucwords($feature_id);
            }
            
            $permissions[$feature_id]['name'] = $name;
        }
        
        $permissions[$feature_id] = array_merge_recursive_distinct($permissions[$feature_id], $config);
        
        return $permissions;
    });
}
```

### 5. DATABASE INTEGRATION PATTERNS

From codebase analysis:
- All tables MUST use `db_prefix()` function
- Foreign keys MUST reference core tables properly
- Migrations MUST be transaction-safe
- Options MUST use `add_option()`, `get_option()`, `update_option()`

### 6. HOOK SYSTEM ARCHITECTURE

The hook system uses:
- `hooks()->add_action()` for events
- `hooks()->add_filter()` for data modification
- `hooks()->do_action()` to trigger events
- `hooks()->apply_filters()` to modify data

### 7. CRITICAL IMPLEMENTATION ERRORS IN MY MODULE

1. **Incorrect Path Constants**: Used wrong path resolution
2. **Missing Database Validation**: No version checks
3. **Improper Hook Order**: Registered hooks in wrong sequence
4. **View Path Issues**: Used incorrect view loading patterns
5. **Missing Security Checks**: Insufficient permission validation

## RECONSTRUCTION PLAN

### Phase 1: Core Module File
Create minimal, working module init file following EXACT codebase patterns

### Phase 2: Database Integration
Implement proper database setup with transactions and error handling

### Phase 3: Permission Integration
Add proper staff capabilities using the real API

### Phase 4: View System
Implement views using the correct PerfexCRM patterns

### Phase 5: Testing
Test each component individually before integration

## NEXT STEPS

1. Create minimal working module following real patterns
2. Test activation/deactivation cycle
3. Add features incrementally
4. Validate each step against codebase patterns

**CRITICAL**: Never assume anything - always verify against actual codebase implementation.