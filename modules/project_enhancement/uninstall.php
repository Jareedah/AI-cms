<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Project Enhancement Module Uninstallation Script
 * Removes all database tables and options created by the module
 */

$CI = &get_instance();

try {
    // Start transaction for atomic uninstallation
    $CI->db->trans_start();
    
    // Remove all module options
    $module_options = [
        'project_enhancement_active',
        'project_enhancement_version',
        'project_enhancement_installed',
        'project_enhancement_auto_milestone_creation',
        'project_enhancement_default_milestone_templates',
        'project_enhancement_time_tracking_enabled',
        'project_enhancement_time_approval_required',
        'project_enhancement_budget_tracking_enabled',
        'project_enhancement_resource_management_enabled',
        'project_enhancement_email_notifications',
        'project_enhancement_client_portal_enabled',
        'project_enhancement_dashboard_widgets_enabled',
        'project_enhancement_api_enabled',
        'project_enhancement_cron_enabled',
        'project_enhancement_default_hourly_rate',
        'project_enhancement_currency',
        'project_enhancement_timezone',
        'project_enhancement_working_hours_per_day',
        'project_enhancement_working_days_per_week',
    ];
    
    foreach ($module_options as $option) {
        $CI->db->where('name', $option);
        $CI->db->delete(db_prefix() . 'options');
    }
    
    // Drop tables in reverse order of creation (to handle foreign key constraints)
    $tables_to_drop = [
        'budget_transactions',
        'project_budgets',
        'resource_availability',
        'staff_skills',
        'project_resources',
        'time_categories',
        'time_entries',
        'milestone_approvals',
        'milestone_dependencies',
        'project_milestones',
    ];
    
    foreach ($tables_to_drop as $table) {
        $table_name = db_prefix() . $table;
        if ($CI->db->table_exists($table_name)) {
            // Disable foreign key checks temporarily
            $CI->db->query('SET FOREIGN_KEY_CHECKS = 0');
            $CI->db->query('DROP TABLE IF EXISTS `' . $table_name . '`');
            $CI->db->query('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
    
    // Complete transaction
    $CI->db->trans_complete();
    
    if ($CI->db->trans_status() === FALSE) {
        // Transaction failed
        throw new Exception('Database uninstallation failed during transaction');
    }
    
    // Log successful uninstallation
    log_activity('Project Enhancement Module: All database tables and options removed successfully');
    
} catch (Exception $e) {
    // Log uninstallation error
    log_activity('Project Enhancement Module Uninstallation Error: ' . $e->getMessage());
    
    // Re-throw the exception
    throw $e;
}