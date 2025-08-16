<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Milestones Model
 * Handles all milestone-related database operations and business logic
 */
class Milestones_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get milestone by ID
     * @param int $id
     * @param bool $include_dependencies
     * @return object|null
     */
    public function get($id, $include_dependencies = false)
    {
        $this->db->select('m.*, p.name as project_name, s.firstname, s.lastname')
                 ->from(db_prefix() . 'project_milestones m')
                 ->join(db_prefix() . 'projects p', 'p.id = m.project_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = m.staff_id', 'left')
                 ->where('m.id', $id);

        $milestone = $this->db->get()->row();

        if ($milestone && $include_dependencies) {
            $milestone->dependencies = $this->get_dependencies($id);
            $milestone->dependents = $this->get_dependents($id);
        }

        return $milestone;
    }

    /**
     * Get all milestones with optional filtering
     * @param array $where
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_all($where = [], $limit = null, $offset = 0)
    {
        $this->db->select('m.*, p.name as project_name, s.firstname, s.lastname')
                 ->from(db_prefix() . 'project_milestones m')
                 ->join(db_prefix() . 'projects p', 'p.id = m.project_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = m.staff_id', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('m.due_date', 'ASC');
        $this->db->order_by('m.order_number', 'ASC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Get milestones by project ID
     * @param int $project_id
     * @param string $status
     * @return array
     */
    public function get_by_project($project_id, $status = null)
    {
        $this->db->select('m.*, s.firstname, s.lastname')
                 ->from(db_prefix() . 'project_milestones m')
                 ->join(db_prefix() . 'staff s', 's.staffid = m.staff_id', 'left')
                 ->where('m.project_id', $project_id);

        if ($status) {
            $this->db->where('m.status', $status);
        }

        $this->db->order_by('m.order_number', 'ASC');
        $this->db->order_by('m.due_date', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Create new milestone
     * @param array $data
     * @return int|bool
     */
    public function add($data)
    {
        // Validate required fields
        if (!$this->validate_milestone_data($data)) {
            return false;
        }

        // Set default values
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Set order number if not provided
        if (!isset($data['order_number'])) {
            $data['order_number'] = $this->get_next_order_number($data['project_id']);
        }

        $this->db->trans_start();

        $this->db->insert(db_prefix() . 'project_milestones', $data);
        $milestone_id = $this->db->insert_id();

        if ($milestone_id) {
            // Handle dependencies if provided
            if (isset($data['dependencies']) && is_array($data['dependencies'])) {
                $this->add_dependencies($milestone_id, $data['dependencies']);
            }

            // Log activity
            $this->log_milestone_activity($milestone_id, 'milestone_created', $data);

            // Trigger hooks
            hooks()->do_action('milestone_created', ['milestone_id' => $milestone_id, 'data' => $data]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $milestone_id;
    }

    /**
     * Update milestone
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        // Validate required fields
        if (!$this->validate_milestone_data($data, $id)) {
            return false;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'project_milestones', $data);

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            // Handle dependencies update
            if (isset($data['dependencies'])) {
                $this->update_dependencies($id, $data['dependencies']);
            }

            // Log activity
            $this->log_milestone_activity($id, 'milestone_updated', $data);

            // Trigger hooks
            hooks()->do_action('milestone_updated', ['milestone_id' => $id, 'data' => $data]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== false && $affected_rows > 0;
    }

    /**
     * Delete milestone
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // Check if milestone exists
        $milestone = $this->get($id);
        if (!$milestone) {
            return false;
        }

        // Check if milestone has dependents
        $dependents = $this->get_dependents($id);
        if (!empty($dependents)) {
            $this->session->set_flashdata('message-danger', _l('milestone_has_dependents_cannot_delete'));
            return false;
        }

        $this->db->trans_start();

        // Delete dependencies first
        $this->db->where('milestone_id', $id);
        $this->db->or_where('depends_on_milestone_id', $id);
        $this->db->delete(db_prefix() . 'milestone_dependencies');

        // Delete approvals
        $this->db->where('milestone_id', $id);
        $this->db->delete(db_prefix() . 'milestone_approvals');

        // Delete milestone
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'project_milestones');

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            // Log activity
            $this->log_milestone_activity($id, 'milestone_deleted', $milestone);

            // Trigger hooks
            hooks()->do_action('milestone_deleted', ['milestone_id' => $id, 'milestone' => $milestone]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== false && $affected_rows > 0;
    }

    /**
     * Update milestone progress
     * @param int $id
     * @param float $percentage
     * @return bool
     */
    public function update_progress($id, $percentage)
    {
        $percentage = max(0, min(100, (float)$percentage));

        $data = [
            'completion_percentage' => $percentage,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Auto-update status based on progress
        if ($percentage == 0) {
            $data['status'] = 'not_started';
        } elseif ($percentage == 100) {
            $data['status'] = 'completed';
        } elseif ($percentage > 0) {
            $data['status'] = 'in_progress';
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'project_milestones', $data);

        if ($this->db->affected_rows() > 0) {
            // Log activity
            $this->log_milestone_activity($id, 'progress_updated', ['percentage' => $percentage]);

            // Trigger hooks
            hooks()->do_action('milestone_progress_updated', ['milestone_id' => $id, 'percentage' => $percentage]);

            return true;
        }

        return false;
    }

    /**
     * Get milestone dependencies
     * @param int $milestone_id
     * @return array
     */
    public function get_dependencies($milestone_id)
    {
        $this->db->select('md.*, m.name as dependency_name, m.status as dependency_status')
                 ->from(db_prefix() . 'milestone_dependencies md')
                 ->join(db_prefix() . 'project_milestones m', 'm.id = md.depends_on_milestone_id')
                 ->where('md.milestone_id', $milestone_id);

        return $this->db->get()->result_array();
    }

    /**
     * Get milestone dependents (milestones that depend on this one)
     * @param int $milestone_id
     * @return array
     */
    public function get_dependents($milestone_id)
    {
        $this->db->select('md.*, m.name as dependent_name, m.status as dependent_status')
                 ->from(db_prefix() . 'milestone_dependencies md')
                 ->join(db_prefix() . 'project_milestones m', 'm.id = md.milestone_id')
                 ->where('md.depends_on_milestone_id', $milestone_id);

        return $this->db->get()->result_array();
    }

    /**
     * Add milestone dependencies
     * @param int $milestone_id
     * @param array $dependencies
     * @return bool
     */
    public function add_dependencies($milestone_id, $dependencies)
    {
        if (empty($dependencies)) {
            return true;
        }

        // Remove existing dependencies
        $this->db->where('milestone_id', $milestone_id);
        $this->db->delete(db_prefix() . 'milestone_dependencies');

        $insert_data = [];
        foreach ($dependencies as $dependency) {
            // Prevent circular dependencies
            if ($this->would_create_circular_dependency($milestone_id, $dependency['depends_on_milestone_id'])) {
                continue;
            }

            $insert_data[] = [
                'milestone_id' => $milestone_id,
                'depends_on_milestone_id' => $dependency['depends_on_milestone_id'],
                'dependency_type' => $dependency['dependency_type'] ?? 'finish_to_start',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }

        if (!empty($insert_data)) {
            return $this->db->insert_batch(db_prefix() . 'milestone_dependencies', $insert_data);
        }

        return true;
    }

    /**
     * Update milestone dependencies
     * @param int $milestone_id
     * @param array $dependencies
     * @return bool
     */
    public function update_dependencies($milestone_id, $dependencies)
    {
        return $this->add_dependencies($milestone_id, $dependencies);
    }

    /**
     * Check if adding dependency would create circular dependency
     * @param int $milestone_id
     * @param int $depends_on_id
     * @return bool
     */
    private function would_create_circular_dependency($milestone_id, $depends_on_id)
    {
        // Simple check: if depends_on_id depends on milestone_id (directly or indirectly)
        $visited = [];
        return $this->has_dependency_path($depends_on_id, $milestone_id, $visited);
    }

    /**
     * Check if there's a dependency path from source to target
     * @param int $source_id
     * @param int $target_id
     * @param array $visited
     * @return bool
     */
    private function has_dependency_path($source_id, $target_id, &$visited)
    {
        if ($source_id == $target_id) {
            return true;
        }

        if (in_array($source_id, $visited)) {
            return false; // Prevent infinite loops
        }

        $visited[] = $source_id;

        $dependencies = $this->get_dependencies($source_id);
        foreach ($dependencies as $dependency) {
            if ($this->has_dependency_path($dependency['depends_on_milestone_id'], $target_id, $visited)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get upcoming milestones (due within specified days)
     * @param int $days
     * @param int $project_id
     * @return array
     */
    public function get_upcoming($days = 7, $project_id = null)
    {
        $this->db->select('m.*, p.name as project_name, s.firstname, s.lastname')
                 ->from(db_prefix() . 'project_milestones m')
                 ->join(db_prefix() . 'projects p', 'p.id = m.project_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = m.staff_id', 'left')
                 ->where('m.status !=', 'completed')
                 ->where('m.status !=', 'cancelled')
                 ->where('m.due_date >=', date('Y-m-d'))
                 ->where('m.due_date <=', date('Y-m-d', strtotime("+{$days} days")));

        if ($project_id) {
            $this->db->where('m.project_id', $project_id);
        }

        $this->db->order_by('m.due_date', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get overdue milestones
     * @param int $project_id
     * @return array
     */
    public function get_overdue($project_id = null)
    {
        $this->db->select('m.*, p.name as project_name, s.firstname, s.lastname')
                 ->from(db_prefix() . 'project_milestones m')
                 ->join(db_prefix() . 'projects p', 'p.id = m.project_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = m.staff_id', 'left')
                 ->where('m.status !=', 'completed')
                 ->where('m.status !=', 'cancelled')
                 ->where('m.due_date <', date('Y-m-d'));

        if ($project_id) {
            $this->db->where('m.project_id', $project_id);
        }

        $this->db->order_by('m.due_date', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get milestone statistics for a project
     * @param int $project_id
     * @return array
     */
    public function get_project_statistics($project_id)
    {
        $stats = [
            'total' => 0,
            'not_started' => 0,
            'in_progress' => 0,
            'completed' => 0,
            'on_hold' => 0,
            'cancelled' => 0,
            'overdue' => 0,
            'avg_progress' => 0
        ];

        // Get status counts
        $this->db->select('status, COUNT(*) as count')
                 ->from(db_prefix() . 'project_milestones')
                 ->where('project_id', $project_id)
                 ->group_by('status');

        $status_counts = $this->db->get()->result_array();

        foreach ($status_counts as $status) {
            $stats[$status['status']] = (int)$status['count'];
            $stats['total'] += (int)$status['count'];
        }

        // Get overdue count
        $this->db->where('project_id', $project_id)
                 ->where('status !=', 'completed')
                 ->where('status !=', 'cancelled')
                 ->where('due_date <', date('Y-m-d'));
        $stats['overdue'] = $this->db->count_all_results(db_prefix() . 'project_milestones');

        // Get average progress
        $this->db->select_avg('completion_percentage')
                 ->from(db_prefix() . 'project_milestones')
                 ->where('project_id', $project_id);
        $avg_result = $this->db->get()->row();
        $stats['avg_progress'] = round((float)$avg_result->completion_percentage, 2);

        return $stats;
    }

    /**
     * Validate milestone data
     * @param array $data
     * @param int $id
     * @return bool
     */
    private function validate_milestone_data($data, $id = null)
    {
        // Required fields
        $required_fields = ['name', 'project_id', 'staff_id', 'start_date', 'due_date'];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->session->set_flashdata('message-danger', _l($field . '_required'));
                return false;
            }
        }

        // Validate dates
        if (strtotime($data['start_date']) > strtotime($data['due_date'])) {
            $this->session->set_flashdata('message-danger', _l('start_date_cannot_be_after_due_date'));
            return false;
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

        return true;
    }

    /**
     * Get next order number for project
     * @param int $project_id
     * @return int
     */
    private function get_next_order_number($project_id)
    {
        $this->db->select_max('order_number')
                 ->from(db_prefix() . 'project_milestones')
                 ->where('project_id', $project_id);

        $result = $this->db->get()->row();
        return ((int)$result->order_number) + 1;
    }

    /**
     * Log milestone activity
     * @param int $milestone_id
     * @param string $action
     * @param array $data
     */
    private function log_milestone_activity($milestone_id, $action, $data = [])
    {
        $milestone = $this->get($milestone_id);
        if ($milestone) {
            $description = _l($action) . ': ' . $milestone->name;
            if (!empty($data)) {
                $description .= ' - ' . json_encode($data);
            }
            
            log_activity($description);
        }
    }

    /**
     * Create default milestones for a project
     * @param int $project_id
     * @param array $templates
     * @return bool
     */
    public function create_default_milestones($project_id, $templates = null)
    {
        if (!$templates) {
            $CI = &get_instance();
            $CI->load->config('project_enhancement/module_config');
            $templates = $CI->config->item('default_milestone_templates');
        }

        if (empty($templates)) {
            return false;
        }

        $project_start_date = date('Y-m-d');
        $current_date = $project_start_date;

        foreach ($templates as $key => $template) {
            $milestone_data = [
                'project_id' => $project_id,
                'name' => $template['name'],
                'description' => $template['description'],
                'start_date' => $current_date,
                'due_date' => date('Y-m-d', strtotime($current_date . ' +2 weeks')),
                'status' => 'not_started',
                'priority' => $template['priority'],
                'staff_id' => get_staff_user_id(),
                'order_number' => $template['order']
            ];

            $this->add($milestone_data);

            // Move to next milestone start date
            $current_date = date('Y-m-d', strtotime($current_date . ' +2 weeks'));
        }

        return true;
    }
}