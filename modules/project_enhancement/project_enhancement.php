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
if (function_exists('register_language_files')) {
    register_language_files(PROJECT_ENHANCEMENT_MODULE_NAME, [PROJECT_ENHANCEMENT_MODULE_NAME]);
}

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
    
    try {
        // Include installation script
        if (file_exists(PROJECT_ENHANCEMENT_PATH . 'install.php')) {
            require_once(PROJECT_ENHANCEMENT_PATH . 'install.php');
        } else {
            throw new Exception('Installation file not found');
        }
        
        // Set module version and status
        add_option('project_enhancement_version', PROJECT_ENHANCEMENT_VERSION, 1);
        add_option('project_enhancement_installed', '1', 1);
        add_option('project_enhancement_active', '1', 1);
        
        log_activity('Project Enhancement Module Activated Successfully');
        
    } catch (Exception $e) {
        log_activity('Project Enhancement Module Activation Failed: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Module deactivation hook function
 */
function project_enhancement_deactivation_hook()
{
    try {
        // Update module status
        update_option('project_enhancement_active', '0');
        
        log_activity('Project Enhancement Module Deactivated Successfully');
        
    } catch (Exception $e) {
        log_activity('Project Enhancement Module Deactivation Failed: ' . $e->getMessage());
    }
}

/**
 * Module uninstall hook function
 */
function project_enhancement_uninstall_hook()
{
    $CI = &get_instance();
    
    try {
        // Include uninstallation script
        if (file_exists(PROJECT_ENHANCEMENT_PATH . 'uninstall.php')) {
            require_once(PROJECT_ENHANCEMENT_PATH . 'uninstall.php');
        }
        
        log_activity('Project Enhancement Module Uninstalled Successfully');
        
    } catch (Exception $e) {
        log_activity('Project Enhancement Module Uninstall Failed: ' . $e->getMessage());
    }
}

/**
 * Initialize admin menu items
 */
function project_enhancement_init_admin_menu()
{
    // Check if module is active
    if (!is_project_enhancement_active()) {
        return;
    }
    
    $CI = &get_instance();
    
    // Check if user has permission
    if (!staff_can('view', 'project_enhancement')) {
        return;
    }
    
    // Main menu item with submenu
    $CI->app_menu->add_sidebar_menu_item('project-enhancement', [
        'name'     => _l('project_mgmt_plus'),
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
    // Check if module is active
    if (!is_project_enhancement_active()) {
        return;
    }
    
    // Check if client features are enabled
    if (!get_option('project_enhancement_client_access')) {
        return;
    }
    
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
    // Check if module is active
    if (!is_project_enhancement_active()) {
        return $widgets;
    }
    
    // Check if user has permission
    if (!staff_can('view', 'project_enhancement')) {
        return $widgets;
    }
    
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
    // Check if module is active
    if (!is_project_enhancement_active()) {
        return $result;
    }
    
    $CI = &get_instance();
    
    if (staff_can('view', 'project_enhancement')) {
        try {
            // Search milestones
            $CI->db->select('id, name, description, project_id, due_date')
                   ->from(db_prefix() . 'project_milestones')
                   ->where('name !=', '')
                   ->group_start()
                   ->like('name', $q)
                   ->or_like('description', $q)
                   ->group_end()
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
            $CI->db->select('id, description, project_id, staff_id, date, duration')
                   ->from(db_prefix() . 'time_entries')
                   ->where('description !=', '')
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
        } catch (Exception $e) {
            log_activity('Project Enhancement Global Search Error: ' . $e->getMessage());
        }
    }
    
    return $result;
}

/**
 * Format global search output
 */
function project_enhancement_search_output($output, $data)
{
    try {
        if ($data['type'] == 'project_milestones') {
            $output = '<a href="' . admin_url('project_enhancement/milestones/view/' . $data['result']['id']) . '">' 
                    . htmlspecialchars($data['result']['name']) . '</a>';
        } elseif ($data['type'] == 'time_entries') {
            $output = '<a href="' . admin_url('project_enhancement/time_tracking/view/' . $data['result']['id']) . '">' 
                    . htmlspecialchars($data['result']['description']) . '</a>';
        }
    } catch (Exception $e) {
        log_activity('Project Enhancement Search Output Error: ' . $e->getMessage());
    }
    
    return $output;
}

/**
 * Set up default milestones when a new project is created
 */
function project_enhancement_setup_default_milestones($project_id)
{
    if (!is_project_enhancement_active()) {
        return;
    }
    
    try {
        $CI = &get_instance();
        $CI->load->model('project_enhancement/milestones_model');
        
        if (method_exists($CI->milestones_model, 'create_default_milestones')) {
            $CI->milestones_model->create_default_milestones($project_id);
        }
        
        log_activity('Project Enhancement: Default milestones created for project ' . $project_id);
    } catch (Exception $e) {
        log_activity('Project Enhancement: Failed to create default milestones for project ' . $project_id . ' - ' . $e->getMessage());
    }
}

/**
 * Update milestone status when project status changes
 */
function project_enhancement_update_milestone_status($data)
{
    if (!is_project_enhancement_active()) {
        return;
    }
    
    try {
        $CI = &get_instance();
        $CI->load->model('project_enhancement/milestones_model');
        
        if (method_exists($CI->milestones_model, 'update_milestones_on_project_status_change')) {
            $CI->milestones_model->update_milestones_on_project_status_change($data);
        }
        
        log_activity('Project Enhancement: Milestone status updated for project ' . $data['project_id']);
    } catch (Exception $e) {
        log_activity('Project Enhancement: Failed to update milestone status for project ' . $data['project_id'] . ' - ' . $e->getMessage());
    }
}

/**
 * Update milestone progress when task status changes
 */
function project_enhancement_update_milestone_progress($data)
{
    if (!is_project_enhancement_active()) {
        return;
    }
    
    try {
        $CI = &get_instance();
        $CI->load->model('project_enhancement/milestones_model');
        
        if (method_exists($CI->milestones_model, 'update_milestone_progress_from_tasks')) {
            $CI->milestones_model->update_milestone_progress_from_tasks($data);
        }
        
        log_activity('Project Enhancement: Milestone progress updated from task changes');
    } catch (Exception $e) {
        log_activity('Project Enhancement: Failed to update milestone progress from task changes - ' . $e->getMessage());
    }
}

/**
 * Add time entries to invoice
 */
function project_enhancement_add_time_entries_to_invoice($items)
{
    if (!is_project_enhancement_active()) {
        return $items;
    }
    
    try {
        $CI = &get_instance();
        $CI->load->model('project_enhancement/time_tracking_model');
        
        if (method_exists($CI->time_tracking_model, 'get_billable_time_entries_for_invoice')) {
            $time_entries = $CI->time_tracking_model->get_billable_time_entries_for_invoice();
            // Add time entries to invoice items
            foreach ($time_entries as $entry) {
                $items[] = [
                    'description' => $entry['description'],
                    'qty'         => $entry['hours'],
                    'rate'        => $entry['hourly_rate'],
                    'unit'        => _l('hours'),
                ];
            }
        }
    } catch (Exception $e) {
        log_activity('Project Enhancement: Failed to add time entries to invoice - ' . $e->getMessage());
    }
    
    return $items;
}

/**
 * Mark time entries as invoiced
 */
function project_enhancement_mark_time_entries_invoiced($invoice_id)
{
    if (!is_project_enhancement_active()) {
        return;
    }
    
    try {
        $CI = &get_instance();
        $CI->load->model('project_enhancement/time_tracking_model');
        
        if (method_exists($CI->time_tracking_model, 'mark_time_entries_as_invoiced')) {
            $CI->time_tracking_model->mark_time_entries_as_invoiced($invoice_id);
        }
        
        log_activity('Project Enhancement: Time entries marked as invoiced for invoice ' . $invoice_id);
    } catch (Exception $e) {
        log_activity('Project Enhancement: Failed to mark time entries as invoiced for invoice ' . $invoice_id . ' - ' . $e->getMessage());
    }
}

/**
 * Cron tasks for the module
 */
function project_enhancement_cron_tasks()
{
    if (!is_project_enhancement_active()) {
        return;
    }
    
    try {
        $CI = &get_instance();
        
        // Load module library for cron tasks
        $CI->load->library('project_enhancement/project_enhancement_module');
        
        // Run scheduled tasks
        if (method_exists($CI->project_enhancement_module, 'run_cron_tasks')) {
            $CI->project_enhancement_module->run_cron_tasks();
        }
        
        log_activity('Project Enhancement: Cron tasks executed successfully');
    } catch (Exception $e) {
        log_activity('Project Enhancement: Cron tasks failed - ' . $e->getMessage());
    }
}

/**
 * Helper function to check if module is active
 */
function is_project_enhancement_active()
{
    return get_option('project_enhancement_active') == '1' && get_option('project_enhancement_installed') == '1';
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

/**
 * Helper function to check module requirements
 */
function project_enhancement_check_requirements()
{
    $requirements = [
        'php_version' => version_compare(PHP_VERSION, '7.4.0', '>='),
        'perfexcrm_version' => version_compare(get_option('perfexcrm_version'), '2.3.0', '>='),
        'mysqli_extension' => extension_loaded('mysqli'),
        'curl_extension' => extension_loaded('curl'),
        'json_extension' => extension_loaded('json'),
    ];
    
    return $requirements;
}