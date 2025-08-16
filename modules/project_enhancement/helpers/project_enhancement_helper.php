<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Project Enhancement Helper Functions
 * Common utility functions for the Project Management Enhancement module
 */

if (!function_exists('milestone_status_color')) {
    /**
     * Get the appropriate color class for milestone status
     *
     * @param string $status
     * @return string
     */
    function milestone_status_color($status)
    {
        switch ($status) {
            case 'not_started':
                return 'default';
            case 'in_progress':
                return 'info';
            case 'completed':
                return 'success';
            case 'on_hold':
                return 'warning';
            case 'cancelled':
                return 'danger';
            default:
                return 'default';
        }
    }
}

if (!function_exists('priority_color')) {
    /**
     * Get the appropriate color class for priority level
     *
     * @param string $priority
     * @return string
     */
    function priority_color($priority)
    {
        switch ($priority) {
            case 'low':
                return 'success';
            case 'medium':
                return 'info';
            case 'high':
                return 'warning';
            case 'critical':
                return 'danger';
            default:
                return 'default';
        }
    }
}

if (!function_exists('seconds_to_time_format')) {
    /**
     * Convert seconds to human readable time format
     *
     * @param int $seconds
     * @param string $format
     * @return string
     */
    function seconds_to_time_format($seconds, $format = 'short')
    {
        if (!is_numeric($seconds) || $seconds < 0) {
            return '0:00';
        }

        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remaining_seconds = $seconds % 60;

        if ($format === 'full') {
            if ($hours > 0) {
                return sprintf('%d:%02d:%02d', $hours, $minutes, $remaining_seconds);
            } else {
                return sprintf('%d:%02d', $minutes, $remaining_seconds);
            }
        } else {
            // Short format (default)
            if ($hours > 0) {
                return sprintf('%d:%02dh', $hours, $minutes);
            } else {
                return sprintf('%dm', $minutes);
            }
        }
    }
}

if (!function_exists('time_entry_status_color')) {
    /**
     * Get the appropriate color class for time entry status
     *
     * @param string $status
     * @return string
     */
    function time_entry_status_color($status)
    {
        switch ($status) {
            case 'draft':
                return 'default';
            case 'submitted':
                return 'info';
            case 'approved':
                return 'success';
            case 'rejected':
                return 'danger';
            default:
                return 'default';
        }
    }
}

if (!function_exists('budget_status_color')) {
    /**
     * Get the appropriate color class for budget status based on utilization
     *
     * @param float $utilization_percentage
     * @return string
     */
    function budget_status_color($utilization_percentage)
    {
        if ($utilization_percentage >= 100) {
            return 'danger';
        } elseif ($utilization_percentage >= 90) {
            return 'warning';
        } elseif ($utilization_percentage >= 75) {
            return 'info';
        } else {
            return 'success';
        }
    }
}

if (!function_exists('format_project_enhancement_currency')) {
    /**
     * Format currency for project enhancement module
     *
     * @param float $amount
     * @param string $currency
     * @return string
     */
    function format_project_enhancement_currency($amount, $currency = null)
    {
        if ($currency === null) {
            $currency = get_option('project_enhancement_currency', 'USD');
        }

        // Use PerfexCRM's built-in currency formatting if available
        if (function_exists('app_format_money')) {
            return app_format_money($amount, $currency);
        }

        // Fallback formatting
        return $currency . ' ' . number_format($amount, 2);
    }
}

if (!function_exists('get_project_enhancement_date_format')) {
    /**
     * Get the date format for the project enhancement module
     *
     * @return string
     */
    function get_project_enhancement_date_format()
    {
        return get_option('dateformat', 'Y-m-d');
    }
}

if (!function_exists('format_project_enhancement_date')) {
    /**
     * Format date for project enhancement module
     *
     * @param string $date
     * @param string $format
     * @return string
     */
    function format_project_enhancement_date($date, $format = null)
    {
        if (empty($date)) {
            return '';
        }

        if ($format === null) {
            $format = get_project_enhancement_date_format();
        }

        // Use PerfexCRM's built-in date formatting if available
        if (function_exists('_d')) {
            return _d($date);
        }

        // Fallback formatting
        return date($format, strtotime($date));
    }
}

