<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Project Management Enhancement
Description: Advanced project management features including milestone tracking, time tracking, resource management, and budget control with comprehensive reporting and client portal integration.
Version: 1.0.0
Requires at least: 2.3.*
Author: Development Team
Author URI: https://github.com/project-enhancement
Module URI: https://github.com/project-enhancement/perfexcrm-project-enhancement
*/

// Module constants
define('PROJECT_ENHANCEMENT_MODULE_NAME', 'project_enhancement');
define('PROJECT_ENHANCEMENT_VERSION', '1.0.0');
define('PROJECT_ENHANCEMENT_PATH', __DIR__ . '/');

// Load Composer dependencies if available
if (file_exists(PROJECT_ENHANCEMENT_PATH . 'vendor/autoload.php')) {
    require_once(PROJECT_ENHANCEMENT_PATH . 'vendor/autoload.php');
}

/**
 * Register module activation hook
 */
register_activation_hook(PROJECT_ENHANCEMENT_MODULE_NAME, 'project_enhancement_activation_hook');

/**
 * Register module deactivation hook
 */
register_deactivation_hook(PROJECT_ENHANCEMENT_MODULE_NAME, 'project_enhancement_deactivation_hook');

/**
 * Register module uninstall hook
 */
register_uninstall_hook(PROJECT_ENHANCEMENT_MODULE_NAME, 'project_enhancement_uninstall_hook');

/**
 * Register language files
 */
register_language_files(PROJECT_ENHANCEMENT_MODULE_NAME, [PROJECT_ENHANCEMENT_MODULE_NAME]);

/**
 * Admin area initialization
 */
hooks()->add_action('admin_init', 'project_enhancement_init_admin_menu');
hooks()->add_action('admin_init', 'project_enhancement_permissions');

/**
 * Client area initialization
 */
hooks()->add_action('clients_init', 'project_enhancement_init_client_menu');

/**
 * Dashboard widgets integration
 */
hooks()->add_filter('get_dashboard_widgets', 'project_enhancement_dashboard_widgets');

/**
 * Global search integration
 */
hooks()->add_filter('global_search_result_query', 'project_enhancement_global_search', 10, 3);
hooks()->add_filter('global_search_result_output', 'project_enhancement_search_output', 10, 2);

/**
 * Project lifecycle hooks
 */
hooks()->add_action('after_project_added', 'project_enhancement_setup_default_milestones');
hooks()->add_action('project_status_changed', 'project_enhancement_update_milestone_status');
hooks()->add_action('task_status_changed', 'project_enhancement_update_milestone_progress');

/**
 * Invoice integration hooks
 */
hooks()->add_filter('invoice_items_data', 'project_enhancement_add_time_entries_to_invoice');
hooks()->add_action('after_invoice_added', 'project_enhancement_mark_time_entries_invoiced');

/**
 * Cron job integration
 */
register_cron_task('project_enhancement_cron_tasks');

/**
 * Module activation hook function
 */
function project_enhancement_activation_hook()
{
    $CI = &get_instance();
    
    // Include installation script
    require_once(PROJECT_ENHANCEMENT_PATH . 'install.php');
    
    // Set module version
    add_option('project_enhancement_version', PROJECT_ENHANCEMENT_VERSION);
    add_option('project_enhancement_installed', '1');
    
    log_activity('Project Enhancement Module Activated');
}

/**
 * Module deactivation hook function
 */
function project_enhancement_deactivation_hook()
{
    // Update module status
    update_option('project_enhancement_active', '0');
    
    log_activity('Project Enhancement Module Deactivated');
}

/**
 * Module uninstall hook function
 */
function project_enhancement_uninstall_hook()
{
    $CI = &get_instance();
    
    // Include uninstallation script
    if (file_exists(PROJECT_ENHANCEMENT_PATH . 'uninstall.php')) {
        require_once(PROJECT_ENHANCEMENT_PATH . 'uninstall.php');
    }
    
    log_activity('Project Enhancement Module Uninstalled');
}

/**
 * Initialize admin menu items
 */
