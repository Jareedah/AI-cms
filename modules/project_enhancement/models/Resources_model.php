<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Resources Model
 * Handles staff allocation, skills management, and availability tracking
 */
class Resources_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get resource allocation by ID
     * @param int $id
     * @return object|null
     */
    public function get($id)
    {
        $this->db->select('pr.*, p.name as project_name, s.firstname, s.lastname, s.email')
                 ->from(db_prefix() . 'project_resources pr')
                 ->join(db_prefix() . 'projects p', 'p.id = pr.project_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = pr.staff_id', 'left')
                 ->where('pr.id', $id);

        return $this->db->get()->row();
    }

    /**
     * Get all resource allocations with optional filtering
     * @param array $where
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_all($where = [], $limit = null, $offset = 0)
    {
        $this->db->select('pr.*, p.name as project_name, s.firstname, s.lastname, s.email')
                 ->from(db_prefix() . 'project_resources pr')
                 ->join(db_prefix() . 'projects p', 'p.id = pr.project_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = pr.staff_id', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('pr.start_date', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Get resource allocations by project
     * @param int $project_id
     * @param bool $active_only
     * @return array
     */
    public function get_by_project($project_id, $active_only = true)
    {
        $this->db->select('pr.*, s.firstname, s.lastname, s.email')
                 ->from(db_prefix() . 'project_resources pr')
                 ->join(db_prefix() . 'staff s', 's.staffid = pr.staff_id', 'left')
                 ->where('pr.project_id', $project_id);

        if ($active_only) {
            $this->db->where('pr.active', 1);
        }

        $this->db->order_by('pr.start_date', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Get resource allocations by staff member
     * @param int $staff_id
     * @param bool $active_only
     * @return array
     */
    public function get_by_staff($staff_id, $active_only = true)
    {
        $this->db->select('pr.*, p.name as project_name, p.status as project_status')
                 ->from(db_prefix() . 'project_resources pr')
                 ->join(db_prefix() . 'projects p', 'p.id = pr.project_id', 'left')
                 ->where('pr.staff_id', $staff_id);

        if ($active_only) {
            $this->db->where('pr.active', 1);
            $this->db->where('(pr.end_date IS NULL OR pr.end_date >= CURDATE())', null, false);
        }

        $this->db->order_by('pr.start_date', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Add new resource allocation
     * @param array $data
     * @return int|bool
     */
    public function add($data)
    {
        // Validate required fields
        if (!$this->validate_resource_data($data)) {
            return false;
        }

        // Check for conflicts
        if ($this->has_allocation_conflict($data['staff_id'], $data['project_id'], $data['start_date'], $data['end_date'] ?? null)) {
            $this->session->set_flashdata('message-danger', _l('resource_allocation_conflict'));
            return false;
        }

        // Set default values
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $this->db->insert(db_prefix() . 'project_resources', $data);
        $resource_id = $this->db->insert_id();

        if ($resource_id) {
            // Log activity
            $this->log_resource_activity($resource_id, 'resource_allocated', $data);

            // Trigger hooks
            hooks()->do_action('resource_allocated', ['resource_id' => $resource_id, 'data' => $data]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $resource_id;
    }

    /**
     * Update resource allocation
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        // Validate required fields
        if (!$this->validate_resource_data($data, $id)) {
            return false;
        }

        // Get current allocation for conflict checking
        $current = $this->get($id);
        if (!$current) {
            return false;
        }

        // Check for conflicts (excluding current allocation)
        if (isset($data['start_date']) || isset($data['end_date'])) {
            $start_date = $data['start_date'] ?? $current->start_date;
            $end_date = $data['end_date'] ?? $current->end_date;
            
            if ($this->has_allocation_conflict($current->staff_id, $current->project_id, $start_date, $end_date, $id)) {
                $this->session->set_flashdata('message-danger', _l('resource_allocation_conflict'));
                return false;
            }
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'project_resources', $data);

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            // Log activity
            $this->log_resource_activity($id, 'resource_updated', $data);

            // Trigger hooks
            hooks()->do_action('resource_updated', ['resource_id' => $id, 'data' => $data]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== false && $affected_rows > 0;
    }

    /**
     * Delete resource allocation
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // Check if resource allocation exists
        $resource = $this->get($id);
        if (!$resource) {
            return false;
        }

        $this->db->trans_start();

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'project_resources');

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            // Log activity
            $this->log_resource_activity($id, 'resource_removed', $resource);

            // Trigger hooks
            hooks()->do_action('resource_removed', ['resource_id' => $id, 'resource' => $resource]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== false && $affected_rows > 0;
    }

    /**
     * Check for allocation conflicts
     * @param int $staff_id
     * @param int $project_id
     * @param string $start_date
     * @param string $end_date
     * @param int $exclude_id
     * @return bool
     */
    private function has_allocation_conflict($staff_id, $project_id, $start_date, $end_date = null, $exclude_id = null)
    {
        // Check if staff is already allocated to the same project
        $this->db->where('staff_id', $staff_id)
                 ->where('project_id', $project_id)
                 ->where('active', 1);

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        $existing = $this->db->count_all_results(db_prefix() . 'project_resources');

        if ($existing > 0) {
            return true; // Already allocated to this project
        }

        // Check for overlapping allocations with other projects
        $this->db->where('staff_id', $staff_id)
                 ->where('active', 1);

        if ($exclude_id) {
            $this->db->where('id !=', $exclude_id);
        }

        // Date overlap logic
        if ($end_date) {
            $this->db->where('(start_date <= "' . $end_date . '" AND (end_date IS NULL OR end_date >= "' . $start_date . '"))', null, false);
        } else {
            $this->db->where('(end_date IS NULL OR end_date >= "' . $start_date . '")', null, false);
        }

        $overlapping = $this->db->get(db_prefix() . 'project_resources')->result_array();

        // Check if total allocation percentage exceeds 100%
        $total_allocation = 0;
        foreach ($overlapping as $allocation) {
            $total_allocation += (float)$allocation['allocation_percentage'];
        }

        return $total_allocation >= 100;
    }

    /**
     * Get staff availability for a date range
     * @param int $staff_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public function get_staff_availability($staff_id, $start_date, $end_date)
    {
        $availability = [];
        $current_date = $start_date;

        while ($current_date <= $end_date) {
            // Get availability record for this date
            $this->db->select('*')
                     ->from(db_prefix() . 'resource_availability')
                     ->where('staff_id', $staff_id)
                     ->where('date', $current_date);

            $day_availability = $this->db->get()->row();

            if ($day_availability) {
                $available_hours = (float)$day_availability->available_hours;
                $reason = $day_availability->unavailable_reason;
            } else {
                // Default working hours
                $available_hours = (float)get_option('project_enhancement_working_hours_per_day', 8);
                $reason = null;
            }

            // Get current allocations for this date
            $allocations = $this->get_staff_allocations_for_date($staff_id, $current_date);
            
            $allocated_percentage = 0;
            foreach ($allocations as $allocation) {
                $allocated_percentage += (float)$allocation['allocation_percentage'];
            }

            $available_percentage = 100 - $allocated_percentage;
            $available_hours_actual = ($available_percentage / 100) * $available_hours;

            $availability[$current_date] = [
                'date' => $current_date,
                'total_hours' => $available_hours,
                'allocated_percentage' => $allocated_percentage,
                'available_percentage' => $available_percentage,
                'available_hours' => max(0, $available_hours_actual),
                'unavailable_reason' => $reason,
                'allocations' => $allocations
            ];

            $current_date = date('Y-m-d', strtotime($current_date . ' +1 day'));
        }

        return $availability;
    }

    /**
     * Get staff allocations for a specific date
     * @param int $staff_id
     * @param string $date
     * @return array
     */
    private function get_staff_allocations_for_date($staff_id, $date)
    {
        $this->db->select('pr.*, p.name as project_name')
                 ->from(db_prefix() . 'project_resources pr')
                 ->join(db_prefix() . 'projects p', 'p.id = pr.project_id', 'left')
                 ->where('pr.staff_id', $staff_id)
                 ->where('pr.active', 1)
                 ->where('pr.start_date <=', $date)
                 ->where('(pr.end_date IS NULL OR pr.end_date >=)', $date);

        return $this->db->get()->result_array();
    }

    /**
     * Set staff availability for a date
     * @param int $staff_id
     * @param string $date
     * @param float $available_hours
     * @param string $reason
     * @return bool
     */
    public function set_availability($staff_id, $date, $available_hours, $reason = null)
    {
        $data = [
            'staff_id' => $staff_id,
            'date' => $date,
            'available_hours' => $available_hours,
            'unavailable_reason' => $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Check if record exists
        $this->db->where('staff_id', $staff_id)
                 ->where('date', $date);
        $existing = $this->db->get(db_prefix() . 'resource_availability')->row();

        if ($existing) {
            // Update existing record
            $this->db->where('staff_id', $staff_id)
                     ->where('date', $date);
            $this->db->update(db_prefix() . 'resource_availability', $data);
        } else {
            // Insert new record
            $data['created_at'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'resource_availability', $data);
        }

        return $this->db->affected_rows() > 0;
    }

    /**
     * Get resource utilization statistics
     * @param array $filters
     * @return array
     */
    public function get_utilization_stats($filters = [])
    {
        $stats = [
            'total_staff' => 0,
            'allocated_staff' => 0,
            'over_allocated_staff' => 0,
            'under_allocated_staff' => 0,
            'avg_utilization' => 0,
            'by_project' => [],
            'by_staff' => []
        ];

        // Get all active staff
        $this->db->select('staffid, firstname, lastname')
                 ->from(db_prefix() . 'staff')
                 ->where('active', 1);

        if (isset($filters['department_id'])) {
            $this->db->where('departmentid', $filters['department_id']);
        }

        $staff_members = $this->db->get()->result_array();
        $stats['total_staff'] = count($staff_members);

        $date_from = $filters['date_from'] ?? date('Y-m-d');
        $date_to = $filters['date_to'] ?? date('Y-m-d', strtotime('+30 days'));

        foreach ($staff_members as $staff) {
            $staff_id = $staff['staffid'];
            $staff_name = $staff['firstname'] . ' ' . $staff['lastname'];

            // Get current allocations
            $this->db->select('pr.*, p.name as project_name')
                     ->from(db_prefix() . 'project_resources pr')
                     ->join(db_prefix() . 'projects p', 'p.id = pr.project_id', 'left')
                     ->where('pr.staff_id', $staff_id)
                     ->where('pr.active', 1)
                     ->where('pr.start_date <=', $date_to)
                     ->where('(pr.end_date IS NULL OR pr.end_date >=)', $date_from);

            $allocations = $this->db->get()->result_array();

            $total_allocation = 0;
            $project_allocations = [];

            foreach ($allocations as $allocation) {
                $percentage = (float)$allocation['allocation_percentage'];
                $total_allocation += $percentage;

                $project_name = $allocation['project_name'];
                if (!isset($project_allocations[$project_name])) {
                    $project_allocations[$project_name] = 0;
                }
                $project_allocations[$project_name] += $percentage;

                // By project stats
                if (!isset($stats['by_project'][$project_name])) {
                    $stats['by_project'][$project_name] = [
                        'staff_count' => 0,
                        'total_allocation' => 0,
                        'avg_allocation' => 0
                    ];
                }
                $stats['by_project'][$project_name]['staff_count']++;
                $stats['by_project'][$project_name]['total_allocation'] += $percentage;
            }

            // Staff utilization categorization
            if ($total_allocation > 0) {
                $stats['allocated_staff']++;
                
                if ($total_allocation > 100) {
                    $stats['over_allocated_staff']++;
                } elseif ($total_allocation < 80) {
                    $stats['under_allocated_staff']++;
                }
            }

            $stats['by_staff'][$staff_name] = [
                'total_allocation' => $total_allocation,
                'project_count' => count($allocations),
                'projects' => $project_allocations,
                'status' => $this->get_utilization_status($total_allocation)
            ];
        }

        // Calculate averages
        if ($stats['total_staff'] > 0) {
            $total_utilization = array_sum(array_column($stats['by_staff'], 'total_allocation'));
            $stats['avg_utilization'] = round($total_utilization / $stats['total_staff'], 2);
        }

        foreach ($stats['by_project'] as $project => &$project_stats) {
            if ($project_stats['staff_count'] > 0) {
                $project_stats['avg_allocation'] = round($project_stats['total_allocation'] / $project_stats['staff_count'], 2);
            }
        }

        return $stats;
    }

    /**
     * Get utilization status based on percentage
     * @param float $percentage
     * @return string
     */
    private function get_utilization_status($percentage)
    {
        if ($percentage == 0) {
            return 'unallocated';
        } elseif ($percentage < 50) {
            return 'under_utilized';
        } elseif ($percentage <= 100) {
            return 'optimal';
        } else {
            return 'over_allocated';
        }
    }

    /**
     * Find available staff for a project
     * @param array $requirements
     * @return array
     */
    public function find_available_staff($requirements = [])
    {
        $start_date = $requirements['start_date'] ?? date('Y-m-d');
        $end_date = $requirements['end_date'] ?? null;
        $required_skills = $requirements['skills'] ?? [];
        $min_availability = $requirements['min_availability'] ?? 20; // Minimum percentage availability
        $department_id = $requirements['department_id'] ?? null;

        // Get all active staff
        $this->db->select('s.staffid, s.firstname, s.lastname, s.email, s.departmentid')
                 ->from(db_prefix() . 'staff s')
                 ->where('s.active', 1);

        if ($department_id) {
            $this->db->where('s.departmentid', $department_id);
        }

        $staff_members = $this->db->get()->result_array();
        $available_staff = [];

        foreach ($staff_members as $staff) {
            $staff_id = $staff['staffid'];

            // Check skills match if required
            if (!empty($required_skills)) {
                $staff_skills = $this->get_staff_skills($staff_id);
                $skill_match = $this->check_skill_match($staff_skills, $required_skills);
                
                if (!$skill_match['matches']) {
                    continue;
                }
                $staff['skill_match'] = $skill_match;
            }

            // Check availability
            $availability = $this->get_staff_availability($staff_id, $start_date, $end_date ?: $start_date);
            $avg_availability = $this->calculate_average_availability($availability);

            if ($avg_availability >= $min_availability) {
                $staff['availability'] = $availability;
                $staff['avg_availability'] = $avg_availability;
                $available_staff[] = $staff;
            }
        }

        // Sort by availability (highest first)
        usort($available_staff, function($a, $b) {
            return $b['avg_availability'] <=> $a['avg_availability'];
        });

        return $available_staff;
    }

    /**
     * Get staff skills
     * @param int $staff_id
     * @return array
     */
    public function get_staff_skills($staff_id)
    {
        $this->db->select('ss.*, verifier.firstname as verifier_firstname, verifier.lastname as verifier_lastname')
                 ->from(db_prefix() . 'staff_skills ss')
                 ->join(db_prefix() . 'staff verifier', 'verifier.staffid = ss.verified_by', 'left')
                 ->where('ss.staff_id', $staff_id)
                 ->order_by('ss.skill_name', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Add staff skill
     * @param array $data
     * @return int|bool
     */
    public function add_skill($data)
    {
        // Check if skill already exists for this staff member
        $this->db->where('staff_id', $data['staff_id'])
                 ->where('skill_name', $data['skill_name']);
        
        if ($this->db->count_all_results(db_prefix() . 'staff_skills') > 0) {
            $this->session->set_flashdata('message-danger', _l('skill_already_exists'));
            return false;
        }

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'staff_skills', $data);
        return $this->db->insert_id();
    }

    /**
     * Update staff skill
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update_skill($id, $data)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'staff_skills', $data);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Delete staff skill
     * @param int $id
     * @return bool
     */
    public function delete_skill($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'staff_skills');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Check skill match
     * @param array $staff_skills
     * @param array $required_skills
     * @return array
     */
    private function check_skill_match($staff_skills, $required_skills)
    {
        $staff_skill_names = array_column($staff_skills, 'skill_name');
        $matched_skills = [];
        $missing_skills = [];

        foreach ($required_skills as $required_skill) {
            if (in_array($required_skill, $staff_skill_names)) {
                $matched_skills[] = $required_skill;
            } else {
                $missing_skills[] = $required_skill;
            }
        }

        $match_percentage = count($required_skills) > 0 ? (count($matched_skills) / count($required_skills)) * 100 : 100;

        return [
            'matches' => count($matched_skills) > 0,
            'match_percentage' => round($match_percentage, 2),
            'matched_skills' => $matched_skills,
            'missing_skills' => $missing_skills
        ];
    }

    /**
     * Calculate average availability
     * @param array $availability
     * @return float
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
     * Validate resource data
     * @param array $data
     * @param int $id
     * @return bool
     */
    private function validate_resource_data($data, $id = null)
    {
        // Required fields
        $required_fields = ['project_id', 'staff_id', 'role', 'start_date', 'allocation_percentage'];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->session->set_flashdata('message-danger', _l($field . '_required'));
                return false;
            }
        }

        // Validate allocation percentage
        if ($data['allocation_percentage'] < 0 || $data['allocation_percentage'] > 100) {
            $this->session->set_flashdata('message-danger', _l('invalid_allocation_percentage'));
            return false;
        }

        // Validate dates
        if (isset($data['end_date']) && !empty($data['end_date'])) {
            if (strtotime($data['start_date']) > strtotime($data['end_date'])) {
                $this->session->set_flashdata('message-danger', _l('start_date_cannot_be_after_end_date'));
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

        return true;
    }

    /**
     * Log resource activity
     * @param int $resource_id
     * @param string $action
     * @param array $data
     */
    private function log_resource_activity($resource_id, $action, $data = [])
    {
        $resource = $this->get($resource_id);
        if ($resource) {
            $description = _l($action) . ': ' . $resource->firstname . ' ' . $resource->lastname . ' - ' . $resource->project_name;
            if (!empty($data)) {
                $description .= ' - ' . json_encode($data);
            }
            
            log_activity($description);
        }
    }

    /**
     * Get workload forecast
     * @param int $staff_id
     * @param int $days_ahead
     * @return array
     */
    public function get_workload_forecast($staff_id, $days_ahead = 30)
    {
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime("+{$days_ahead} days"));

        return $this->get_staff_availability($staff_id, $start_date, $end_date);
    }

    /**
     * Optimize resource allocation
     * @param int $project_id
     * @param array $requirements
     * @return array
     */
    public function optimize_allocation($project_id, $requirements = [])
    {
        // This is a simplified optimization algorithm
        // In a real-world scenario, this could be much more complex
        
        $available_staff = $this->find_available_staff($requirements);
        $optimized_allocation = [];

        $total_required_percentage = $requirements['total_percentage'] ?? 100;
        $staff_count = count($available_staff);

        if ($staff_count == 0) {
            return ['success' => false, 'message' => 'No available staff found'];
        }

        $percentage_per_staff = round($total_required_percentage / $staff_count, 2);

        foreach ($available_staff as $staff) {
            $allocation_percentage = min($percentage_per_staff, $staff['avg_availability']);
            
            $optimized_allocation[] = [
                'staff_id' => $staff['staffid'],
                'staff_name' => $staff['firstname'] . ' ' . $staff['lastname'],
                'allocation_percentage' => $allocation_percentage,
                'available_percentage' => $staff['avg_availability'],
                'skill_match' => $staff['skill_match'] ?? null
            ];

            $total_required_percentage -= $allocation_percentage;
            
            if ($total_required_percentage <= 0) {
                break;
            }
        }

        return [
            'success' => true,
            'allocations' => $optimized_allocation,
            'remaining_percentage' => max(0, $total_required_percentage)
        ];
    }
}