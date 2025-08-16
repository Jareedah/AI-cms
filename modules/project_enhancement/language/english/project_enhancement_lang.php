<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Project Enhancement Module Language File - English
 */

// Module Name
$lang['project_enhancement'] = 'Project Enhancement';
$lang['project_mgmt_plus'] = 'Project Mgmt+';

// Menu Items
$lang['milestones'] = 'Milestones';
$lang['time_tracking'] = 'Time Tracking';
$lang['resource_management'] = 'Resource Management';
$lang['budget_management'] = 'Budget Management';
$lang['project_progress'] = 'Project Progress';

// Permissions
$lang['manage_milestones'] = 'Manage Milestones';
$lang['track_time'] = 'Track Time';
$lang['approve_time'] = 'Approve Time';
$lang['manage_resources'] = 'Manage Resources';
$lang['view_budget'] = 'View Budget';
$lang['manage_budget'] = 'Manage Budget';

// Permission Help Text
$lang['permission_help_view_project_enhancement'] = 'Staff member can view project enhancement features';
$lang['permission_help_manage_milestones'] = 'Staff member can create, edit and manage project milestones';
$lang['permission_help_track_time'] = 'Staff member can track time for projects and tasks';
$lang['permission_help_approve_time'] = 'Staff member can approve time entries from other staff members';
$lang['permission_help_manage_resources'] = 'Staff member can allocate and manage project resources';
$lang['permission_help_view_budget'] = 'Staff member can view project budget information';
$lang['permission_help_manage_budget'] = 'Staff member can create and manage project budgets';

// Milestones
$lang['milestone'] = 'Milestone';
$lang['milestone_name'] = 'Milestone Name';
$lang['milestone_description'] = 'Description';
$lang['milestone_start_date'] = 'Start Date';
$lang['milestone_due_date'] = 'Due Date';
$lang['milestone_status'] = 'Status';
$lang['milestone_priority'] = 'Priority';
$lang['milestone_progress'] = 'Progress';
$lang['milestone_dependencies'] = 'Dependencies';
$lang['milestone_approval'] = 'Approval';

// Milestone Status
$lang['milestone_status_not_started'] = 'Not Started';
$lang['milestone_status_in_progress'] = 'In Progress';
$lang['milestone_status_completed'] = 'Completed';
$lang['milestone_status_on_hold'] = 'On Hold';
$lang['milestone_status_cancelled'] = 'Cancelled';

// Milestone Priority
$lang['milestone_priority_low'] = 'Low';
$lang['milestone_priority_medium'] = 'Medium';
$lang['milestone_priority_high'] = 'High';
$lang['milestone_priority_critical'] = 'Critical';

// Time Tracking
$lang['time_entry'] = 'Time Entry';
$lang['time_entries'] = 'Time Entries';
$lang['start_time'] = 'Start Time';
$lang['end_time'] = 'End Time';
$lang['duration'] = 'Duration';
$lang['duration_minutes'] = 'Duration (Minutes)';
$lang['billable'] = 'Billable';
$lang['non_billable'] = 'Non-Billable';
$lang['hourly_rate'] = 'Hourly Rate';
$lang['time_category'] = 'Category';
$lang['time_description'] = 'Description';
$lang['timer_start'] = 'Start Timer';
$lang['timer_stop'] = 'Stop Timer';
$lang['timer_pause'] = 'Pause Timer';
$lang['timer_resume'] = 'Resume Timer';

// Time Entry Status
$lang['time_status_draft'] = 'Draft';
$lang['time_status_submitted'] = 'Submitted';
$lang['time_status_approved'] = 'Approved';
$lang['time_status_rejected'] = 'Rejected';

// Resource Management
$lang['resource'] = 'Resource';
$lang['resources'] = 'Resources';
$lang['resource_allocation'] = 'Resource Allocation';
$lang['allocation_percentage'] = 'Allocation %';
$lang['resource_role'] = 'Role';
$lang['resource_start_date'] = 'Start Date';
$lang['resource_end_date'] = 'End Date';
$lang['resource_availability'] = 'Availability';
$lang['available_hours'] = 'Available Hours';
$lang['unavailable_reason'] = 'Unavailable Reason';

