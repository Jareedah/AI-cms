<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Budget Model
 * Handles project budget tracking, transactions, and financial management
 */
class Budget_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get budget by ID
     * @param int $id
     * @return object|null
     */
    public function get($id)
    {
        $this->db->select('pb.*, p.name as project_name, 
                          creator.firstname as creator_firstname, creator.lastname as creator_lastname,
                          approver.firstname as approver_firstname, approver.lastname as approver_lastname')
                 ->from(db_prefix() . 'project_budgets pb')
                 ->join(db_prefix() . 'projects p', 'p.id = pb.project_id', 'left')
                 ->join(db_prefix() . 'staff creator', 'creator.staffid = pb.created_by', 'left')
                 ->join(db_prefix() . 'staff approver', 'approver.staffid = pb.approved_by', 'left')
                 ->where('pb.id', $id);

        return $this->db->get()->row();
    }

    /**
     * Get all budgets with optional filtering
     * @param array $where
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function get_all($where = [], $limit = null, $offset = 0)
    {
        $this->db->select('pb.*, p.name as project_name, 
                          creator.firstname as creator_firstname, creator.lastname as creator_lastname')
                 ->from(db_prefix() . 'project_budgets pb')
                 ->join(db_prefix() . 'projects p', 'p.id = pb.project_id', 'left')
                 ->join(db_prefix() . 'staff creator', 'creator.staffid = pb.created_by', 'left');

        if (!empty($where)) {
            $this->db->where($where);
        }

        $this->db->order_by('pb.created_at', 'DESC');

        if ($limit) {
            $this->db->limit($limit, $offset);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Get budgets by project
     * @param int $project_id
     * @param bool $approved_only
     * @return array
     */
    public function get_by_project($project_id, $approved_only = false)
    {
        $this->db->select('pb.*, creator.firstname as creator_firstname, creator.lastname as creator_lastname')
                 ->from(db_prefix() . 'project_budgets pb')
                 ->join(db_prefix() . 'staff creator', 'creator.staffid = pb.created_by', 'left')
                 ->where('pb.project_id', $project_id);

        if ($approved_only) {
            $this->db->where('pb.approved_by IS NOT NULL', null, false);
        }

        $this->db->order_by('pb.category', 'ASC');

        return $this->db->get()->result_array();
    }

    /**
     * Add new budget
     * @param array $data
     * @return int|bool
     */
    public function add($data)
    {
        // Validate required fields
        if (!$this->validate_budget_data($data)) {
            return false;
        }

        // Set default values
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        // Set default currency if not provided
        if (!isset($data['currency']) || empty($data['currency'])) {
            $data['currency'] = get_option('project_enhancement_currency', 'USD');
        }

        $this->db->trans_start();

        $this->db->insert(db_prefix() . 'project_budgets', $data);
        $budget_id = $this->db->insert_id();

        if ($budget_id) {
            // Create initial allocation transaction
            $this->add_transaction([
                'project_id' => $data['project_id'],
                'budget_id' => $budget_id,
                'amount' => $data['allocated_amount'],
                'transaction_type' => 'allocation',
                'description' => 'Initial budget allocation for ' . $data['category'],
                'created_by' => $data['created_by']
            ]);

            // Log activity
            $this->log_budget_activity($budget_id, 'budget_created', $data);

            // Trigger hooks
            hooks()->do_action('budget_created', ['budget_id' => $budget_id, 'data' => $data]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $budget_id;
    }

    /**
     * Update budget
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update($id, $data)
    {
        // Validate required fields
        if (!$this->validate_budget_data($data, $id)) {
            return false;
        }

        $current_budget = $this->get($id);
        if (!$current_budget) {
            return false;
        }

        $data['updated_at'] = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'project_budgets', $data);

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            // Check if allocated amount changed
            if (isset($data['allocated_amount']) && $data['allocated_amount'] != $current_budget->allocated_amount) {
                $difference = $data['allocated_amount'] - $current_budget->allocated_amount;
                
                // Add adjustment transaction
                $this->add_transaction([
                    'project_id' => $current_budget->project_id,
                    'budget_id' => $id,
                    'amount' => abs($difference),
                    'transaction_type' => $difference > 0 ? 'allocation' : 'adjustment',
                    'description' => 'Budget allocation adjustment for ' . $current_budget->category,
                    'created_by' => get_staff_user_id()
                ]);
            }

            // Log activity
            $this->log_budget_activity($id, 'budget_updated', $data);

            // Trigger hooks
            hooks()->do_action('budget_updated', ['budget_id' => $id, 'data' => $data]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== false && $affected_rows > 0;
    }

    /**
     * Delete budget
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        // Check if budget exists
        $budget = $this->get($id);
        if (!$budget) {
            return false;
        }

        // Check if budget has transactions
        $transaction_count = $this->get_transaction_count($id);
        if ($transaction_count > 1) { // More than initial allocation
            $this->session->set_flashdata('message-danger', _l('cannot_delete_budget_with_transactions'));
            return false;
        }

        $this->db->trans_start();

        // Delete all transactions
        $this->db->where('budget_id', $id);
        $this->db->delete(db_prefix() . 'budget_transactions');

        // Delete budget
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'project_budgets');

        $affected_rows = $this->db->affected_rows();

        if ($affected_rows > 0) {
            // Log activity
            $this->log_budget_activity($id, 'budget_deleted', $budget);

            // Trigger hooks
            hooks()->do_action('budget_deleted', ['budget_id' => $id, 'budget' => $budget]);
        }

        $this->db->trans_complete();

        return $this->db->trans_status() !== false && $affected_rows > 0;
    }

    /**
     * Approve budget
     * @param int $id
     * @param int $approver_id
     * @return bool
     */
    public function approve($id, $approver_id)
    {
        $budget = $this->get($id);
        if (!$budget || $budget->approved_by) {
            return false;
        }

        $update_data = [
            'approved_by' => $approver_id,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'project_budgets', $update_data);

        if ($this->db->affected_rows() > 0) {
            // Log activity
            $this->log_budget_activity($id, 'budget_approved', ['approver_id' => $approver_id]);

            // Trigger hooks
            hooks()->do_action('budget_approved', ['budget_id' => $id, 'approver_id' => $approver_id]);

            return true;
        }

        return false;
    }

    /**
     * Add budget transaction
     * @param array $data
     * @return int|bool
     */
    public function add_transaction($data)
    {
        // Validate required fields
        if (!$this->validate_transaction_data($data)) {
            return false;
        }

        // Set default values
        $data['created_at'] = date('Y-m-d H:i:s');

        $this->db->trans_start();

        $this->db->insert(db_prefix() . 'budget_transactions', $data);
        $transaction_id = $this->db->insert_id();

        if ($transaction_id) {
            // Update budget spent amount
            $this->update_budget_spent_amount($data['budget_id']);

            // Log activity
            $this->log_transaction_activity($transaction_id, 'transaction_created', $data);

            // Trigger hooks
            hooks()->do_action('budget_transaction_created', ['transaction_id' => $transaction_id, 'data' => $data]);
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $transaction_id;
    }

    /**
     * Get budget transactions
     * @param int $budget_id
     * @param string $transaction_type
     * @return array
     */
    public function get_transactions($budget_id = null, $transaction_type = null)
    {
        $this->db->select('bt.*, pb.category as budget_category, p.name as project_name,
                          s.firstname as creator_firstname, s.lastname as creator_lastname')
                 ->from(db_prefix() . 'budget_transactions bt')
                 ->join(db_prefix() . 'project_budgets pb', 'pb.id = bt.budget_id', 'left')
                 ->join(db_prefix() . 'projects p', 'p.id = bt.project_id', 'left')
                 ->join(db_prefix() . 'staff s', 's.staffid = bt.created_by', 'left');

        if ($budget_id) {
            $this->db->where('bt.budget_id', $budget_id);
        }

        if ($transaction_type) {
            $this->db->where('bt.transaction_type', $transaction_type);
        }

        $this->db->order_by('bt.created_at', 'DESC');

        return $this->db->get()->result_array();
    }

    /**
     * Get project budget summary
     * @param int $project_id
     * @param string $currency
     * @return array
     */
    public function get_project_summary($project_id, $currency = null)
    {
        if (!$currency) {
            $currency = get_option('project_enhancement_currency', 'USD');
        }

        $summary = [
            'total_allocated' => 0,
            'total_spent' => 0,
            'total_remaining' => 0,
            'utilization_percentage' => 0,
            'categories' => [],
            'transactions_count' => 0,
            'over_budget_categories' => [],
            'currency' => $currency
        ];

        // Get budget data by category
        $this->db->select('category, SUM(allocated_amount) as allocated, SUM(spent_amount) as spent')
                 ->from(db_prefix() . 'project_budgets')
                 ->where('project_id', $project_id)
                 ->where('currency', $currency)
                 ->group_by('category');

        $budgets = $this->db->get()->result_array();

        foreach ($budgets as $budget) {
            $allocated = (float)$budget['allocated'];
            $spent = (float)$budget['spent'];
            $remaining = $allocated - $spent;
            $utilization = $allocated > 0 ? ($spent / $allocated) * 100 : 0;

            $summary['total_allocated'] += $allocated;
            $summary['total_spent'] += $spent;

            $summary['categories'][$budget['category']] = [
                'allocated' => $allocated,
                'spent' => $spent,
                'remaining' => $remaining,
                'utilization_percentage' => round($utilization, 2),
                'status' => $this->get_budget_status($utilization)
            ];

            if ($utilization > 100) {
                $summary['over_budget_categories'][] = $budget['category'];
            }
        }

        $summary['total_remaining'] = $summary['total_allocated'] - $summary['total_spent'];
        $summary['utilization_percentage'] = $summary['total_allocated'] > 0 
            ? round(($summary['total_spent'] / $summary['total_allocated']) * 100, 2) 
            : 0;

        // Get transaction count
        $this->db->where('project_id', $project_id);
        $summary['transactions_count'] = $this->db->count_all_results(db_prefix() . 'budget_transactions');

        return $summary;
    }

    /**
     * Get budget variance analysis
     * @param int $project_id
     * @param string $period
     * @return array
     */
    public function get_variance_analysis($project_id, $period = 'monthly')
    {
        $analysis = [
            'periods' => [],
            'variance_trend' => [],
            'avg_variance' => 0,
            'forecast' => []
        ];

        $date_format = $period === 'weekly' ? '%Y-%u' : '%Y-%m';
        $date_interval = $period === 'weekly' ? '1 WEEK' : '1 MONTH';

        // Get spending by period
        $this->db->select("DATE_FORMAT(created_at, '{$date_format}') as period, 
                          SUM(CASE WHEN transaction_type = 'expense' THEN amount ELSE 0 END) as spent,
                          COUNT(*) as transaction_count")
                 ->from(db_prefix() . 'budget_transactions')
                 ->where('project_id', $project_id)
                 ->where('created_at >=', date('Y-m-d', strtotime('-6 months')))
                 ->group_by('period')
                 ->order_by('period', 'ASC');

        $spending_data = $this->db->get()->result_array();

        // Calculate variance (difference from average)
        $spending_amounts = array_column($spending_data, 'spent');
        $avg_spending = count($spending_amounts) > 0 ? array_sum($spending_amounts) / count($spending_amounts) : 0;

        foreach ($spending_data as $data) {
            $variance = (float)$data['spent'] - $avg_spending;
            $variance_percentage = $avg_spending > 0 ? ($variance / $avg_spending) * 100 : 0;

            $analysis['periods'][] = $data['period'];
            $analysis['variance_trend'][] = [
                'period' => $data['period'],
                'spent' => (float)$data['spent'],
                'variance' => round($variance, 2),
                'variance_percentage' => round($variance_percentage, 2),
                'transaction_count' => (int)$data['transaction_count']
            ];
        }

        $analysis['avg_variance'] = count($analysis['variance_trend']) > 0 
            ? round(array_sum(array_column($analysis['variance_trend'], 'variance')) / count($analysis['variance_trend']), 2) 
            : 0;

        // Simple forecast for next 3 periods
        if (count($spending_amounts) >= 3) {
            $trend = $this->calculate_trend($spending_amounts);
            $last_amount = end($spending_amounts);

            for ($i = 1; $i <= 3; $i++) {
                $forecast_amount = $last_amount + ($trend * $i);
                $analysis['forecast'][] = [
                    'period' => $i,
                    'forecasted_amount' => round(max(0, $forecast_amount), 2)
                ];
            }
        }

        return $analysis;
    }

    /**
     * Get budget alerts
     * @param int $project_id
     * @param array $thresholds
     * @return array
     */
    public function get_budget_alerts($project_id, $thresholds = [])
    {
        $default_thresholds = [
            'warning' => 80,   // 80% of budget used
            'critical' => 95,  // 95% of budget used
            'over_budget' => 100 // Over budget
        ];

        $thresholds = array_merge($default_thresholds, $thresholds);
        $alerts = [];

        $budgets = $this->get_by_project($project_id, true); // Only approved budgets

        foreach ($budgets as $budget) {
            $allocated = (float)$budget['allocated_amount'];
            $spent = (float)$budget['spent_amount'];
            $utilization = $allocated > 0 ? ($spent / $allocated) * 100 : 0;

            if ($utilization >= $thresholds['over_budget']) {
                $alerts[] = [
                    'type' => 'over_budget',
                    'severity' => 'critical',
                    'category' => $budget['category'],
                    'message' => "Budget exceeded for {$budget['category']}",
                    'utilization' => round($utilization, 2),
                    'amount_over' => round($spent - $allocated, 2),
                    'currency' => $budget['currency']
                ];
            } elseif ($utilization >= $thresholds['critical']) {
                $alerts[] = [
                    'type' => 'critical_threshold',
                    'severity' => 'critical',
                    'category' => $budget['category'],
                    'message' => "Critical budget threshold reached for {$budget['category']}",
                    'utilization' => round($utilization, 2),
                    'remaining' => round($allocated - $spent, 2),
                    'currency' => $budget['currency']
                ];
            } elseif ($utilization >= $thresholds['warning']) {
                $alerts[] = [
                    'type' => 'warning_threshold',
                    'severity' => 'warning',
                    'category' => $budget['category'],
                    'message' => "Budget warning threshold reached for {$budget['category']}",
                    'utilization' => round($utilization, 2),
                    'remaining' => round($allocated - $spent, 2),
                    'currency' => $budget['currency']
                ];
            }
        }

        return $alerts;
    }

    /**
     * Convert currency amount
     * @param float $amount
     * @param string $from_currency
     * @param string $to_currency
     * @return float
     */
    public function convert_currency($amount, $from_currency, $to_currency)
    {
        if ($from_currency === $to_currency) {
            return $amount;
        }

        // This is a simplified currency conversion
        // In a real application, you would use a currency exchange API
        $exchange_rates = [
            'USD' => 1.0,
            'EUR' => 0.85,
            'GBP' => 0.73,
            'CAD' => 1.25,
            'AUD' => 1.35
        ];

        $usd_amount = $amount / ($exchange_rates[$from_currency] ?? 1);
        return $usd_amount * ($exchange_rates[$to_currency] ?? 1);
    }

    /**
     * Get budget statistics
     * @param array $filters
     * @return array
     */
    public function get_statistics($filters = [])
    {
        $stats = [
            'total_projects_with_budget' => 0,
            'total_allocated' => 0,
            'total_spent' => 0,
            'avg_utilization' => 0,
            'over_budget_count' => 0,
            'by_category' => [],
            'by_currency' => [],
            'by_month' => []
        ];

        // Build base query
        $this->db->select('pb.*, p.name as project_name')
                 ->from(db_prefix() . 'project_budgets pb')
                 ->join(db_prefix() . 'projects p', 'p.id = pb.project_id', 'left');

        // Apply filters
        if (isset($filters['project_id'])) {
            $this->db->where('pb.project_id', $filters['project_id']);
        }

        if (isset($filters['currency'])) {
            $this->db->where('pb.currency', $filters['currency']);
        }

        if (isset($filters['date_from'])) {
            $this->db->where('DATE(pb.created_at) >=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $this->db->where('DATE(pb.created_at) <=', $filters['date_to']);
        }

        $budgets = $this->db->get()->result_array();

        $project_ids = [];
        $total_utilization = 0;
        $budget_count = 0;

        foreach ($budgets as $budget) {
            $allocated = (float)$budget['allocated_amount'];
            $spent = (float)$budget['spent_amount'];
            $utilization = $allocated > 0 ? ($spent / $allocated) * 100 : 0;

            $project_ids[] = $budget['project_id'];
            $stats['total_allocated'] += $allocated;
            $stats['total_spent'] += $spent;
            $total_utilization += $utilization;
            $budget_count++;

            if ($utilization > 100) {
                $stats['over_budget_count']++;
            }

            // By category
            $category = $budget['category'];
            if (!isset($stats['by_category'][$category])) {
                $stats['by_category'][$category] = ['allocated' => 0, 'spent' => 0, 'count' => 0];
            }
            $stats['by_category'][$category]['allocated'] += $allocated;
            $stats['by_category'][$category]['spent'] += $spent;
            $stats['by_category'][$category]['count']++;

            // By currency
            $currency = $budget['currency'];
            if (!isset($stats['by_currency'][$currency])) {
                $stats['by_currency'][$currency] = ['allocated' => 0, 'spent' => 0, 'count' => 0];
            }
            $stats['by_currency'][$currency]['allocated'] += $allocated;
            $stats['by_currency'][$currency]['spent'] += $spent;
            $stats['by_currency'][$currency]['count']++;

            // By month
            $month = date('Y-m', strtotime($budget['created_at']));
            if (!isset($stats['by_month'][$month])) {
                $stats['by_month'][$month] = ['allocated' => 0, 'spent' => 0, 'count' => 0];
            }
            $stats['by_month'][$month]['allocated'] += $allocated;
            $stats['by_month'][$month]['spent'] += $spent;
            $stats['by_month'][$month]['count']++;
        }

        $stats['total_projects_with_budget'] = count(array_unique($project_ids));
        $stats['avg_utilization'] = $budget_count > 0 ? round($total_utilization / $budget_count, 2) : 0;

        return $stats;
    }

    /**
     * Update budget spent amount
     * @param int $budget_id
     * @return bool
     */
    private function update_budget_spent_amount($budget_id)
    {
        // Calculate total spent from transactions
        $this->db->select('SUM(CASE WHEN transaction_type = "expense" THEN amount 
                                  WHEN transaction_type = "income" THEN -amount 
                                  ELSE 0 END) as total_spent')
                 ->from(db_prefix() . 'budget_transactions')
                 ->where('budget_id', $budget_id);

        $result = $this->db->get()->row();
        $total_spent = max(0, (float)$result->total_spent);

        // Update budget
        $this->db->where('id', $budget_id);
        $this->db->update(db_prefix() . 'project_budgets', ['spent_amount' => $total_spent]);

        return $this->db->affected_rows() > 0;
    }

    /**
     * Get transaction count for budget
     * @param int $budget_id
     * @return int
     */
    private function get_transaction_count($budget_id)
    {
        $this->db->where('budget_id', $budget_id);
        return $this->db->count_all_results(db_prefix() . 'budget_transactions');
    }

    /**
     * Get budget status based on utilization
     * @param float $utilization
     * @return string
     */
    private function get_budget_status($utilization)
    {
        if ($utilization > 100) {
            return 'over_budget';
        } elseif ($utilization >= 95) {
            return 'critical';
        } elseif ($utilization >= 80) {
            return 'warning';
        } elseif ($utilization >= 50) {
            return 'good';
        } else {
            return 'excellent';
        }
    }

    /**
     * Calculate trend from array of values
     * @param array $values
     * @return float
     */
    private function calculate_trend($values)
    {
        $n = count($values);
        if ($n < 2) return 0;

        $sum_x = 0;
        $sum_y = array_sum($values);
        $sum_xy = 0;
        $sum_x2 = 0;

        for ($i = 0; $i < $n; $i++) {
            $x = $i + 1;
            $y = $values[$i];
            $sum_x += $x;
            $sum_xy += $x * $y;
            $sum_x2 += $x * $x;
        }

        $slope = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - $sum_x * $sum_x);
        return $slope;
    }

    /**
     * Validate budget data
     * @param array $data
     * @param int $id
     * @return bool
     */
    private function validate_budget_data($data, $id = null)
    {
        // Required fields
        $required_fields = ['project_id', 'category', 'allocated_amount', 'created_by'];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->session->set_flashdata('message-danger', _l($field . '_required'));
                return false;
            }
        }

        // Validate allocated amount
        if ($data['allocated_amount'] <= 0) {
            $this->session->set_flashdata('message-danger', _l('allocated_amount_must_be_positive'));
            return false;
        }

        // Validate project exists
        $this->db->where('id', $data['project_id']);
        if ($this->db->count_all_results(db_prefix() . 'projects') == 0) {
            $this->session->set_flashdata('message-danger', _l('project_not_found'));
            return false;
        }

        return true;
    }

    /**
     * Validate transaction data
     * @param array $data
     * @return bool
     */
    private function validate_transaction_data($data)
    {
        // Required fields
        $required_fields = ['project_id', 'budget_id', 'amount', 'transaction_type', 'description', 'created_by'];

        foreach ($required_fields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $this->session->set_flashdata('message-danger', _l($field . '_required'));
                return false;
            }
        }

        // Validate amount
        if ($data['amount'] <= 0) {
            $this->session->set_flashdata('message-danger', _l('amount_must_be_positive'));
            return false;
        }

        // Validate transaction type
        $valid_types = ['expense', 'income', 'allocation', 'adjustment'];
        if (!in_array($data['transaction_type'], $valid_types)) {
            $this->session->set_flashdata('message-danger', _l('invalid_transaction_type'));
            return false;
        }

        return true;
    }

    /**
     * Log budget activity
     * @param int $budget_id
     * @param string $action
     * @param array $data
     */
    private function log_budget_activity($budget_id, $action, $data = [])
    {
        $budget = $this->get($budget_id);
        if ($budget) {
            $description = _l($action) . ': ' . $budget->project_name . ' - ' . $budget->category;
            if (!empty($data)) {
                $description .= ' - ' . json_encode($data);
            }
            
            log_activity($description);
        }
    }

    /**
     * Log transaction activity
     * @param int $transaction_id
     * @param string $action
     * @param array $data
     */
    private function log_transaction_activity($transaction_id, $action, $data = [])
    {
        $description = _l($action) . ': Transaction ID ' . $transaction_id;
        if (!empty($data)) {
            $description .= ' - ' . json_encode($data);
        }
        
        log_activity($description);
    }
}