function project_enhancement_init_admin_menu()
{
    $CI = &get_instance();
    
    // Main menu item with submenu
    $CI->app_menu->add_sidebar_menu_item('project-enhancement', [
        'name'     => _l('project_enhancement'),
        'collapse' => true,
        'position' => 31, // After Projects (30)
        'icon'     => 'fa fa-project-diagram',
    ]);
    
    // Milestones submenu
    $CI->app_menu->add_sidebar_children_item('project-enhancement', [
        'slug'     => 'milestones',
        'name'     => _l('milestones'),
        'href'     => admin_url('project_enhancement/milestones'),
        'position' => 1,
        'icon'     => 'fa fa-flag-checkered',
    ]);
    
    // Time Tracking submenu
    $CI->app_menu->add_sidebar_children_item('project-enhancement', [
        'slug'     => 'time-tracking',
        'name'     => _l('time_tracking'),
        'href'     => admin_url('project_enhancement/time_tracking'),
        'position' => 2,
        'icon'     => 'fa fa-clock',
    ]);
    
    // Resource Management submenu
    $CI->app_menu->add_sidebar_children_item('project-enhancement', [
        'slug'     => 'resources',
        'name'     => _l('resource_management'),
        'href'     => admin_url('project_enhancement/resources'),
        'position' => 3,
        'icon'     => 'fa fa-users',
    ]);
    
    // Budget Management submenu
    $CI->app_menu->add_sidebar_children_item('project-enhancement', [
        'slug'     => 'budget',
        'name'     => _l('budget_management'),
        'href'     => admin_url('project_enhancement/budget'),
        'position' => 4,
        'icon'     => 'fa fa-dollar-sign',
    ]);
    
    // Reports submenu
    $CI->app_menu->add_sidebar_children_item('project-enhancement', [
        'slug'     => 'reports',
        'name'     => _l('reports'),
        'href'     => admin_url('project_enhancement/reports'),
        'position' => 5,
        'icon'     => 'fa fa-chart-bar',
    ]);
}

/**
 * Initialize client area menu items
 */
function project_enhancement_init_client_menu()
{
    // Project Progress menu for clients
    add_theme_menu_item('project-progress', [
        'name'     => _l('project_progress'),
        'href'     => site_url('project_enhancement/progress'),
        'position' => 12, // After Projects (10)
        'icon'     => 'fa fa-tasks',
    ]);
    
    // Only show for logged-in clients
    if (is_client_logged_in()) {
        add_theme_menu_item('project-milestones', [
            'name'     => _l('milestones'),
            'href'     => site_url('project_enhancement/milestones'),
            'position' => 13,
            'icon'     => 'fa fa-flag',
        ]);
    }
}

/**
 * Register staff permissions
 */
function project_enhancement_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view'              => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create'            => _l('permission_create'),
            'edit'              => _l('permission_edit'),
            'delete'            => _l('permission_delete'),
            'manage_milestones' => _l('manage_milestones'),
            'track_time'        => _l('track_time'),
            'approve_time'      => _l('approve_time'),
            'manage_resources'  => _l('manage_resources'),
            'view_budget'       => _l('view_budget'),
            'manage_budget'     => _l('manage_budget'),
        ],
        'help' => [
            'view'              => _l('permission_help_view_project_enhancement'),
            'manage_milestones' => _l('permission_help_manage_milestones'),
            'track_time'        => _l('permission_help_track_time'),
            'approve_time'      => _l('permission_help_approve_time'),
            'manage_resources'  => _l('permission_help_manage_resources'),
            'view_budget'       => _l('permission_help_view_budget'),
            'manage_budget'     => _l('permission_help_manage_budget'),
        ],
    ];
    
    register_staff_capabilities('project_enhancement', $capabilities, _l('project_enhancement'));
}

/**
 * Add dashboard widgets
 */
function project_enhancement_dashboard_widgets($widgets)
{
    // Project Progress Widget
    $widgets[] = [
        'path'      => 'project_enhancement/widgets/project_progress',
        'container' => 'left-4',
    ];
    
    // Time Tracking Summary Widget
    $widgets[] = [
        'path'      => 'project_enhancement/widgets/time_tracking_summary',
        'container' => 'right-4',
    ];
    
    // Budget Status Widget
    $widgets[] = [
        'path'      => 'project_enhancement/widgets/budget_status',
        'container' => 'left-4',
    ];
    
    // Upcoming Milestones Widget
    $widgets[] = [
        'path'      => 'project_enhancement/widgets/upcoming_milestones',
        'container' => 'right-4',
    ];
    
    return $widgets;
}

