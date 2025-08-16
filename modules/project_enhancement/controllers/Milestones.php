<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Milestones Controller
 * Handles milestone management, dependencies, and approval workflows
 */
class Milestones extends AdminController
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
        $this->load->model('projects_model');
        $this->load->model('staff_model');
    }

    /**
     * List all milestones
     */
    public function index()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('milestones');
        
        // Get filter parameters
        $project_id = $this->input->get('project_id');
        $status = $this->input->get('status');
        $staff_id = $this->input->get('staff_id');
        
        // Build where clause
        $where = [];
        if ($project_id) {
            $where['m.project_id'] = $project_id;
        }
        if ($status) {
            $where['m.status'] = $status;
        }
        if ($staff_id) {
            $where['m.staff_id'] = $staff_id;
        }
        
        // Get milestones with pagination
        $limit = 25;
        $offset = ($this->input->get('page') ?: 1 - 1) * $limit;
        
        $data['milestones'] = $this->milestones_model->get_all($where, $limit, $offset);
        $data['total_milestones'] = count($this->milestones_model->get_all($where));
        
        // Get filter options
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        $data['current_filters'] = [
            'project_id' => $project_id,
            'status' => $status,
            'staff_id' => $staff_id
        ];
        
        $this->load->view('admin/project_enhancement/milestones/manage', $data);
    }

    /**
     * Create new milestone
     */
    public function create($project_id = null)
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement') || !staff_can('manage_milestones', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('add') . ' ' . _l('milestone');
        
        // Handle form submission
        if ($this->input->post()) {
            $this->handle_milestone_form();
            return;
        }
        
        // Prepare form data
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        $data['selected_project_id'] = $project_id;
        
        // Get available milestones for dependencies
        if ($project_id) {
            $data['available_milestones'] = $this->milestones_model->get_by_project($project_id);
        }
        
        $this->load->view('admin/project_enhancement/milestones/form', $data);
    }

    /**
     * Edit milestone
     */
    public function edit($id)
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement') || !staff_can('manage_milestones', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get milestone
        $milestone = $this->milestones_model->get($id, true);
        if (!$milestone) {
            show_404();
        }

        $data['title'] = _l('edit') . ' ' . _l('milestone');
        $data['milestone'] = $milestone;
        
        // Handle form submission
        if ($this->input->post()) {
            $this->handle_milestone_form($id);
            return;
        }
        
        // Prepare form data
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        $data['available_milestones'] = $this->milestones_model->get_by_project($milestone->project_id);
        
        $this->load->view('admin/project_enhancement/milestones/form', $data);
    }

    /**
     * View milestone details
     */
    public function view($id)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get milestone with dependencies
        $milestone = $this->milestones_model->get($id, true);
        if (!$milestone) {
            show_404();
        }

        $data['title'] = $milestone->name;
        $data['milestone'] = $milestone;
        
        // Get related data
        $this->load->model('project_enhancement/time_tracking_model');
        $data['time_entries'] = $this->time_tracking_model->get_by_project($milestone->project_id, null, null);
        $data['time_entries'] = array_filter($data['time_entries'], function($entry) use ($id) {
            return $entry['milestone_id'] == $id;
        });
        
        // Get milestone approvals
        $this->db->select('ma.*, s.firstname, s.lastname')
                 ->from(db_prefix() . 'milestone_approvals ma')
                 ->join(db_prefix() . 'staff s', 's.staffid = ma.approver_staff_id', 'left')
                 ->where('ma.milestone_id', $id)
                 ->order_by('ma.created_at', 'DESC');
        $data['approvals'] = $this->db->get()->result_array();
        
        $this->load->view('admin/project_enhancement/milestones/view', $data);
    }

    /**
     * Delete milestone
     */
    public function delete($id)
    {
        // Check permissions
        if (!staff_can('delete', 'project_enhancement') || !staff_can('manage_milestones', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Validate milestone exists
        $milestone = $this->milestones_model->get($id);
        if (!$milestone) {
            set_alert('danger', _l('milestone_not_found'));
            redirect(admin_url('project_enhancement/milestones'));
        }

        if ($this->milestones_model->delete($id)) {
            set_alert('success', _l('milestone_deleted_successfully'));
        } else {
            set_alert('danger', _l('operation_failed'));
        }

        redirect(admin_url('project_enhancement/milestones'));
    }

    /**
     * Update milestone progress
     */
    public function update_progress()
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement') || !staff_can('manage_milestones', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $id = $this->input->post('milestone_id');
        $percentage = $this->input->post('percentage');

        if (!$id || $percentage === null) {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }

        $success = $this->milestones_model->update_progress($id, $percentage);

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }

    /**
     * Get milestones for project (AJAX)
     */
    public function get_project_milestones($project_id)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $milestones = $this->milestones_model->get_by_project($project_id);

        header('Content-Type: application/json');
        echo json_encode($milestones);
    }

    /**
     * Gantt chart view
     */
    public function gantt($project_id = null)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('milestones') . ' - ' . _l('gantt_chart');
        
        if ($project_id) {
            $project = $this->projects_model->get($project_id);
            if (!$project) {
                show_404();
            }
            $data['project'] = $project;
            $data['milestones'] = $this->milestones_model->get_by_project($project_id);
        } else {
            $data['milestones'] = $this->milestones_model->get_all();
        }
        
        $data['projects'] = $this->projects_model->get();
        $data['selected_project_id'] = $project_id;
        
        $this->load->view('admin/project_enhancement/milestones/gantt', $data);
    }

    /**
     * Get Gantt chart data (AJAX)
     */
    public function gantt_data($project_id = null)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        if ($project_id) {
            $milestones = $this->milestones_model->get_by_project($project_id);
        } else {
            $milestones = $this->milestones_model->get_all();
        }

        $gantt_data = [];
        foreach ($milestones as $milestone) {
            $gantt_data[] = [
                'id' => $milestone['id'],
                'text' => $milestone['name'],
                'start_date' => $milestone['start_date'],
                'end_date' => $milestone['due_date'],
                'progress' => $milestone['completion_percentage'] / 100,
                'priority' => $milestone['priority'],
                'status' => $milestone['status'],
                'project_name' => $milestone['project_name'] ?? '',
                'dependencies' => $this->milestones_model->get_dependencies($milestone['id'])
            ];
        }

        header('Content-Type: application/json');
        echo json_encode(['data' => $gantt_data]);
    }

    /**
     * Milestone templates
     */
    public function templates()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement') || !staff_can('manage_milestones', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('milestone_templates');
        
        // Get default templates from config
        $this->load->config('project_enhancement/module_config');
        $data['templates'] = $this->config->item('default_milestone_templates');
        
        $this->load->view('admin/project_enhancement/milestones/templates', $data);
    }

    /**
     * Apply template to project
     */
    public function apply_template($project_id)
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement') || !staff_can('manage_milestones', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Validate project
        $project = $this->projects_model->get($project_id);
        if (!$project) {
            show_404();
        }

        if ($this->input->post()) {
            $template_keys = $this->input->post('templates');
            
            if (!empty($template_keys)) {
                $this->load->config('project_enhancement/module_config');
                $all_templates = $this->config->item('default_milestone_templates');
                
                $selected_templates = [];
                foreach ($template_keys as $key) {
                    if (isset($all_templates[$key])) {
                        $selected_templates[$key] = $all_templates[$key];
                    }
                }
                
                if ($this->milestones_model->create_default_milestones($project_id, $selected_templates)) {
                    set_alert('success', _l('milestone_templates_applied_successfully'));
                } else {
                    set_alert('danger', _l('operation_failed'));
                }
            }
            
            redirect(admin_url('project_enhancement/milestones?project_id=' . $project_id));
        }

        $data['title'] = _l('apply_milestone_template');
        $data['project'] = $project;
        
        // Get available templates
        $this->load->config('project_enhancement/module_config');
        $data['templates'] = $this->config->item('default_milestone_templates');
        
        $this->load->view('admin/project_enhancement/milestones/apply_template', $data);
    }

    /**
     * Bulk actions
     */
    public function bulk_action()
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement') || !staff_can('manage_milestones', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $action = $this->input->post('bulk_action');
        $milestone_ids = $this->input->post('milestone_ids');

        if (!$action || empty($milestone_ids)) {
            set_alert('danger', _l('no_items_selected'));
            redirect(admin_url('project_enhancement/milestones'));
        }

        $success_count = 0;
        
        switch ($action) {
            case 'delete':
                if (staff_can('delete', 'project_enhancement')) {
                    foreach ($milestone_ids as $id) {
                        if ($this->milestones_model->delete($id)) {
                            $success_count++;
                        }
                    }
                    set_alert('success', sprintf(_l('bulk_delete_success'), $success_count));
                }
                break;
                
            case 'mark_completed':
                foreach ($milestone_ids as $id) {
                    if ($this->milestones_model->update($id, ['status' => 'completed', 'completion_percentage' => 100])) {
                        $success_count++;
                    }
                }
                set_alert('success', sprintf(_l('bulk_status_update_success'), $success_count));
                break;
                
            case 'mark_in_progress':
                foreach ($milestone_ids as $id) {
                    if ($this->milestones_model->update($id, ['status' => 'in_progress'])) {
                        $success_count++;
                    }
                }
                set_alert('success', sprintf(_l('bulk_status_update_success'), $success_count));
                break;
        }

        redirect(admin_url('project_enhancement/milestones'));
    }

    /**
     * Export milestones
     */
    public function export($format = 'csv', $project_id = null)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get milestones data
        if ($project_id) {
            $milestones = $this->milestones_model->get_by_project($project_id);
            $filename_suffix = '_project_' . $project_id;
        } else {
            $milestones = $this->milestones_model->get_all();
            $filename_suffix = '_all';
        }

        $filename = 'milestones_export' . $filename_suffix . '_' . date('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                $this->export_csv($milestones, $filename);
                break;
            case 'excel':
                $this->export_excel($milestones, $filename);
                break;
            default:
                show_404();
        }
    }

    /**
     * Handle milestone form submission
     */
    private function handle_milestone_form($id = null)
    {
        $data = [
            'project_id' => $this->input->post('project_id'),
            'name' => $this->input->post('name'),
            'description' => $this->input->post('description'),
            'start_date' => $this->input->post('start_date'),
            'due_date' => $this->input->post('due_date'),
            'status' => $this->input->post('status'),
            'priority' => $this->input->post('priority'),
            'staff_id' => $this->input->post('staff_id'),
            'completion_percentage' => $this->input->post('completion_percentage') ?: 0
        ];

        // Handle dependencies
        $dependencies = $this->input->post('dependencies');
        if ($dependencies) {
            $data['dependencies'] = [];
            foreach ($dependencies as $dep_id) {
                $data['dependencies'][] = [
                    'depends_on_milestone_id' => $dep_id,
                    'dependency_type' => 'finish_to_start' // Default type
                ];
            }
        }

        if ($id) {
            // Update existing milestone
            if ($this->milestones_model->update($id, $data)) {
                set_alert('success', _l('milestone_updated_successfully'));
                redirect(admin_url('project_enhancement/milestones/view/' . $id));
            } else {
                set_alert('danger', _l('operation_failed'));
            }
        } else {
            // Create new milestone
            $milestone_id = $this->milestones_model->add($data);
            if ($milestone_id) {
                set_alert('success', _l('milestone_created_successfully'));
                redirect(admin_url('project_enhancement/milestones/view/' . $milestone_id));
            } else {
                set_alert('danger', _l('operation_failed'));
            }
        }
    }

    /**
     * Export milestones as CSV
     */
    private function export_csv($milestones, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'ID',
            'Project',
            'Name',
            'Description',
            'Start Date',
            'Due Date',
            'Status',
            'Priority',
            'Progress (%)',
            'Assigned To',
            'Created Date'
        ]);

        // CSV data
        foreach ($milestones as $milestone) {
            fputcsv($output, [
                $milestone['id'],
                $milestone['project_name'] ?? '',
                $milestone['name'],
                $milestone['description'],
                $milestone['start_date'],
                $milestone['due_date'],
                $milestone['status'],
                $milestone['priority'],
                $milestone['completion_percentage'],
                ($milestone['firstname'] ?? '') . ' ' . ($milestone['lastname'] ?? ''),
                $milestone['created_at']
            ]);
        }

        fclose($output);
    }

    /**
     * Export milestones as Excel
     */
    private function export_excel($milestones, $filename)
    {
        // This would require PHPSpreadsheet or similar library
        // For now, fall back to CSV
        $this->export_csv($milestones, $filename);
    }
}