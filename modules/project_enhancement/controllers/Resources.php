<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Resources Controller
 * Handles staff allocation, skills management, and availability tracking
 */
class Resources extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        // Check if module is active
        if (!get_option('project_enhancement_active')) {
            show_404();
        }
        
        // Load required models
        $this->load->model('project_enhancement/resources_model');
        $this->load->model('projects_model');
        $this->load->model('staff_model');
    }

    /**
     * List all resource allocations
     */
    public function index()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('resource_management');
        
        // Get filter parameters
        $project_id = $this->input->get('project_id');
        $staff_id = $this->input->get('staff_id');
        $active_only = $this->input->get('active_only') !== '0';
        
        // Build where clause
        $where = [];
        if ($project_id) {
            $where['pr.project_id'] = $project_id;
        }
        if ($staff_id) {
            $where['pr.staff_id'] = $staff_id;
        }
        if ($active_only) {
            $where['pr.active'] = 1;
        }
        
        // Get resource allocations with pagination
        $limit = 25;
        $offset = ($this->input->get('page') ?: 1 - 1) * $limit;
        
        $data['resources'] = $this->resources_model->get_all($where, $limit, $offset);
        $data['total_resources'] = count($this->resources_model->get_all($where));
        
        // Get filter options
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        $data['current_filters'] = [
            'project_id' => $project_id,
            'staff_id' => $staff_id,
            'active_only' => $active_only
        ];
        
        // Get utilization statistics
        $data['utilization_stats'] = $this->resources_model->get_utilization_stats();
        
        $this->load->view('admin/project_enhancement/resources/manage', $data);
    }

    /**
     * Create new resource allocation
     */
    public function create($project_id = null, $staff_id = null)
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement') || !staff_can('manage_resources', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('add') . ' ' . _l('resource_allocation');
        
        // Handle form submission
        if ($this->input->post()) {
            $this->handle_resource_form();
            return;
        }
        
        // Prepare form data
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        $data['selected_project_id'] = $project_id;
        $data['selected_staff_id'] = $staff_id;
        
        // Get availability data for selected staff
        if ($staff_id) {
            $data['availability'] = $this->resources_model->get_staff_availability(
                $staff_id, 
                date('Y-m-d'), 
                date('Y-m-d', strtotime('+30 days'))
            );
        }
        
        $this->load->view('admin/project_enhancement/resources/form', $data);
    }

    /**
     * Edit resource allocation
     */
    public function edit($id)
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement') || !staff_can('manage_resources', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get resource allocation
        $resource = $this->resources_model->get($id);
        if (!$resource) {
            show_404();
        }

        $data['title'] = _l('edit') . ' ' . _l('resource_allocation');
        $data['resource'] = $resource;
        
        // Handle form submission
        if ($this->input->post()) {
            $this->handle_resource_form($id);
            return;
        }
        
        // Prepare form data
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        
        // Get availability data
        $data['availability'] = $this->resources_model->get_staff_availability(
            $resource->staff_id, 
            date('Y-m-d'), 
            date('Y-m-d', strtotime('+30 days'))
        );
        
        $this->load->view('admin/project_enhancement/resources/form', $data);
    }

    /**
     * View resource allocation details
     */
    public function view($id)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get resource allocation
        $resource = $this->resources_model->get($id);
        if (!$resource) {
            show_404();
        }

        $data['title'] = _l('resource_allocation') . ' - ' . $resource->firstname . ' ' . $resource->lastname;
        $data['resource'] = $resource;
        
        // Get workload forecast
        $data['workload_forecast'] = $this->resources_model->get_workload_forecast($resource->staff_id, 60);
        
        // Get related time entries
        $this->load->model('project_enhancement/time_tracking_model');
        $data['time_entries'] = $this->time_tracking_model->get_by_project($resource->project_id, null, $resource->staff_id);
        
        $this->load->view('admin/project_enhancement/resources/view', $data);
    }

    /**
     * Delete resource allocation
     */
    public function delete($id)
    {
        // Check permissions
        if (!staff_can('delete', 'project_enhancement') || !staff_can('manage_resources', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Validate resource exists
        $resource = $this->resources_model->get($id);
        if (!$resource) {
            set_alert('danger', _l('resource_not_found'));
            redirect(admin_url('project_enhancement/resources'));
        }

        if ($this->resources_model->delete($id)) {
            set_alert('success', _l('resource_allocation_deleted_successfully'));
        } else {
            set_alert('danger', _l('operation_failed'));
        }

        redirect(admin_url('project_enhancement/resources'));
    }

    /**
     * Staff skills management
     */
    public function skills($staff_id = null)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement') || !staff_can('manage_skills', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('skills_management');
        
        if ($staff_id) {
            // View specific staff member's skills
            $staff = $this->staff_model->get($staff_id);
            if (!$staff) {
                show_404();
            }
            
            $data['staff_member'] = $staff;
            $data['skills'] = $this->resources_model->get_staff_skills($staff_id);
        } else {
            // Overview of all staff skills
            $data['staff_members'] = $this->staff_model->get();
            
            // Get skills summary
            $skills_summary = [];
            foreach ($data['staff_members'] as $staff) {
                $skills = $this->resources_model->get_staff_skills($staff['staffid']);
                $skills_summary[$staff['staffid']] = [
                    'staff' => $staff,
                    'skills_count' => count($skills),
                    'skills' => $skills
                ];
            }
            $data['skills_summary'] = $skills_summary;
        }
        
        $this->load->view('admin/project_enhancement/resources/skills', $data);
    }

    /**
     * Add skill to staff member
     */
    public function add_skill($staff_id)
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement') || !staff_can('manage_skills', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Validate staff member
        $staff = $this->staff_model->get($staff_id);
        if (!$staff) {
            show_404();
        }

        if ($this->input->post()) {
            $skill_data = [
                'staff_id' => $staff_id,
                'skill_name' => $this->input->post('skill_name'),
                'proficiency_level' => $this->input->post('proficiency_level'),
                'years_experience' => $this->input->post('years_experience'),
                'verified_by' => $this->input->post('verified_by'),
                'notes' => $this->input->post('notes')
            ];

            if ($this->resources_model->add_skill($skill_data)) {
                set_alert('success', _l('skill_added_successfully'));
            } else {
                set_alert('danger', _l('operation_failed'));
            }

            redirect(admin_url('project_enhancement/resources/skills/' . $staff_id));
        }

        $data['title'] = _l('add_skill');
        $data['staff_member'] = $staff;
        $data['staff_members'] = $this->staff_model->get(); // For verifier dropdown
        
        $this->load->view('admin/project_enhancement/resources/skill_form', $data);
    }

    /**
     * Edit staff skill
     */
    public function edit_skill($skill_id)
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement') || !staff_can('manage_skills', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get skill
        $this->db->where('id', $skill_id);
        $skill = $this->db->get(db_prefix() . 'staff_skills')->row();
        
        if (!$skill) {
            show_404();
        }

        if ($this->input->post()) {
            $skill_data = [
                'skill_name' => $this->input->post('skill_name'),
                'proficiency_level' => $this->input->post('proficiency_level'),
                'years_experience' => $this->input->post('years_experience'),
                'verified_by' => $this->input->post('verified_by'),
                'notes' => $this->input->post('notes')
            ];

            if ($this->resources_model->update_skill($skill_id, $skill_data)) {
                set_alert('success', _l('skill_updated_successfully'));
            } else {
                set_alert('danger', _l('operation_failed'));
            }

            redirect(admin_url('project_enhancement/resources/skills/' . $skill->staff_id));
        }

        $data['title'] = _l('edit_skill');
        $data['skill'] = $skill;
        $data['staff_member'] = $this->staff_model->get($skill->staff_id);
        $data['staff_members'] = $this->staff_model->get(); // For verifier dropdown
        
        $this->load->view('admin/project_enhancement/resources/skill_form', $data);
    }

    /**
     * Delete staff skill
     */
    public function delete_skill($skill_id)
    {
        // Check permissions
        if (!staff_can('delete', 'project_enhancement') || !staff_can('manage_skills', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get skill to find staff_id for redirect
        $this->db->where('id', $skill_id);
        $skill = $this->db->get(db_prefix() . 'staff_skills')->row();
        
        if (!$skill) {
            set_alert('danger', _l('skill_not_found'));
            redirect(admin_url('project_enhancement/resources/skills'));
        }

        if ($this->resources_model->delete_skill($skill_id)) {
            set_alert('success', _l('skill_deleted_successfully'));
        } else {
            set_alert('danger', _l('operation_failed'));
        }

        redirect(admin_url('project_enhancement/resources/skills/' . $skill->staff_id));
    }

    /**
     * Availability management
     */
    public function availability($staff_id = null)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement') || !staff_can('manage_availability', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('availability_management');
        
        // Get date range
        $date_from = $this->input->get('date_from') ?: date('Y-m-d');
        $date_to = $this->input->get('date_to') ?: date('Y-m-d', strtotime('+30 days'));
        
        if ($staff_id) {
            // View specific staff member's availability
            $staff = $this->staff_model->get($staff_id);
            if (!$staff) {
                show_404();
            }
            
            $data['staff_member'] = $staff;
            $data['availability'] = $this->resources_model->get_staff_availability($staff_id, $date_from, $date_to);
        } else {
            // Overview of all staff availability
            $data['staff_members'] = $this->staff_model->get();
            
            // Get availability summary
            $availability_summary = [];
            foreach ($data['staff_members'] as $staff) {
                $availability = $this->resources_model->get_staff_availability($staff['staffid'], $date_from, $date_to);
                $avg_availability = $this->calculate_average_availability($availability);
                
                $availability_summary[$staff['staffid']] = [
                    'staff' => $staff,
                    'avg_availability' => $avg_availability,
                    'status' => $this->get_availability_status($avg_availability)
                ];
            }
            $data['availability_summary'] = $availability_summary;
        }
        
        $data['date_from'] = $date_from;
        $data['date_to'] = $date_to;
        
        $this->load->view('admin/project_enhancement/resources/availability', $data);
    }

    /**
     * Set staff availability
     */
    public function set_availability()
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement') || !staff_can('manage_availability', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $staff_id = $this->input->post('staff_id');
        $date = $this->input->post('date');
        $available_hours = $this->input->post('available_hours');
        $reason = $this->input->post('reason');

        if (!$staff_id || !$date || $available_hours === null) {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }

        $success = $this->resources_model->set_availability($staff_id, $date, $available_hours, $reason);

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }

    /**
     * Resource optimization
     */
    public function optimize($project_id)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement') || !staff_can('manage_resources', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Validate project
        $project = $this->projects_model->get($project_id);
        if (!$project) {
            show_404();
        }

        $data['title'] = _l('resource_optimization') . ' - ' . $project->name;
        $data['project'] = $project;

        if ($this->input->post()) {
            // Get optimization requirements
            $requirements = [
                'start_date' => $this->input->post('start_date'),
                'end_date' => $this->input->post('end_date'),
                'skills' => $this->input->post('skills') ?: [],
                'min_availability' => $this->input->post('min_availability') ?: 20,
                'total_percentage' => $this->input->post('total_percentage') ?: 100,
                'department_id' => $this->input->post('department_id')
            ];

            // Run optimization
            $optimization_result = $this->resources_model->optimize_allocation($project_id, $requirements);
            $data['optimization_result'] = $optimization_result;
            
            // Get available staff for manual selection
            $data['available_staff'] = $this->resources_model->find_available_staff($requirements);
        }

        // Get existing allocations
        $data['current_allocations'] = $this->resources_model->get_by_project($project_id);
        
        // Get departments for filtering
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        
        $this->load->view('admin/project_enhancement/resources/optimize', $data);
    }

    /**
     * Find available staff (AJAX)
     */
    public function find_available_staff()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $requirements = [
            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),
            'skills' => $this->input->post('skills') ?: [],
            'min_availability' => $this->input->post('min_availability') ?: 20,
            'department_id' => $this->input->post('department_id')
        ];

        $available_staff = $this->resources_model->find_available_staff($requirements);

        header('Content-Type: application/json');
        echo json_encode(['staff' => $available_staff]);
    }

    /**
     * Utilization reports
     */
    public function reports()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('resource_utilization_reports');
        
        // Get filter parameters
        $department_id = $this->input->get('department_id');
        $date_from = $this->input->get('date_from') ?: date('Y-m-01');
        $date_to = $this->input->get('date_to') ?: date('Y-m-t');
        
        // Build filters
        $filters = [];
        if ($department_id) {
            $filters['department_id'] = $department_id;
        }
        if ($date_from) {
            $filters['date_from'] = $date_from;
        }
        if ($date_to) {
            $filters['date_to'] = $date_to;
        }
        
        // Get utilization statistics
        $data['utilization_stats'] = $this->resources_model->get_utilization_stats($filters);
        
        // Get departments for filtering
        $this->load->model('departments_model');
        $data['departments'] = $this->departments_model->get();
        
        $data['current_filters'] = [
            'department_id' => $department_id,
            'date_from' => $date_from,
            'date_to' => $date_to
        ];
        
        $this->load->view('admin/project_enhancement/resources/reports', $data);
    }

    /**
     * Bulk actions
     */
    public function bulk_action()
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement') || !staff_can('manage_resources', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $action = $this->input->post('bulk_action');
        $resource_ids = $this->input->post('resource_ids');

        if (!$action || empty($resource_ids)) {
            set_alert('danger', _l('no_items_selected'));
            redirect(admin_url('project_enhancement/resources'));
        }

        $success_count = 0;
        
        switch ($action) {
            case 'delete':
                if (staff_can('delete', 'project_enhancement')) {
                    foreach ($resource_ids as $id) {
                        if ($this->resources_model->delete($id)) {
                            $success_count++;
                        }
                    }
                    set_alert('success', sprintf(_l('bulk_delete_success'), $success_count));
                }
                break;
                
            case 'deactivate':
                foreach ($resource_ids as $id) {
                    if ($this->resources_model->update($id, ['active' => 0])) {
                        $success_count++;
                    }
                }
                set_alert('success', sprintf(_l('bulk_deactivate_success'), $success_count));
                break;
                
            case 'activate':
                foreach ($resource_ids as $id) {
                    if ($this->resources_model->update($id, ['active' => 1])) {
                        $success_count++;
                    }
                }
                set_alert('success', sprintf(_l('bulk_activate_success'), $success_count));
                break;
        }

        redirect(admin_url('project_enhancement/resources'));
    }

    /**
     * Export resources data
     */
    public function export($format = 'csv')
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get filter parameters
        $filters = [
            'project_id' => $this->input->get('project_id'),
            'staff_id' => $this->input->get('staff_id'),
            'active_only' => $this->input->get('active_only') !== '0'
        ];

        // Get resources data
        $where = [];
        if ($filters['project_id']) {
            $where['pr.project_id'] = $filters['project_id'];
        }
        if ($filters['staff_id']) {
            $where['pr.staff_id'] = $filters['staff_id'];
        }
        if ($filters['active_only']) {
            $where['pr.active'] = 1;
        }

        $resources = $this->resources_model->get_all($where);

        $filename = 'resources_export_' . date('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                $this->export_csv($resources, $filename);
                break;
            case 'excel':
                $this->export_excel($resources, $filename);
                break;
            default:
                show_404();
        }
    }

    /**
     * Handle resource form submission
     */
    private function handle_resource_form($id = null)
    {
        $data = [
            'project_id' => $this->input->post('project_id'),
            'staff_id' => $this->input->post('staff_id'),
            'role' => $this->input->post('role'),
            'start_date' => $this->input->post('start_date'),
            'end_date' => $this->input->post('end_date'),
            'allocation_percentage' => $this->input->post('allocation_percentage'),
            'hourly_rate' => $this->input->post('hourly_rate'),
            'notes' => $this->input->post('notes'),
            'active' => $this->input->post('active') ? 1 : 0
        ];

        if ($id) {
            // Update existing resource
            if ($this->resources_model->update($id, $data)) {
                set_alert('success', _l('resource_allocation_updated_successfully'));
                redirect(admin_url('project_enhancement/resources/view/' . $id));
            } else {
                set_alert('danger', _l('operation_failed'));
            }
        } else {
            // Create new resource
            $resource_id = $this->resources_model->add($data);
            if ($resource_id) {
                set_alert('success', _l('resource_allocation_created_successfully'));
                redirect(admin_url('project_enhancement/resources/view/' . $resource_id));
            } else {
                set_alert('danger', _l('operation_failed'));
            }
        }
    }

    /**
     * Calculate average availability
     */
    private function calculate_average_availability($availability)
    {
        if (empty($availability)) {
            return 0;
        }

        $total_percentage = array_sum(array_column($availability, 'available_percentage'));
        return round($total_percentage / count($availability), 2);
    }

    /**
     * Get availability status
     */
    private function get_availability_status($percentage)
    {
        if ($percentage >= 80) {
            return 'available';
        } elseif ($percentage >= 50) {
            return 'partially_available';
        } elseif ($percentage >= 20) {
            return 'limited_availability';
        } else {
            return 'unavailable';
        }
    }

    /**
     * Export resources as CSV
     */
    private function export_csv($resources, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'ID',
            'Project',
            'Staff Member',
            'Role',
            'Start Date',
            'End Date',
            'Allocation %',
            'Hourly Rate',
            'Active',
            'Created Date'
        ]);

        // CSV data
        foreach ($resources as $resource) {
            fputcsv($output, [
                $resource['id'],
                $resource['project_name'] ?? '',
                ($resource['firstname'] ?? '') . ' ' . ($resource['lastname'] ?? ''),
                $resource['role'],
                $resource['start_date'],
                $resource['end_date'] ?? '',
                $resource['allocation_percentage'] . '%',
                $resource['hourly_rate'] ?? '',
                $resource['active'] ? 'Yes' : 'No',
                $resource['created_at']
            ]);
        }

        fclose($output);
    }

    /**
     * Export resources as Excel
     */
    private function export_excel($resources, $filename)
    {
        // This would require PHPSpreadsheet or similar library
        // For now, fall back to CSV
        $this->export_csv($resources, $filename);
    }
}