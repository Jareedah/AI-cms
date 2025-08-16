<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Project Management Plus
Description: Enhanced project management features for PerfexCRM
Version: 1.0.0
Requires at least: 2.3.0
Author: Development Team
Author URI: https://example.com
*/

// Module constants
define('PROJECT_MGMT_MODULE_NAME', 'project_mgmt');
define('PROJECT_MGMT_VERSION', '1.0.0');
define('PROJECT_MGMT_PATH', __DIR__ . '/');

/**
 * Register module activation hook
 */
register_activation_hook(PROJECT_MGMT_MODULE_NAME, 'project_mgmt_activation_hook');

function project_mgmt_activation_hook()
{
    // Create database tables if needed
    if (file_exists(PROJECT_MGMT_PATH . 'install.php')) {
        require_once(PROJECT_MGMT_PATH . 'install.php');
    }
    
    // Add module options
    add_option('project_mgmt_version', PROJECT_MGMT_VERSION);
    add_option('project_mgmt_active', '1');
    
    // Log activation
    if (function_exists('log_activity')) {
        log_activity('Project Management Plus Module Activated');
    }
}

/**
 * Register module deactivation hook
 */
register_deactivation_hook(PROJECT_MGMT_MODULE_NAME, 'project_mgmt_deactivation_hook');

function project_mgmt_deactivation_hook()
{
    // Update module status
    update_option('project_mgmt_active', '0');
    
    // Log deactivation
    if (function_exists('log_activity')) {
        log_activity('Project Management Plus Module Deactivated');
    }
}

/**
 * Register module uninstall hook
 */
register_uninstall_hook(PROJECT_MGMT_MODULE_NAME, 'project_mgmt_uninstall_hook');

function project_mgmt_uninstall_hook()
{
    // Remove database tables
    if (file_exists(PROJECT_MGMT_PATH . 'uninstall.php')) {
        require_once(PROJECT_MGMT_PATH . 'uninstall.php');
    }
    
    // Remove module options
    delete_option('project_mgmt_version');
    delete_option('project_mgmt_active');
    
    // Log uninstall
    if (function_exists('log_activity')) {
        log_activity('Project Management Plus Module Uninstalled');
    }
}

/**
 * Check if module is active
 */
function is_project_mgmt_active()
{
    return get_option('project_mgmt_active') == '1';
}

/**
 * Initialize admin menu
 */
hooks()->add_action('admin_init', 'project_mgmt_init_admin_menu');

function project_mgmt_init_admin_menu()
{
    if (!is_project_mgmt_active()) {
        return;
    }
    
    $CI = &get_instance();
    
    if (staff_can('view', 'projects')) {
        $CI->app_menu->add_sidebar_menu_item('project-mgmt-plus', [
            'name'     => _l('project_mgmt_plus'),
            'href'     => admin_url('project_mgmt'),
            'position' => 31,
            'icon'     => 'fa fa-project-diagram',
        ]);
    }
}

/**
 * Register language files
 */
if (function_exists('register_language_files')) {
    register_language_files(PROJECT_MGMT_MODULE_NAME, [PROJECT_MGMT_MODULE_NAME]);
}

/**
 * Register staff capabilities
 */
register_staff_capabilities('project_mgmt', [
    'capabilities' => [
        'view'   => _l('permission_view') . ' (' . _l('project_mgmt_plus') . ')',
        'create' => _l('permission_create') . ' (' . _l('project_mgmt_plus') . ')',
        'edit'   => _l('permission_edit') . ' (' . _l('project_mgmt_plus') . ')',
        'delete' => _l('permission_delete') . ' (' . _l('project_mgmt_plus') . ')',
    ]
], _l('project_mgmt_plus'));