if (!function_exists('calculate_working_hours')) {
    /**
     * Calculate working hours between two dates
     *
     * @param string $start_date
     * @param string $end_date
     * @param float $hours_per_day
     * @param int $working_days_per_week
     * @return float
     */
    function calculate_working_hours($start_date, $end_date, $hours_per_day = null, $working_days_per_week = null)
    {
        if ($hours_per_day === null) {
            $hours_per_day = (float) get_option('project_enhancement_working_hours_per_day', 8);
        }

        if ($working_days_per_week === null) {
            $working_days_per_week = (int) get_option('project_enhancement_working_days_per_week', 5);
        }

        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        
        $total_days = $interval->days;
        $working_days = 0;

        // Calculate working days (excluding weekends if 5-day work week)
        if ($working_days_per_week == 5) {
            $weeks = floor($total_days / 7);
            $remaining_days = $total_days % 7;
            
            $working_days = $weeks * 5;
            
            // Add remaining working days
            for ($i = 0; $i < $remaining_days; $i++) {
                $day_of_week = ($start->format('N') + $i - 1) % 7 + 1;
                if ($day_of_week <= 5) { // Monday to Friday
                    $working_days++;
                }
            }
        } else {
            $working_days = $total_days;
        }

        return $working_days * $hours_per_day;
    }
}

if (!function_exists('get_milestone_completion_percentage')) {
    /**
     * Calculate milestone completion percentage based on tasks
     *
     * @param int $milestone_id
     * @return float
     */
    function get_milestone_completion_percentage($milestone_id)
    {
        $CI = &get_instance();
        $CI->load->model('project_enhancement/milestones_model');
        
        return $CI->milestones_model->calculate_completion_percentage($milestone_id);
    }
}

if (!function_exists('is_milestone_overdue')) {
    /**
     * Check if a milestone is overdue
     *
     * @param string $due_date
     * @param string $status
     * @return bool
     */
    function is_milestone_overdue($due_date, $status)
    {
        if (empty($due_date) || $status === 'completed') {
            return false;
        }

        return strtotime($due_date) < time();
    }
}

if (!function_exists('get_days_until_due')) {
    /**
     * Get the number of days until a due date
     *
     * @param string $due_date
     * @return int
     */
    function get_days_until_due($due_date)
    {
        if (empty($due_date)) {
            return null;
        }

        $due_timestamp = strtotime($due_date);
        $current_timestamp = time();
        
        return ceil(($due_timestamp - $current_timestamp) / (60 * 60 * 24));
    }
}

if (!function_exists('create_widget_id')) {
    /**
     * Create a unique widget ID
     *
     * @return string
     */
    function create_widget_id()
    {
        return 'widget-' . uniqid();
    }
}

if (!function_exists('project_enhancement_log_activity')) {
    /**
     * Log activity for project enhancement module
     *
     * @param string $description
     * @param int $staff_id
     * @param array $additional_data
     * @return void
     */
    function project_enhancement_log_activity($description, $staff_id = null, $additional_data = [])
    {
        if ($staff_id === null) {
            $staff_id = get_staff_user_id();
        }

        $log_data = [
            'description' => '[Project Enhancement] ' . $description,
            'date' => date('Y-m-d H:i:s'),
            'staffid' => $staff_id,
            'additional_data' => json_encode($additional_data)
        ];

        if (function_exists('log_activity')) {
            log_activity($log_data['description']);
        }
    }
}

if (!function_exists('get_project_enhancement_permissions')) {
    /**
     * Get all project enhancement permissions for current user
     *
     * @return array
     */
    function get_project_enhancement_permissions()
    {
        $permissions = [];
        $capabilities = ['view', 'create', 'edit', 'delete', 'manage_milestones', 'track_time', 'approve_time', 'manage_resources', 'view_budget', 'manage_budget'];
        
        foreach ($capabilities as $capability) {
            $permissions[$capability] = staff_can($capability, 'project_enhancement');
        }
        
        return $permissions;
    }
}

if (!function_exists('project_enhancement_access_denied')) {
    /**
     * Handle access denied for project enhancement features
     *
     * @param string $capability
     * @return void
     */
    function project_enhancement_access_denied($capability = '')
    {
        if (function_exists('access_denied')) {
            access_denied('project_enhancement' . ($capability ? '_' . $capability : ''));
        } else {
            show_404();
        }
    }
}