// Skills
$lang['skill'] = 'Skill';
$lang['skills'] = 'Skills';
$lang['skill_name'] = 'Skill Name';
$lang['proficiency_level'] = 'Proficiency Level';
$lang['skill_beginner'] = 'Beginner';
$lang['skill_intermediate'] = 'Intermediate';
$lang['skill_advanced'] = 'Advanced';
$lang['skill_expert'] = 'Expert';
$lang['verified_by'] = 'Verified By';

// Budget Management
$lang['budget'] = 'Budget';
$lang['budgets'] = 'Budgets';
$lang['budget_category'] = 'Budget Category';
$lang['allocated_amount'] = 'Allocated Amount';
$lang['spent_amount'] = 'Spent Amount';
$lang['remaining_amount'] = 'Remaining Amount';
$lang['budget_transaction'] = 'Budget Transaction';
$lang['transaction_type'] = 'Transaction Type';
$lang['transaction_expense'] = 'Expense';
$lang['transaction_income'] = 'Income';
$lang['transaction_allocation'] = 'Allocation';
$lang['transaction_adjustment'] = 'Adjustment';

// Dashboard Widgets
$lang['widget_project_progress'] = 'Project Progress';
$lang['widget_time_tracking_summary'] = 'Time Tracking Summary';
$lang['widget_budget_status'] = 'Budget Status';
$lang['widget_upcoming_milestones'] = 'Upcoming Milestones';
$lang['widget_resource_utilization'] = 'Resource Utilization';
$lang['widget_project_health'] = 'Project Health';

// Reports
$lang['reports'] = 'Reports';
$lang['report_time_tracking'] = 'Time Tracking Report';
$lang['report_budget_analysis'] = 'Budget Analysis Report';
$lang['report_resource_utilization'] = 'Resource Utilization Report';
$lang['report_project_progress'] = 'Project Progress Report';
$lang['report_milestone_status'] = 'Milestone Status Report';

// General Actions
$lang['add'] = 'Add';
$lang['edit'] = 'Edit';
$lang['delete'] = 'Delete';
$lang['view'] = 'View';
$lang['save'] = 'Save';
$lang['cancel'] = 'Cancel';
$lang['approve'] = 'Approve';
$lang['reject'] = 'Reject';
$lang['submit'] = 'Submit';
$lang['create'] = 'Create';
$lang['update'] = 'Update';
$lang['export'] = 'Export';
$lang['import'] = 'Import';

// Status Messages
$lang['milestone_created_successfully'] = 'Milestone created successfully';
$lang['milestone_updated_successfully'] = 'Milestone updated successfully';
$lang['milestone_deleted_successfully'] = 'Milestone deleted successfully';
$lang['time_entry_created_successfully'] = 'Time entry created successfully';
$lang['time_entry_updated_successfully'] = 'Time entry updated successfully';
$lang['time_entry_deleted_successfully'] = 'Time entry deleted successfully';
$lang['time_entry_approved_successfully'] = 'Time entry approved successfully';
$lang['time_entry_rejected_successfully'] = 'Time entry rejected successfully';
$lang['resource_allocated_successfully'] = 'Resource allocated successfully';
$lang['resource_updated_successfully'] = 'Resource allocation updated successfully';
$lang['budget_created_successfully'] = 'Budget created successfully';
$lang['budget_updated_successfully'] = 'Budget updated successfully';

// Error Messages
$lang['milestone_not_found'] = 'Milestone not found';
$lang['time_entry_not_found'] = 'Time entry not found';
$lang['resource_not_found'] = 'Resource not found';
$lang['budget_not_found'] = 'Budget not found';
$lang['permission_denied'] = 'Permission denied';
$lang['invalid_data_provided'] = 'Invalid data provided';
$lang['operation_failed'] = 'Operation failed';

