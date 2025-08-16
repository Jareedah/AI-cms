<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Project Enhancement Main Controller
 * Handles dashboard, overview, and general module functionality
 */
class Project_enhancement extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        // Check if module is active
        if (!get_option('project_enhancement_active')) {
            show_404();
        }
        
        // Load required models
        $this->load->model('project_enhancement/milestones_model');
        $this->load->model('project_enhancement/time_tracking_model');
        $this->load->model('project_enhancement/resources_model');
        $this->load->model('project_enhancement/budget_model');
        
        // Load helpers and libraries
        $this->load->helper('project_enhancement_helper');
        $this->load->library('project_enhancement/project_enhancement_module');
    }

    /**
     * Main dashboard/overview page
     */
    public function index()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('project_enhancement');
        
        // Get dashboard statistics
        $data['stats'] = $this->get_dashboard_stats();
        
        // Get recent activities
        $data['recent_milestones'] = $this->milestones_model->get_all([], 5);
        $data['recent_time_entries'] = $this->time_tracking_model->get_all([], 5);
        $data['pending_approvals'] = $this->time_tracking_model->get_pending_approval();
        
        // Get upcoming milestones
        $data['upcoming_milestones'] = $this->milestones_model->get_upcoming(7);
        $data['overdue_milestones'] = $this->milestones_model->get_overdue();
        
        // Get budget alerts
        $data['budget_alerts'] = $this->get_budget_alerts();
        
        // Get resource utilization
        $data['resource_stats'] = $this->resources_model->get_utilization_stats();
        
        // Load dashboard view
        $this->load->view('admin/project_enhancement/dashboard', $data);
    }

    /**
     * Module settings page
     */
    public function settings()
    {
        // Check admin permissions
        if (!is_admin()) {
            access_denied('project_enhancement_settings');
        }

        $data['title'] = _l('module_settings');

        // Handle form submission
        if ($this->input->post()) {
            $this->handle_settings_update();
        }

        // Get current settings
        $data['settings'] = $this->get_module_settings();
        
        $this->load->view('admin/project_enhancement/settings', $data);
    }

    /**
     * Projects overview with enhanced features
     */
    public function projects()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('projects') . ' - ' . _l('project_enhancement');
        
        // Get projects with enhancement data
        $data['projects'] = $this->get_enhanced_projects();
        
        $this->load->view('admin/project_enhancement/projects', $data);
    }

    /**
     * Project detail view with enhancement features
     */
    public function project($id)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Validate project exists
        $this->load->model('projects_model');
        $project = $this->projects_model->get($id);
        
        if (!$project) {
            show_404();
        }

        $data['title'] = $project->name . ' - ' . _l('project_enhancement');
        $data['project'] = $project;
        
        // Get project enhancement data
        $data['milestones'] = $this->milestones_model->get_by_project($id);
        $data['milestone_stats'] = $this->milestones_model->get_project_statistics($id);
        $data['time_entries'] = $this->time_tracking_model->get_by_project($id);
        $data['time_stats'] = $this->time_tracking_model->get_statistics(['project_id' => $id]);
        $data['resources'] = $this->resources_model->get_by_project($id);
        $data['budget_summary'] = $this->budget_model->get_project_summary($id);
        $data['budget_alerts'] = $this->budget_model->get_budget_alerts($id);
        
        $this->load->view('admin/project_enhancement/project_detail', $data);
    }

    /**
     * Export project data
     */
    public function export($project_id = null, $format = 'csv')
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $this->load->library('export');
        
        $data = $this->prepare_export_data($project_id);
        
        switch ($format) {
            case 'csv':
                $this->export_csv($data, $project_id);
                break;
            case 'excel':
                $this->export_excel($data, $project_id);
                break;
            case 'pdf':
                $this->export_pdf($data, $project_id);
                break;
            default:
                show_404();
        }
    }

    /**
     * AJAX endpoint for dashboard widgets
     */
    public function widget_data($widget_type)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $data = [];

        switch ($widget_type) {
            case 'project_progress':
                $data = $this->get_project_progress_widget_data();
                break;
            case 'time_tracking':
                $data = $this->get_time_tracking_widget_data();
                break;
            case 'budget_status':
                $data = $this->get_budget_status_widget_data();
                break;
            case 'upcoming_milestones':
                $data = $this->get_upcoming_milestones_widget_data();
                break;
            case 'resource_utilization':
                $data = $this->get_resource_utilization_widget_data();
                break;
            default:
                header('HTTP/1.1 404 Not Found');
                exit;
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * AJAX endpoint for charts data
     */
    public function chart_data($chart_type, $project_id = null)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $data = [];

        switch ($chart_type) {
            case 'milestone_progress':
                $data = $this->get_milestone_progress_chart($project_id);
                break;
            case 'time_distribution':
                $data = $this->get_time_distribution_chart($project_id);
                break;
            case 'budget_utilization':
                $data = $this->get_budget_utilization_chart($project_id);
                break;
            case 'resource_workload':
                $data = $this->get_resource_workload_chart($project_id);
                break;
            default:
                header('HTTP/1.1 404 Not Found');
                exit;
        }

        header('Content-Type: application/json');
        echo json_encode($data);
    }

    /**
     * Get dashboard statistics
     */
    private function get_dashboard_stats()
    {
        $stats = [
            'total_projects' => 0,
            'active_projects' => 0,
            'total_milestones' => 0,
            'completed_milestones' => 0,
            'overdue_milestones' => 0,
            'total_time_entries' => 0,
            'pending_approvals' => 0,
            'total_budget_allocated' => 0,
            'total_budget_spent' => 0,
            'over_budget_projects' => 0,
            'active_resources' => 0,
            'over_allocated_staff' => 0
        ];

        // Projects stats
        $this->load->model('projects_model');
        $stats['total_projects'] = $this->projects_model->get_projects_total();
        $stats['active_projects'] = $this->projects_model->get_projects_total(['status' => 2]); // In progress

        // Milestones stats
        $all_milestones = $this->milestones_model->get_all();
        $stats['total_milestones'] = count($all_milestones);
        $stats['completed_milestones'] = count(array_filter($all_milestones, function($m) {
            return $m['status'] == 'completed';
        }));
        $stats['overdue_milestones'] = count($this->milestones_model->get_overdue());

        // Time tracking stats
        $time_stats = $this->time_tracking_model->get_statistics();
        $stats['total_time_entries'] = $time_stats['total_entries'];
        $stats['pending_approvals'] = count($this->time_tracking_model->get_pending_approval());

        // Budget stats
        $budget_stats = $this->budget_model->get_statistics();
        $stats['total_budget_allocated'] = $budget_stats['total_allocated'];
        $stats['total_budget_spent'] = $budget_stats['total_spent'];
        $stats['over_budget_projects'] = $budget_stats['over_budget_count'];

        // Resource stats
        $resource_stats = $this->resources_model->get_utilization_stats();
        $stats['active_resources'] = $resource_stats['allocated_staff'];
        $stats['over_allocated_staff'] = $resource_stats['over_allocated_staff'];

        return $stats;
    }

    /**
     * Get budget alerts for dashboard
     */
    private function get_budget_alerts()
    {
        $alerts = [];
        
        // Get all active projects
        $this->load->model('projects_model');
        $projects = $this->projects_model->get(['status' => 2]); // In progress projects
        
        foreach ($projects as $project) {
            $project_alerts = $this->budget_model->get_budget_alerts($project['id']);
            $alerts = array_merge($alerts, $project_alerts);
        }
        
        // Sort by severity (critical first)
        usort($alerts, function($a, $b) {
            $severity_order = ['critical' => 1, 'warning' => 2, 'info' => 3];
            return $severity_order[$a['severity']] <=> $severity_order[$b['severity']];
        });
        
        return array_slice($alerts, 0, 10); // Limit to 10 alerts
    }

    /**
     * Get enhanced projects data
     */
    private function get_enhanced_projects()
    {
        $this->load->model('projects_model');
        $projects = $this->projects_model->get();
        
        foreach ($projects as &$project) {
            // Add milestone stats
            $project['milestone_stats'] = $this->milestones_model->get_project_statistics($project['id']);
            
            // Add time tracking stats
            $project['time_stats'] = $this->time_tracking_model->get_statistics(['project_id' => $project['id']]);
            
            // Add budget summary
            $project['budget_summary'] = $this->budget_model->get_project_summary($project['id']);
            
            // Add resource count
            $project['resource_count'] = count($this->resources_model->get_by_project($project['id']));
        }
        
        return $projects;
    }

    /**
     * Handle settings update
     */
    private function handle_settings_update()
    {
        $settings = [
            'project_enhancement_auto_milestone_creation',
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
            'project_enhancement_working_hours_per_day',
            'project_enhancement_working_days_per_week'
        ];

        foreach ($settings as $setting) {
            $value = $this->input->post($setting);
            if ($value !== null) {
                update_option($setting, $value);
            }
        }

        set_alert('success', _l('settings_updated_successfully'));
        redirect(admin_url('project_enhancement/settings'));
    }

    /**
     * Get current module settings
     */
    private function get_module_settings()
    {
        return [
            'auto_milestone_creation' => get_option('project_enhancement_auto_milestone_creation'),
            'time_tracking_enabled' => get_option('project_enhancement_time_tracking_enabled'),
            'time_approval_required' => get_option('project_enhancement_time_approval_required'),
            'budget_tracking_enabled' => get_option('project_enhancement_budget_tracking_enabled'),
            'resource_management_enabled' => get_option('project_enhancement_resource_management_enabled'),
            'email_notifications' => get_option('project_enhancement_email_notifications'),
            'client_portal_enabled' => get_option('project_enhancement_client_portal_enabled'),
            'dashboard_widgets_enabled' => get_option('project_enhancement_dashboard_widgets_enabled'),
            'api_enabled' => get_option('project_enhancement_api_enabled'),
            'cron_enabled' => get_option('project_enhancement_cron_enabled'),
            'default_hourly_rate' => get_option('project_enhancement_default_hourly_rate'),
            'currency' => get_option('project_enhancement_currency'),
            'working_hours_per_day' => get_option('project_enhancement_working_hours_per_day'),
            'working_days_per_week' => get_option('project_enhancement_working_days_per_week')
        ];
    }

    /**
     * Prepare data for export
     */
    private function prepare_export_data($project_id = null)
    {
        $data = [
            'milestones' => [],
            'time_entries' => [],
            'resources' => [],
            'budgets' => []
        ];

        if ($project_id) {
            $data['milestones'] = $this->milestones_model->get_by_project($project_id);
            $data['time_entries'] = $this->time_tracking_model->get_by_project($project_id);
            $data['resources'] = $this->resources_model->get_by_project($project_id);
            $data['budgets'] = $this->budget_model->get_by_project($project_id);
        } else {
            $data['milestones'] = $this->milestones_model->get_all();
            $data['time_entries'] = $this->time_tracking_model->get_all();
            $data['resources'] = $this->resources_model->get_all();
            $data['budgets'] = $this->budget_model->get_all();
        }

        return $data;
    }

    /**
     * Export data as CSV
     */
    private function export_csv($data, $project_id = null)
    {
        $filename = 'project_enhancement_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        // Export milestones
        if (!empty($data['milestones'])) {
            fputcsv($output, ['=== MILESTONES ===']);
            fputcsv($output, ['ID', 'Project', 'Name', 'Status', 'Progress', 'Due Date', 'Priority']);
            foreach ($data['milestones'] as $milestone) {
                fputcsv($output, [
                    $milestone['id'],
                    $milestone['project_name'] ?? '',
                    $milestone['name'],
                    $milestone['status'],
                    $milestone['completion_percentage'] . '%',
                    $milestone['due_date'],
                    $milestone['priority']
                ]);
            }
            fputcsv($output, []); // Empty row
        }
        
        // Export time entries
        if (!empty($data['time_entries'])) {
            fputcsv($output, ['=== TIME ENTRIES ===']);
            fputcsv($output, ['ID', 'Project', 'Staff', 'Duration (hours)', 'Billable', 'Status', 'Date']);
            foreach ($data['time_entries'] as $entry) {
                fputcsv($output, [
                    $entry['id'],
                    $entry['project_name'] ?? '',
                    ($entry['firstname'] ?? '') . ' ' . ($entry['lastname'] ?? ''),
                    round($entry['duration_minutes'] / 60, 2),
                    $entry['billable'] ? 'Yes' : 'No',
                    $entry['status'],
                    date('Y-m-d', strtotime($entry['start_time']))
                ]);
            }
        }
        
        fclose($output);
    }

    /**
     * Get widget data methods
     */
    private function get_project_progress_widget_data()
    {
        $projects = $this->get_enhanced_projects();
        $data = [];
        
        foreach (array_slice($projects, 0, 5) as $project) {
            $data[] = [
                'name' => $project['name'],
                'progress' => $project['milestone_stats']['avg_progress'],
                'status' => $project['status']
            ];
        }
        
        return $data;
    }

    private function get_time_tracking_widget_data()
    {
        $stats = $this->time_tracking_model->get_statistics();
        
        return [
            'total_hours' => $stats['total_hours'],
            'billable_hours' => $stats['billable_hours'],
            'pending_hours' => $stats['pending_hours'],
            'by_category' => array_slice($stats['by_category'], 0, 5, true)
        ];
    }

    private function get_budget_status_widget_data()
    {
        $stats = $this->budget_model->get_statistics();
        
        return [
            'total_allocated' => $stats['total_allocated'],
            'total_spent' => $stats['total_spent'],
            'avg_utilization' => $stats['avg_utilization'],
            'over_budget_count' => $stats['over_budget_count']
        ];
    }

    private function get_upcoming_milestones_widget_data()
    {
        return $this->milestones_model->get_upcoming(14); // Next 2 weeks
    }

    private function get_resource_utilization_widget_data()
    {
        return $this->resources_model->get_utilization_stats();
    }

    /**
     * Chart data methods
     */
    private function get_milestone_progress_chart($project_id = null)
    {
        if ($project_id) {
            $milestones = $this->milestones_model->get_by_project($project_id);
        } else {
            $milestones = $this->milestones_model->get_all();
        }
        
        $status_counts = [
            'not_started' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'on_hold' => 0,
            'cancelled' => 0
        ];
        
        foreach ($milestones as $milestone) {
            $status_counts[$milestone['status']]++;
        }
        
        return [
            'labels' => array_keys($status_counts),
            'data' => array_values($status_counts),
            'colors' => ['#6c757d', '#007bff', '#28a745', '#ffc107', '#dc3545']
        ];
    }

    private function get_time_distribution_chart($project_id = null)
    {
        $filters = $project_id ? ['project_id' => $project_id] : [];
        $stats = $this->time_tracking_model->get_statistics($filters);
        
        return [
            'labels' => array_keys($stats['by_category']),
            'data' => array_column($stats['by_category'], 'hours'),
            'colors' => ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1', '#20c997', '#fd7e14']
        ];
    }

    private function get_budget_utilization_chart($project_id = null)
    {
        if ($project_id) {
            $summary = $this->budget_model->get_project_summary($project_id);
            $categories = $summary['categories'];
        } else {
            $stats = $this->budget_model->get_statistics();
            $categories = $stats['by_category'];
        }
        
        return [
            'labels' => array_keys($categories),
            'allocated' => array_column($categories, 'allocated'),
            'spent' => array_column($categories, 'spent')
        ];
    }

    private function get_resource_workload_chart($project_id = null)
    {
        $filters = $project_id ? ['project_id' => $project_id] : [];
        $stats = $this->resources_model->get_utilization_stats($filters);
        
        return [
            'labels' => array_keys($stats['by_staff']),
            'data' => array_column($stats['by_staff'], 'total_allocation'),
            'colors' => ['#007bff', '#28a745', '#ffc107', '#dc3545', '#6f42c1']
        ];
    }
}