/**
 * Global search integration
 */
function project_enhancement_global_search($result, $q, $limit)
{
    $CI = &get_instance();
    
    if (staff_can('view', 'project_enhancement')) {
        // Search milestones
        $CI->db->select('id, name, description, project_id, due_date')
               ->from(db_prefix() . 'project_milestones')
               ->like('name', $q)
               ->or_like('description', $q)
               ->limit($limit);
        
        $milestones = $CI->db->get()->result_array();
        
        if (!empty($milestones)) {
            $result[] = [
                'result'         => $milestones,
                'type'           => 'project_milestones',
                'search_heading' => _l('milestones'),
            ];
        }
        
        // Search time entries
        $CI->db->select('id, description, project_id, staff_id, start_time, duration_minutes')
               ->from(db_prefix() . 'time_entries')
               ->like('description', $q)
               ->limit($limit);
        
        $time_entries = $CI->db->get()->result_array();
        
        if (!empty($time_entries)) {
            $result[] = [
                'result'         => $time_entries,
                'type'           => 'time_entries',
                'search_heading' => _l('time_entries'),
            ];
        }
    }
    
    return $result;
}

/**
 * Format global search output
 */
function project_enhancement_search_output($output, $data)
{
    if ($data['type'] == 'project_milestones') {
        $output = '<a href="' . admin_url('project_enhancement/milestones/view/' . $data['result']['id']) . '">' 
                . $data['result']['name'] . '</a>';
    } elseif ($data['type'] == 'time_entries') {
        $output = '<a href="' . admin_url('project_enhancement/time_tracking/view/' . $data['result']['id']) . '">' 
                . $data['result']['description'] . '</a>';
    }
    
    return $output;
}

/**
 * Set up default milestones when a new project is created
 */
function project_enhancement_setup_default_milestones($project_id)
{
    // This will be implemented in the models phase
    // For now, just log the action
    log_activity('Project Enhancement: Default milestones setup triggered for project ' . $project_id);
}

/**
 * Update milestone status when project status changes
 */
function project_enhancement_update_milestone_status($data)
{
    // This will be implemented in the models phase
    log_activity('Project Enhancement: Milestone status update triggered for project ' . $data['project_id']);
}

/**
 * Update milestone progress when task status changes
 */
function project_enhancement_update_milestone_progress($data)
{
    // This will be implemented in the models phase
    log_activity('Project Enhancement: Milestone progress update triggered');
}

/**
 * Add time entries to invoice
 */
function project_enhancement_add_time_entries_to_invoice($items)
{
    // This will be implemented in the models phase
    return $items;
}

/**
 * Mark time entries as invoiced
 */
function project_enhancement_mark_time_entries_invoiced($invoice_id)
{
    // This will be implemented in the models phase
    log_activity('Project Enhancement: Time entries marked as invoiced for invoice ' . $invoice_id);
}

/**
 * Cron tasks for the module
 */
function project_enhancement_cron_tasks()
{
    $CI = &get_instance();
    
    // Load module library for cron tasks
    $CI->load->library('project_enhancement/project_enhancement_module');
    
    // Run scheduled tasks
    if (method_exists($CI->project_enhancement_module, 'run_cron_tasks')) {
        $CI->project_enhancement_module->run_cron_tasks();
    }
}

/**
 * Helper function to check if module is active
 */
function is_project_enhancement_active()
{
    return get_option('project_enhancement_active') == '1';
}

/**
 * Helper function to get module version
 */
function get_project_enhancement_version()
{
    return get_option('project_enhancement_version', PROJECT_ENHANCEMENT_VERSION);
}

/**
 * Helper function to log module activities
 */
function project_enhancement_log($message, $data = [])
{
    if (is_array($data) && !empty($data)) {
        $message .= ' - Data: ' . json_encode($data);
    }
    
    log_activity('[Project Enhancement] ' . $message);
}