// Validation Messages
$lang['milestone_name_required'] = 'Milestone name is required';
$lang['milestone_start_date_required'] = 'Start date is required';
$lang['milestone_due_date_required'] = 'Due date is required';
$lang['time_start_time_required'] = 'Start time is required';
$lang['time_description_required'] = 'Time description is required';
$lang['resource_role_required'] = 'Resource role is required';
$lang['budget_category_required'] = 'Budget category is required';
$lang['allocated_amount_required'] = 'Allocated amount is required';

// Confirmation Messages
$lang['confirm_delete_milestone'] = 'Are you sure you want to delete this milestone?';
$lang['confirm_delete_time_entry'] = 'Are you sure you want to delete this time entry?';
$lang['confirm_delete_resource'] = 'Are you sure you want to remove this resource allocation?';
$lang['confirm_delete_budget'] = 'Are you sure you want to delete this budget?';
$lang['confirm_approve_time_entry'] = 'Are you sure you want to approve this time entry?';
$lang['confirm_reject_time_entry'] = 'Are you sure you want to reject this time entry?';

// Email Templates
$lang['email_milestone_created_subject'] = 'New Milestone Created: %s';
$lang['email_milestone_due_reminder_subject'] = 'Milestone Due Reminder: %s';
$lang['email_time_entry_approval_subject'] = 'Time Entry Approval Required';
$lang['email_budget_alert_subject'] = 'Budget Alert: %s';

// Client Portal
$lang['client_milestone_progress'] = 'Milestone Progress';
$lang['client_project_timeline'] = 'Project Timeline';
$lang['client_budget_overview'] = 'Budget Overview';

// Settings
$lang['settings'] = 'Settings';
$lang['module_settings'] = 'Module Settings';
$lang['auto_milestone_creation'] = 'Auto Milestone Creation';
$lang['time_approval_required'] = 'Time Approval Required';
$lang['email_notifications_enabled'] = 'Email Notifications';
$lang['client_portal_enabled'] = 'Client Portal Access';
$lang['dashboard_widgets_enabled'] = 'Dashboard Widgets';
$lang['default_hourly_rate'] = 'Default Hourly Rate';
$lang['working_hours_per_day'] = 'Working Hours Per Day';
$lang['working_days_per_week'] = 'Working Days Per Week';

// Tooltips and Help
$lang['tooltip_milestone_dependencies'] = 'Select milestones that must be completed before this milestone can start';
$lang['tooltip_allocation_percentage'] = 'Percentage of time this resource is allocated to the project (0-100%)';
$lang['tooltip_billable_time'] = 'Check if this time should be billed to the client';
$lang['tooltip_budget_category'] = 'Category helps organize budget items for better tracking';

// Charts and Analytics
$lang['chart_milestone_progress'] = 'Milestone Progress';
$lang['chart_time_distribution'] = 'Time Distribution';
$lang['chart_budget_utilization'] = 'Budget Utilization';
$lang['chart_resource_workload'] = 'Resource Workload';
$lang['analytics_dashboard'] = 'Analytics Dashboard';

// API Messages
$lang['api_invalid_request'] = 'Invalid API request';
$lang['api_unauthorized'] = 'Unauthorized access';
$lang['api_resource_not_found'] = 'Resource not found';
$lang['api_validation_failed'] = 'Validation failed';
$lang['api_internal_error'] = 'Internal server error';

// Export/Import
$lang['export_milestones'] = 'Export Milestones';
$lang['export_time_entries'] = 'Export Time Entries';
$lang['export_budget_report'] = 'Export Budget Report';
$lang['import_milestones'] = 'Import Milestones';
$lang['import_time_entries'] = 'Import Time Entries';
$lang['export_format'] = 'Export Format';
$lang['csv_format'] = 'CSV Format';
$lang['excel_format'] = 'Excel Format';
$lang['pdf_format'] = 'PDF Format';