<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Time Tracking Model
 * Handles all time tracking related database operations and business logic
 */
class Time_tracking_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get time entry by ID
     * @param int $id
     * @return object|null
     */
    public function get($id)
    {
        $this->db->select('te.*, p.name as project_name, t.name as task_name, m.name as milestone_name, 
                          s.firstname, s.lastname, tc.name as category_name, tc.color_code as category_color,
                          approver.firstname as approver_firstname, approver.lastname as approver_lastname')
                 ->from(db_prefix() . 'time_entries te')
                 ->join(db_prefix() . 'projects p', 'p.id = te.project_id', 'left')
                 ->join(db_prefix() . 'tasks t', 't.id = te.task_id', 'left')
                 ->join(db_prefix() . 'project_milestones m', 'm.id = te.milestone_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = te.staff_id', 'left')
                 ->join(db_prefix() . 'time_categories tc', 'tc.id = te.category_id', 'left')
                 ->join(db_prefix() . 'staff approver', 'approver.staffid = te.approved_by', 'left')
                 ->where('te.id', $id);

        return $this->db->get()->row();
    }

    /**
     * Get all time entries with optional filtering
     * @param array $where
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_all($where = [], $limit = null, $offset = 0)
    {
        $this->db->select('te.*, p.name as project_name, t.name as task_name, m.name as milestone_name, 
                          s.firstname, s.lastname, tc.name as category_name, tc.color_code as category_color')
                 ->from(db_prefix() . 'time_entries te')
                 ->join(db_prefix() . 'projects p', 'p.id = te.project_id', 'left')
                 ->join(db_prefix() . 'tasks t', 't.id = te.task_id', 'left')
                 ->join(db_prefix() . 'project_milestones m', 'm.id = te.milestone_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = te.staff_id', 'left')
                 ->join(db_prefix() . 'time_categories tc', 'tc.id = te.category_id', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('te.start_time', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Get time entries by project
     * @param int $project_id
     * @param string $status
     * @param int $staff_id
     * @return array
     */
    public function get_by_project($project_id, $status = null, $staff_id = null)
    {
        $this->db->select('te.*, t.name as task_name, m.name as milestone_name, 
                          s.firstname, s.lastname, tc.name as category_name, tc.color_code as category_color')
                 ->from(db_prefix() . 'time_entries te')
                 ->join(db_prefix() . 'tasks t', 't.id = te.task_id', 'left')
                 ->join(db_prefix() . 'project_milestones m', 'm.id = te.milestone_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = te.staff_id', 'left')
                 ->join(db_prefix() . 'time_categories tc', 'tc.id = te.category_id', 'left')
                 ->where('te.project_id', $project_id);

        if ($status) {
            $this->db->where('te.status', $status);
        }

        if ($staff_id) {
            $this->db->where('te.staff_id', $staff_id);
        }

        $this->db->order_by('te.start_time', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Get time entries by staff member
     * @param int $staff_id
     * @param string $date_from
     * @param string $date_to
     * @return array
     */
    public function get_by_staff($staff_id, $date_from = null, $date_to = null)
    {
        $this->db->select('te.*, p.name as project_name, t.name as task_name, m.name as milestone_name, 
                          tc.name as category_name, tc.color_code as category_color')
                 ->from(db_prefix() . 'time_entries te')
                 ->join(db_prefix() . 'projects p', 'p.id = te.project_id', 'left')
                 ->join(db_prefix() . 'tasks t', 't.id = te.task_id', 'left')
                 ->join(db_prefix() . 'project_milestones m', 'm.id = te.milestone_id', 'left')
                 ->join(db_prefix() . 'time_categories tc', 'tc.id = te.category_id', 'left')
                 ->where('te.staff_id', $staff_id);

        if ($date_from) {
            $this->db->where('DATE(te.start_time) >=', $date_from);
        }

        if ($date_to) {
            $this->db->where('DATE(te.start_time) <=', $date_to);
        }

        $this->db->order_by('te.start_time', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Add new time entry
     * @param array $data
     * @return int|bool
     */
    public function add($data)
    {
        // Validate required fields
        if (!$this->validate_time_entry_data($data)) {
            return false;
        }

        // Calculate duration if not provided
        if (!isset($data['duration_minutes']) && isset($data['start_time']) && isset($data['end_time'])) {
            $data['duration_minutes'] = $this->calculate_duration($data['start_time'], $data['end_time']);
        }

        // Set default values
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Set default hourly rate if not provided
        if (!isset($data['hourly_rate']) || empty($data['hourly_rate'])) {
            $data['hourly_rate'] = $this->get_staff_hourly_rate($data['staff_id']);
        }

        $this->db->trans_start();

        $this->db->insert(db_prefix() . 'time_entries', $data);
        $time_entry_id = $this->db->insert_id();

        if ($time_entry_id) {
            // Log activity
            $this->log_time_entry_activity($time_entry_id, 'time_entry_created', $data);

            // Trigger hooks
            hooks()->do_action('time_entry_created', ['time_entry_id' => $time_entry_id, 'data' => $data]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $time_entry_id;
    }

    /**
     * Update time entry
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        // Validate required fields
        if (!$this->validate_time_entry_data($data, $id)) {
            return false;
        }

        // Recalculate duration if times changed
        if (isset($data['start_time']) && isset($data['end_time'])) {
            $data['duration_minutes'] = $this->calculate_duration($data['start_time'], $data['end_time']);
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'time_entries', $data);

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            // Log activity
            $this->log_time_entry_activity($id, 'time_entry_updated', $data);

            // Trigger hooks
            hooks()->do_action('time_entry_updated', ['time_entry_id' => $id, 'data' => $data]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== false && $affected_rows > 0;
    }

    /**
     * Delete time entry
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // Check if time entry exists
        $time_entry = $this->get($id);
        if (!$time_entry) {
            return false;
        }

        // Check if time entry is already invoiced
        if ($time_entry->invoice_id) {
            $this->session->set_flashdata('message-danger', _l('cannot_delete_invoiced_time_entry'));
            return false;
        }

        $this->db->trans_start();

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'time_entries');

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            // Log activity
            $this->log_time_entry_activity($id, 'time_entry_deleted', $time_entry);

            // Trigger hooks
            hooks()->do_action('time_entry_deleted', ['time_entry_id' => $id, 'time_entry' => $time_entry]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== false && $affected_rows > 0;
    }

    /**
     * Start timer for a project/task
     * @param array $data
     * @return int|bool
     */
    public function start_timer($data)
    {
        // Check if user already has a running timer
        $running_timer = $this->get_running_timer($data['staff_id']);
        if ($running_timer) {
            $this->session->set_flashdata('message-danger', _l('timer_already_running'));
            return false;
        }

        $timer_data = [
            'project_id' => $data['project_id'],
            'task_id' => $data['task_id'] ?? null,
            'milestone_id' => $data['milestone_id'] ?? null,
            'staff_id' => $data['staff_id'],
            'start_time' => date('Y-m-d H:i:s'),
            'description' => $data['description'] ?? '',
            'billable' => $data['billable'] ?? 1,
            'category_id' => $data['category_id'] ?? null,
            'status' => 'draft'
        ];

        return $this->add($timer_data);
    }

    /**
     * Stop timer
     * @param int $time_entry_id
     * @param string $end_time
     * @return bool
     */
    public function stop_timer($time_entry_id, $end_time = null)
    {
        if (!$end_time) {
            $end_time = date('Y-m-d H:i:s');
        }

        $time_entry = $this->get($time_entry_id);
        if (!$time_entry || $time_entry->end_time) {
            return false;
        }

        $duration_minutes = $this->calculate_duration($time_entry->start_time, $end_time);

        $update_data = [
            'end_time' => $end_time,
            'duration_minutes' => $duration_minutes
        ];

        return $this->update($time_entry_id, $update_data);
    }

    /**
     * Get running timer for staff member
     * @param int $staff_id
     * @return object|null
     */
    public function get_running_timer($staff_id)
    {
        $this->db->select('te.*, p.name as project_name, t.name as task_name, m.name as milestone_name')
                 ->from(db_prefix() . 'time_entries te')
                 ->join(db_prefix() . 'projects p', 'p.id = te.project_id', 'left')
                 ->join(db_prefix() . 'tasks t', 't.id = te.task_id', 'left')
                 ->join(db_prefix() . 'project_milestones m', 'm.id = te.milestone_id', 'left')
                 ->where('te.staff_id', $staff_id)
                 ->where('te.end_time IS NULL', null, false);

        return $this->db->get()->row();
    }

    /**
     * Submit time entry for approval
     * @param int $id
     * @return bool
     */
    public function submit_for_approval($id)
    {
        $time_entry = $this->get($id);
        if (!$time_entry || $time_entry->status != 'draft') {
            return false;
        }

        $update_data = [
            'status' => 'submitted'
        ];

        if ($this->update($id, $update_data)) {
            // Trigger approval notification
            hooks()->do_action('time_entry_submitted_for_approval', ['time_entry_id' => $id]);
            return true;
        }

        return false;
    }

    /**
     * Approve time entry
     * @param int $id
     * @param int $approver_id
     * @param string $comments
     * @return bool
     */
    public function approve($id, $approver_id, $comments = '')
    {
        $time_entry = $this->get($id);
        if (!$time_entry || $time_entry->status != 'submitted') {
            return false;
        }

        $update_data = [
            'status' => 'approved',
            'approved_by' => $approver_id,
            'approved_at' => date('Y-m-d H:i:s')
        ];

        if ($this->update($id, $update_data)) {
            // Log approval activity
            $this->log_time_entry_activity($id, 'time_entry_approved', ['approver_id' => $approver_id, 'comments' => $comments]);

            // Trigger hooks
            hooks()->do_action('time_entry_approved', ['time_entry_id' => $id, 'approver_id' => $approver_id]);

            return true;
        }

        return false;
    }

    /**
     * Reject time entry
     * @param int $id
     * @param int $approver_id
     * @param string $comments
     * @return bool
     */
    public function reject($id, $approver_id, $comments = '')
    {
        $time_entry = $this->get($id);
        if (!$time_entry || $time_entry->status != 'submitted') {
            return false;
        }

        $update_data = [
            'status' => 'rejected',
            'approved_by' => $approver_id,
            'approved_at' => date('Y-m-d H:i:s')
        ];

        if ($this->update($id, $update_data)) {
            // Log rejection activity
            $this->log_time_entry_activity($id, 'time_entry_rejected', ['approver_id' => $approver_id, 'comments' => $comments]);

            // Trigger hooks
            hooks()->do_action('time_entry_rejected', ['time_entry_id' => $id, 'approver_id' => $approver_id]);

            return true;
        }

        return false;
    }

    /**
     * Get time entries pending approval
     * @param int $approver_id
     * @return array
     */
    public function get_pending_approval($approver_id = null)
    {
        $this->db->select('te.*, p.name as project_name, t.name as task_name, m.name as milestone_name, 
                          s.firstname, s.lastname, tc.name as category_name')
                 ->from(db_prefix() . 'time_entries te')
                 ->join(db_prefix() . 'projects p', 'p.id = te.project_id', 'left')
                 ->join(db_prefix() . 'tasks t', 't.id = te.task_id', 'left')
                 ->join(db_prefix() . 'project_milestones m', 'm.id = te.milestone_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = te.staff_id', 'left')
                 ->join(db_prefix() . 'time_categories tc', 'tc.id = te.category_id', 'left')
                 ->where('te.status', 'submitted');

        if ($approver_id) {
            // Add logic for specific approver if needed
        }

        $this->db->order_by('te.created_at', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get time tracking statistics
     * @param array $filters
     * @return array
     */
    public function get_statistics($filters = [])
    {
        $stats = [
            'total_entries' => 0,
            'total_hours' => 0,
            'billable_hours' => 0,
            'non_billable_hours' => 0,
            'total_amount' => 0,
            'approved_hours' => 0,
            'pending_hours' => 0,
            'by_category' => [],
            'by_project' => [],
            'by_staff' => []
        ];

        // Build base query
        $this->db->select('te.*, tc.name as category_name, p.name as project_name, s.firstname, s.lastname')
                 ->from(db_prefix() . 'time_entries te')
                 ->join(db_prefix() . 'time_categories tc', 'tc.id = te.category_id', 'left')
                 ->join(db_prefix() . 'projects p', 'p.id = te.project_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = te.staff_id', 'left');

        // Apply filters
        if (isset($filters['project_id'])) {
            $this->db->where('te.project_id', $filters['project_id']);
        }

        if (isset($filters['staff_id'])) {
            $this->db->where('te.staff_id', $filters['staff_id']);
        }

        if (isset($filters['date_from'])) {
            $this->db->where('DATE(te.start_time) >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $this->db->where('DATE(te.start_time) <=', $filters['date_to']);
        }

        if (isset($filters['status'])) {
            $this->db->where('te.status', $filters['status']);
        }

        $time_entries = $this->db->get()->result_array();

        // Calculate statistics
        foreach ($time_entries as $entry) {
            $hours = $entry['duration_minutes'] / 60;
            $amount = $hours * $entry['hourly_rate'];

            $stats['total_entries']++;
            $stats['total_hours'] += $hours;

            if ($entry['billable']) {
                $stats['billable_hours'] += $hours;
                $stats['total_amount'] += $amount;
            } else {
                $stats['non_billable_hours'] += $hours;
            }

            if ($entry['status'] == 'approved') {
                $stats['approved_hours'] += $hours;
            } elseif ($entry['status'] == 'submitted') {
                $stats['pending_hours'] += $hours;
            }

            // By category
            $category = $entry['category_name'] ?? 'Uncategorized';
            if (!isset($stats['by_category'][$category])) {
                $stats['by_category'][$category] = ['hours' => 0, 'amount' => 0];
            }
            $stats['by_category'][$category]['hours'] += $hours;
            $stats['by_category'][$category]['amount'] += $amount;

            // By project
            $project = $entry['project_name'] ?? 'Unknown Project';
            if (!isset($stats['by_project'][$project])) {
                $stats['by_project'][$project] = ['hours' => 0, 'amount' => 0];
            }
            $stats['by_project'][$project]['hours'] += $hours;
            $stats['by_project'][$project]['amount'] += $amount;

            // By staff
            $staff = $entry['firstname'] . ' ' . $entry['lastname'];
            if (!isset($stats['by_staff'][$staff])) {
                $stats['by_staff'][$staff] = ['hours' => 0, 'amount' => 0];
            }
            $stats['by_staff'][$staff]['hours'] += $hours;
            $stats['by_staff'][$staff]['amount'] += $amount;
        }

        // Round values
        $stats['total_hours'] = round($stats['total_hours'], 2);
        $stats['billable_hours'] = round($stats['billable_hours'], 2);
        $stats['non_billable_hours'] = round($stats['non_billable_hours'], 2);
        $stats['total_amount'] = round($stats['total_amount'], 2);
        $stats['approved_hours'] = round($stats['approved_hours'], 2);
        $stats['pending_hours'] = round($stats['pending_hours'], 2);

        return $stats;
    }

    /**
     * Calculate duration between two times in minutes
     * @param string $start_time
     * @param string $end_time
     * @return int
     */
    private function calculate_duration($start_time, $end_time)
    {
        $start = new DateTime($start_time);
        $end = new DateTime($end_time);
        $diff = $end->diff($start);

        return ($diff->days * 24 * 60) + ($diff->h * 60) + $diff->i;
    }

    /**
     * Get staff hourly rate
     * @param int $staff_id
     * @return float
     */
    private function get_staff_hourly_rate($staff_id)
    {
        // Try to get from staff table if hourly_rate field exists
        $this->db->select('hourly_rate')
                 ->from(db_prefix() . 'staff')
                 ->where('staffid', $staff_id);

        $staff = $this->db->get()->row();

        if ($staff && isset($staff->hourly_rate) && $staff->hourly_rate > 0) {
            return (float)$staff->hourly_rate;
        }

        // Fallback to default rate
        return (float)get_option('project_enhancement_default_hourly_rate', 50.00);
    }

    /**
     * Validate time entry data
     * @param array $data
     * @param int $id
     * @return bool
     */
    private function validate_time_entry_data($data, $id = null)
    {
        // Required fields
        $required_fields = ['project_id', 'staff_id', 'start_time'];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->session->set_flashdata('message-danger', _l($field . '_required'));
                return false;
            }
        }

        // Validate project exists
        $this->db->where('id', $data['project_id']);
        if ($this->db->count_all_results(db_prefix() . 'projects') == 0) {
            $this->session->set_flashdata('message-danger', _l('project_not_found'));
            return false;
        }

        // Validate staff exists
        $this->db->where('staffid', $data['staff_id']);
        if ($this->db->count_all_results(db_prefix() . 'staff') == 0) {
            $this->session->set_flashdata('message-danger', _l('staff_not_found'));
            return false;
        }

        // Validate end time is after start time
        if (isset($data['end_time']) && !empty($data['end_time'])) {
            if (strtotime($data['start_time']) >= strtotime($data['end_time'])) {
                $this->session->set_flashdata('message-danger', _l('end_time_must_be_after_start_time'));
                return false;
            }
        }

        return true;
    }

    /**
     * Log time entry activity
     * @param int $time_entry_id
     * @param string $action
     * @param array $data
     */
    private function log_time_entry_activity($time_entry_id, $action, $data = [])
    {
        $time_entry = $this->get($time_entry_id);
        if ($time_entry) {
            $description = _l($action) . ': ' . $time_entry->project_name;
            if (!empty($data)) {
                $description .= ' - ' . json_encode($data);
            }
            
            log_activity($description);
        }
    }

    /**
     * Get time categories
     * @return array
     */
    public function get_categories()
    {
        $this->db->select('*')
                 ->from(db_prefix() . 'time_categories')
                 ->where('active', 1)
                 ->order_by('name', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Mark time entries as invoiced
     * @param array $time_entry_ids
     * @param int $invoice_id
     * @return bool
     */
    public function mark_as_invoiced($time_entry_ids, $invoice_id)
    {
        if (empty($time_entry_ids)) {
            return false;
        }

        $this->db->where_in('id', $time_entry_ids);
        $this->db->update(db_prefix() . 'time_entries', ['invoice_id' => $invoice_id]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Get billable time entries for invoice
     * @param int $project_id
     * @param int $client_id
     * @return array
     */
    public function get_billable_for_invoice($project_id = null, $client_id = null)
    {
        $this->db->select('te.*, p.name as project_name, s.firstname, s.lastname, tc.name as category_name')
                 ->from(db_prefix() . 'time_entries te')
                 ->join(db_prefix() . 'projects p', 'p.id = te.project_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = te.staff_id', 'left')
                 ->join(db_prefix() . 'time_categories tc', 'tc.id = te.category_id', 'left')
                 ->where('te.billable', 1)
                 ->where('te.status', 'approved')
                 ->where('te.invoice_id IS NULL', null, false);

        if ($project_id) {
            $this->db->where('te.project_id', $project_id);
        }

        if ($client_id) {
            $this->db->where('p.clientid', $client_id);
        }

        $this->db->order_by('te.start_time', 'ASC');

        return $this->db->get()->result_array();
    }
}