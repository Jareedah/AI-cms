<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Time Tracking Controller
 * Handles time entry management, timer functionality, and approval workflows
 */
class Time_tracking extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        // Check if module is active
        if (!get_option('project_enhancement_active')) {
            show_404();
        }
        
        // Load required models
        $this->load->model('project_enhancement/time_tracking_model');
        $this->load->model('projects_model');
        $this->load->model('staff_model');
    }

    /**
     * List all time entries
     */
    public function index()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('time_tracking');
        
        // Get filter parameters
        $project_id = $this->input->get('project_id');
        $staff_id = $this->input->get('staff_id');
        $status = $this->input->get('status');
        $date_from = $this->input->get('date_from');
        $date_to = $this->input->get('date_to');
        
        // Build where clause
        $where = [];
        if ($project_id) {
            $where['te.project_id'] = $project_id;
        }
        if ($staff_id) {
            $where['te.staff_id'] = $staff_id;
        }
        if ($status) {
            $where['te.status'] = $status;
        }
        
        // Get time entries with pagination
        $limit = 25;
        $offset = ($this->input->get('page') ?: 1 - 1) * $limit;
        
        $time_entries = $this->time_tracking_model->get_all($where, $limit, $offset);
        
        // Apply date filters if provided
        if ($date_from || $date_to) {
            $time_entries = array_filter($time_entries, function($entry) use ($date_from, $date_to) {
                $entry_date = date('Y-m-d', strtotime($entry['start_time']));
                
                if ($date_from && $entry_date < $date_from) {
                    return false;
                }
                if ($date_to && $entry_date > $date_to) {
                    return false;
                }
                return true;
            });
        }
        
        $data['time_entries'] = $time_entries;
        $data['total_entries'] = count($this->time_tracking_model->get_all($where));
        
        // Get filter options
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        $data['current_filters'] = [
            'project_id' => $project_id,
            'staff_id' => $staff_id,
            'status' => $status,
            'date_from' => $date_from,
            'date_to' => $date_to
        ];
        
        // Get statistics
        $data['stats'] = $this->time_tracking_model->get_statistics();
        
        $this->load->view('admin/project_enhancement/time_tracking/manage', $data);
    }

    /**
     * Create new time entry
     */
    public function create($project_id = null)
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('add') . ' ' . _l('time_entry');
        
        // Handle form submission
        if ($this->input->post()) {
            $this->handle_time_entry_form();
            return;
        }
        
        // Prepare form data
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        $data['selected_project_id'] = $project_id;
        $data['time_categories'] = $this->time_tracking_model->get_categories();
        
        // Get milestones for selected project
        if ($project_id) {
            $this->load->model('project_enhancement/milestones_model');
            $data['milestones'] = $this->milestones_model->get_by_project($project_id);
            
            // Get tasks for selected project
            $this->load->model('tasks_model');
            $data['tasks'] = $this->tasks_model->get_tasks(['project_id' => $project_id]);
        }
        
        $this->load->view('admin/project_enhancement/time_tracking/form', $data);
    }

    /**
     * Edit time entry
     */
    public function edit($id)
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get time entry
        $time_entry = $this->time_tracking_model->get($id);
        if (!$time_entry) {
            show_404();
        }

        // Check if user can edit this entry
        if (!is_admin() && $time_entry->staff_id != get_staff_user_id()) {
            access_denied('time_tracking');
        }

        // Check if entry is already approved/invoiced
        if ($time_entry->status == 'approved' && $time_entry->invoice_id) {
            set_alert('danger', _l('cannot_edit_invoiced_time_entry'));
            redirect(admin_url('project_enhancement/time_tracking'));
        }

        $data['title'] = _l('edit') . ' ' . _l('time_entry');
        $data['time_entry'] = $time_entry;
        
        // Handle form submission
        if ($this->input->post()) {
            $this->handle_time_entry_form($id);
            return;
        }
        
        // Prepare form data
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        $data['time_categories'] = $this->time_tracking_model->get_categories();
        
        // Get milestones and tasks for project
        $this->load->model('project_enhancement/milestones_model');
        $this->load->model('tasks_model');
        $data['milestones'] = $this->milestones_model->get_by_project($time_entry->project_id);
        $data['tasks'] = $this->tasks_model->get_tasks(['project_id' => $time_entry->project_id]);
        
        $this->load->view('admin/project_enhancement/time_tracking/form', $data);
    }

    /**
     * View time entry details
     */
    public function view($id)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get time entry
        $time_entry = $this->time_tracking_model->get($id);
        if (!$time_entry) {
            show_404();
        }

        $data['title'] = _l('time_entry') . ' #' . $id;
        $data['time_entry'] = $time_entry;
        
        $this->load->view('admin/project_enhancement/time_tracking/view', $data);
    }

    /**
     * Delete time entry
     */
    public function delete($id)
    {
        // Check permissions
        if (!staff_can('delete', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get time entry
        $time_entry = $this->time_tracking_model->get($id);
        if (!$time_entry) {
            set_alert('danger', _l('time_entry_not_found'));
            redirect(admin_url('project_enhancement/time_tracking'));
        }

        // Check if user can delete this entry
        if (!is_admin() && $time_entry->staff_id != get_staff_user_id()) {
            access_denied('time_tracking');
        }

        if ($this->time_tracking_model->delete($id)) {
            set_alert('success', _l('time_entry_deleted_successfully'));
        } else {
            set_alert('danger', _l('operation_failed'));
        }

        redirect(admin_url('project_enhancement/time_tracking'));
    }

    /**
     * Timer functionality
     */
    public function timer()
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('time_tracker');
        
        // Get running timer for current user
        $data['running_timer'] = $this->time_tracking_model->get_running_timer(get_staff_user_id());
        
        // Get recent projects for quick access
        $data['recent_projects'] = $this->get_recent_projects();
        
        // Get time categories
        $data['time_categories'] = $this->time_tracking_model->get_categories();
        
        $this->load->view('admin/project_enhancement/time_tracking/timer', $data);
    }

    /**
     * Start timer (AJAX)
     */
    public function start_timer()
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $project_id = $this->input->post('project_id');
        $task_id = $this->input->post('task_id');
        $milestone_id = $this->input->post('milestone_id');
        $description = $this->input->post('description');
        $category_id = $this->input->post('category_id');
        $billable = $this->input->post('billable') ? 1 : 0;

        if (!$project_id) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => _l('project_required')]);
            exit;
        }

        $timer_data = [
            'project_id' => $project_id,
            'task_id' => $task_id,
            'milestone_id' => $milestone_id,
            'staff_id' => get_staff_user_id(),
            'description' => $description,
            'category_id' => $category_id,
            'billable' => $billable
        ];

        $timer_id = $this->time_tracking_model->start_timer($timer_data);

        header('Content-Type: application/json');
        if ($timer_id) {
            echo json_encode(['success' => true, 'timer_id' => $timer_id]);
        } else {
            echo json_encode(['success' => false, 'error' => _l('operation_failed')]);
        }
    }

    /**
     * Stop timer (AJAX)
     */
    public function stop_timer()
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $timer_id = $this->input->post('timer_id');
        $end_time = $this->input->post('end_time') ?: date('Y-m-d H:i:s');

        if (!$timer_id) {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }

        $success = $this->time_tracking_model->stop_timer($timer_id, $end_time);

        header('Content-Type: application/json');
        echo json_encode(['success' => $success]);
    }

    /**
     * Get running timer status (AJAX)
     */
    public function timer_status()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $timer = $this->time_tracking_model->get_running_timer(get_staff_user_id());

        header('Content-Type: application/json');
        echo json_encode(['timer' => $timer]);
    }

    /**
     * Time tracking reports
     */
    public function reports()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('time_tracking_reports');
        
        // Get filter parameters
        $project_id = $this->input->get('project_id');
        $staff_id = $this->input->get('staff_id');
        $date_from = $this->input->get('date_from') ?: date('Y-m-01'); // First day of current month
        $date_to = $this->input->get('date_to') ?: date('Y-m-t'); // Last day of current month
        
        // Build filters
        $filters = [];
        if ($project_id) {
            $filters['project_id'] = $project_id;
        }
        if ($staff_id) {
            $filters['staff_id'] = $staff_id;
        }
        if ($date_from) {
            $filters['date_from'] = $date_from;
        }
        if ($date_to) {
            $filters['date_to'] = $date_to;
        }
        
        // Get statistics
        $data['stats'] = $this->time_tracking_model->get_statistics($filters);
        
        // Get detailed entries for the period
        $data['time_entries'] = $this->get_filtered_time_entries($filters);
        
        // Get filter options
        $data['projects'] = $this->projects_model->get();
        $data['staff_members'] = $this->staff_model->get();
        $data['current_filters'] = [
            'project_id' => $project_id,
            'staff_id' => $staff_id,
            'date_from' => $date_from,
            'date_to' => $date_to
        ];
        
        $this->load->view('admin/project_enhancement/time_tracking/reports', $data);
    }

    /**
     * Approval management
     */
    public function approvals()
    {
        // Check permissions
        if (!staff_can('manage_time_approvals', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('time_approvals');
        
        // Get pending approvals
        $data['pending_approvals'] = $this->time_tracking_model->get_pending_approval();
        
        $this->load->view('admin/project_enhancement/time_tracking/approvals', $data);
    }

    /**
     * Submit time entry for approval
     */
    public function submit_for_approval($id)
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get time entry
        $time_entry = $this->time_tracking_model->get($id);
        if (!$time_entry) {
            set_alert('danger', _l('time_entry_not_found'));
            redirect(admin_url('project_enhancement/time_tracking'));
        }

        // Check if user owns this entry
        if (!is_admin() && $time_entry->staff_id != get_staff_user_id()) {
            access_denied('time_tracking');
        }

        if ($this->time_tracking_model->submit_for_approval($id)) {
            set_alert('success', _l('time_entry_submitted_for_approval'));
        } else {
            set_alert('danger', _l('operation_failed'));
        }

        redirect(admin_url('project_enhancement/time_tracking/view/' . $id));
    }

    /**
     * Approve time entry
     */
    public function approve($id)
    {
        // Check permissions
        if (!staff_can('manage_time_approvals', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $comments = $this->input->post('comments');
        
        if ($this->time_tracking_model->approve($id, get_staff_user_id(), $comments)) {
            set_alert('success', _l('time_entry_approved_successfully'));
        } else {
            set_alert('danger', _l('operation_failed'));
        }

        redirect(admin_url('project_enhancement/time_tracking/approvals'));
    }

    /**
     * Reject time entry
     */
    public function reject($id)
    {
        // Check permissions
        if (!staff_can('manage_time_approvals', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $comments = $this->input->post('comments');
        
        if ($this->time_tracking_model->reject($id, get_staff_user_id(), $comments)) {
            set_alert('success', _l('time_entry_rejected_successfully'));
        } else {
            set_alert('danger', _l('operation_failed'));
        }

        redirect(admin_url('project_enhancement/time_tracking/approvals'));
    }

    /**
     * Bulk actions
     */
    public function bulk_action()
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $action = $this->input->post('bulk_action');
        $entry_ids = $this->input->post('entry_ids');

        if (!$action || empty($entry_ids)) {
            set_alert('danger', _l('no_items_selected'));
            redirect(admin_url('project_enhancement/time_tracking'));
        }

        $success_count = 0;
        
        switch ($action) {
            case 'delete':
                if (staff_can('delete', 'project_enhancement')) {
                    foreach ($entry_ids as $id) {
                        if ($this->time_tracking_model->delete($id)) {
                            $success_count++;
                        }
                    }
                    set_alert('success', sprintf(_l('bulk_delete_success'), $success_count));
                }
                break;
                
            case 'submit_for_approval':
                foreach ($entry_ids as $id) {
                    if ($this->time_tracking_model->submit_for_approval($id)) {
                        $success_count++;
                    }
                }
                set_alert('success', sprintf(_l('bulk_submit_success'), $success_count));
                break;
                
            case 'approve':
                if (staff_can('manage_time_approvals', 'project_enhancement')) {
                    foreach ($entry_ids as $id) {
                        if ($this->time_tracking_model->approve($id, get_staff_user_id())) {
                            $success_count++;
                        }
                    }
                    set_alert('success', sprintf(_l('bulk_approve_success'), $success_count));
                }
                break;
        }

        redirect(admin_url('project_enhancement/time_tracking'));
    }

    /**
     * Export time entries
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
            'date_from' => $this->input->get('date_from'),
            'date_to' => $this->input->get('date_to'),
            'status' => $this->input->get('status')
        ];

        // Remove empty filters
        $filters = array_filter($filters);

        // Get time entries
        $time_entries = $this->get_filtered_time_entries($filters);

        $filename = 'time_tracking_export_' . date('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                $this->export_csv($time_entries, $filename);
                break;
            case 'excel':
                $this->export_excel($time_entries, $filename);
                break;
            default:
                show_404();
        }
    }

    /**
     * Handle time entry form submission
     */
    private function handle_time_entry_form($id = null)
    {
        $data = [
            'project_id' => $this->input->post('project_id'),
            'task_id' => $this->input->post('task_id'),
            'milestone_id' => $this->input->post('milestone_id'),
            'staff_id' => $this->input->post('staff_id') ?: get_staff_user_id(),
            'start_time' => $this->input->post('start_time'),
            'end_time' => $this->input->post('end_time'),
            'description' => $this->input->post('description'),
            'billable' => $this->input->post('billable') ? 1 : 0,
            'hourly_rate' => $this->input->post('hourly_rate'),
            'category_id' => $this->input->post('category_id'),
            'status' => $this->input->post('status') ?: 'draft'
        ];

        // Calculate duration if both times provided
        if ($data['start_time'] && $data['end_time']) {
            $start = new DateTime($data['start_time']);
            $end = new DateTime($data['end_time']);
            $diff = $end->diff($start);
            $data['duration_minutes'] = ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
        } else {
            $data['duration_minutes'] = $this->input->post('duration_minutes') ?: 0;
        }

        if ($id) {
            // Update existing entry
            if ($this->time_tracking_model->update($id, $data)) {
                set_alert('success', _l('time_entry_updated_successfully'));
                redirect(admin_url('project_enhancement/time_tracking/view/' . $id));
            } else {
                set_alert('danger', _l('operation_failed'));
            }
        } else {
            // Create new entry
            $entry_id = $this->time_tracking_model->add($data);
            if ($entry_id) {
                set_alert('success', _l('time_entry_created_successfully'));
                redirect(admin_url('project_enhancement/time_tracking/view/' . $entry_id));
            } else {
                set_alert('danger', _l('operation_failed'));
            }
        }
    }

    /**
     * Get recent projects for current user
     */
    private function get_recent_projects($limit = 5)
    {
        // Get projects where user has recent time entries
        $this->db->select('DISTINCT p.id, p.name')
                 ->from(db_prefix() . 'projects p')
                 ->join(db_prefix() . 'time_entries te', 'te.project_id = p.id')
                 ->where('te.staff_id', get_staff_user_id())
                 ->where('te.start_time >=', date('Y-m-d', strtotime('-30 days')))
                 ->order_by('te.start_time', 'DESC')
                 ->limit($limit);

        return $this->db->get()->result_array();
    }

    /**
     * Get filtered time entries
     */
    private function get_filtered_time_entries($filters)
    {
        $where = [];
        
        if (isset($filters['project_id'])) {
            $where['te.project_id'] = $filters['project_id'];
        }
        if (isset($filters['staff_id'])) {
            $where['te.staff_id'] = $filters['staff_id'];
        }
        if (isset($filters['status'])) {
            $where['te.status'] = $filters['status'];
        }

        $entries = $this->time_tracking_model->get_all($where);

        // Apply date filters
        if (isset($filters['date_from']) || isset($filters['date_to'])) {
            $entries = array_filter($entries, function($entry) use ($filters) {
                $entry_date = date('Y-m-d', strtotime($entry['start_time']));
                
                if (isset($filters['date_from']) && $entry_date < $filters['date_from']) {
                    return false;
                }
                if (isset($filters['date_to']) && $entry_date > $filters['date_to']) {
                    return false;
                }
                return true;
            });
        }

        return $entries;
    }

    /**
     * Export time entries as CSV
     */
    private function export_csv($time_entries, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'ID',
            'Project',
            'Task',
            'Milestone',
            'Staff Member',
            'Start Time',
            'End Time',
            'Duration (hours)',
            'Description',
            'Billable',
            'Hourly Rate',
            'Total Amount',
            'Category',
            'Status',
            'Created Date'
        ]);

        // CSV data
        foreach ($time_entries as $entry) {
            $duration_hours = round($entry['duration_minutes'] / 60, 2);
            $total_amount = $duration_hours * ($entry['hourly_rate'] ?: 0);
            
            fputcsv($output, [
                $entry['id'],
                $entry['project_name'] ?? '',
                $entry['task_name'] ?? '',
                $entry['milestone_name'] ?? '',
                ($entry['firstname'] ?? '') . ' ' . ($entry['lastname'] ?? ''),
                $entry['start_time'],
                $entry['end_time'] ?? '',
                $duration_hours,
                $entry['description'],
                $entry['billable'] ? 'Yes' : 'No',
                $entry['hourly_rate'] ?: 0,
                round($total_amount, 2),
                $entry['category_name'] ?? '',
                $entry['status'],
                $entry['created_at']
            ]);
        }

        fclose($output);
    }

    /**
     * Export time entries as Excel
     */
    private function export_excel($time_entries, $filename)
    {
        // This would require PHPSpreadsheet or similar library
        // For now, fall back to CSV
        $this->export_csv($time_entries, $filename);
    }
}