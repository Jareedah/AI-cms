<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Budget Controller
 * Handles budget tracking, transactions, and financial management
 */
class Budget extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        // Check if module is active
        if (!get_option('project_enhancement_active')) {
            show_404();
        }
        
        // Load required models
        $this->load->model('project_enhancement/budget_model');
        $this->load->model('projects_model');
        $this->load->model('staff_model');
    }

    /**
     * List all budgets
     */
    public function index()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('budget_management');
        
        // Get filter parameters
        $project_id = $this->input->get('project_id');
        $category = $this->input->get('category');
        $currency = $this->input->get('currency');
        $approved_only = $this->input->get('approved_only') === '1';
        
        // Build where clause
        $where = [];
        if ($project_id) {
            $where['pb.project_id'] = $project_id;
        }
        if ($category) {
            $where['pb.category'] = $category;
        }
        if ($currency) {
            $where['pb.currency'] = $currency;
        }
        if ($approved_only) {
            $where['pb.approved_by IS NOT NULL'] = null;
        }
        
        // Get budgets with pagination
        $limit = 25;
        $offset = ($this->input->get('page') ?: 1 - 1) * $limit;
        
        $data['budgets'] = $this->budget_model->get_all($where, $limit, $offset);
        $data['total_budgets'] = count($this->budget_model->get_all($where));
        
        // Get filter options
        $data['projects'] = $this->projects_model->get();
        
        // Get unique categories and currencies
        $this->db->select('DISTINCT category');
        $this->db->from(db_prefix() . 'project_budgets');
        $data['categories'] = array_column($this->db->get()->result_array(), 'category');
        
        $this->db->select('DISTINCT currency');
        $this->db->from(db_prefix() . 'project_budgets');
        $data['currencies'] = array_column($this->db->get()->result_array(), 'currency');
        
        $data['current_filters'] = [
            'project_id' => $project_id,
            'category' => $category,
            'currency' => $currency,
            'approved_only' => $approved_only
        ];
        
        // Get budget statistics
        $data['budget_stats'] = $this->budget_model->get_statistics();
        
        $this->load->view('admin/project_enhancement/budget/manage', $data);
    }

    /**
     * Create new budget
     */
    public function create($project_id = null)
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement') || !staff_can('manage_budgets', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('add') . ' ' . _l('budget');
        
        // Handle form submission
        if ($this->input->post()) {
            $this->handle_budget_form();
            return;
        }
        
        // Prepare form data
        $data['projects'] = $this->projects_model->get();
        $data['selected_project_id'] = $project_id;
        
        // Get budget categories from config
        $this->load->config('project_enhancement/module_config');
        $data['budget_categories'] = $this->config->item('budget_categories');
        
        // Get available currencies
        $data['currencies'] = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];
        $data['default_currency'] = get_option('project_enhancement_currency', 'USD');
        
        $this->load->view('admin/project_enhancement/budget/form', $data);
    }

    /**
     * Edit budget
     */
    public function edit($id)
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement') || !staff_can('manage_budgets', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get budget
        $budget = $this->budget_model->get($id);
        if (!$budget) {
            show_404();
        }

        $data['title'] = _l('edit') . ' ' . _l('budget');
        $data['budget'] = $budget;
        
        // Handle form submission
        if ($this->input->post()) {
            $this->handle_budget_form($id);
            return;
        }
        
        // Prepare form data
        $data['projects'] = $this->projects_model->get();
        
        // Get budget categories from config
        $this->load->config('project_enhancement/module_config');
        $data['budget_categories'] = $this->config->item('budget_categories');
        
        // Get available currencies
        $data['currencies'] = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];
        
        $this->load->view('admin/project_enhancement/budget/form', $data);
    }

    /**
     * View budget details
     */
    public function view($id)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get budget
        $budget = $this->budget_model->get($id);
        if (!$budget) {
            show_404();
        }

        $data['title'] = _l('budget') . ' - ' . $budget->category;
        $data['budget'] = $budget;
        
        // Get budget transactions
        $data['transactions'] = $this->budget_model->get_transactions($id);
        
        // Get budget alerts
        $data['alerts'] = $this->budget_model->get_budget_alerts($budget->project_id);
        
        // Calculate utilization percentage
        $utilization = $budget->allocated_amount > 0 ? ($budget->spent_amount / $budget->allocated_amount) * 100 : 0;
        $data['utilization_percentage'] = round($utilization, 2);
        
        $this->load->view('admin/project_enhancement/budget/view', $data);
    }

    /**
     * Delete budget
     */
    public function delete($id)
    {
        // Check permissions
        if (!staff_can('delete', 'project_enhancement') || !staff_can('manage_budgets', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Validate budget exists
        $budget = $this->budget_model->get($id);
        if (!$budget) {
            set_alert('danger', _l('budget_not_found'));
            redirect(admin_url('project_enhancement/budget'));
        }

        if ($this->budget_model->delete($id)) {
            set_alert('success', _l('budget_deleted_successfully'));
        } else {
            set_alert('danger', _l('operation_failed'));
        }

        redirect(admin_url('project_enhancement/budget'));
    }

    /**
     * Approve budget
     */
    public function approve($id)
    {
        // Check permissions
        if (!staff_can('manage_budget_approvals', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get budget
        $budget = $this->budget_model->get($id);
        if (!$budget) {
            set_alert('danger', _l('budget_not_found'));
            redirect(admin_url('project_enhancement/budget'));
        }

        if ($this->budget_model->approve($id, get_staff_user_id())) {
            set_alert('success', _l('budget_approved_successfully'));
        } else {
            set_alert('danger', _l('operation_failed'));
        }

        redirect(admin_url('project_enhancement/budget/view/' . $id));
    }

    /**
     * Transactions management
     */
    public function transactions($budget_id = null)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('budget_transactions');
        
        // Get filter parameters
        $transaction_type = $this->input->get('transaction_type');
        
        // Get transactions
        $data['transactions'] = $this->budget_model->get_transactions($budget_id, $transaction_type);
        
        // Get budget info if specific budget
        if ($budget_id) {
            $data['budget'] = $this->budget_model->get($budget_id);
            if (!$data['budget']) {
                show_404();
            }
        }
        
        $data['current_filters'] = [
            'budget_id' => $budget_id,
            'transaction_type' => $transaction_type
        ];
        
        $this->load->view('admin/project_enhancement/budget/transactions', $data);
    }

    /**
     * Add transaction
     */
    public function add_transaction($budget_id = null)
    {
        // Check permissions
        if (!staff_can('create', 'project_enhancement') || !staff_can('manage_budget_transactions', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('add') . ' ' . _l('transaction');
        
        // Handle form submission
        if ($this->input->post()) {
            $this->handle_transaction_form();
            return;
        }
        
        // Get budget if specified
        if ($budget_id) {
            $budget = $this->budget_model->get($budget_id);
            if (!$budget) {
                show_404();
            }
            $data['budget'] = $budget;
        }
        
        // Get all budgets for selection
        $data['budgets'] = $this->budget_model->get_all();
        $data['selected_budget_id'] = $budget_id;
        
        $this->load->view('admin/project_enhancement/budget/transaction_form', $data);
    }

    /**
     * Project budget overview
     */
    public function project($project_id)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Validate project
        $project = $this->projects_model->get($project_id);
        if (!$project) {
            show_404();
        }

        $data['title'] = _l('project_budget') . ' - ' . $project->name;
        $data['project'] = $project;
        
        // Get project budgets
        $data['budgets'] = $this->budget_model->get_by_project($project_id);
        
        // Get budget summary
        $data['budget_summary'] = $this->budget_model->get_project_summary($project_id);
        
        // Get budget alerts
        $data['budget_alerts'] = $this->budget_model->get_budget_alerts($project_id);
        
        // Get variance analysis
        $data['variance_analysis'] = $this->budget_model->get_variance_analysis($project_id);
        
        $this->load->view('admin/project_enhancement/budget/project_overview', $data);
    }

    /**
     * Budget reports
     */
    public function reports()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('budget_reports');
        
        // Get filter parameters
        $project_id = $this->input->get('project_id');
        $currency = $this->input->get('currency');
        $date_from = $this->input->get('date_from') ?: date('Y-m-01');
        $date_to = $this->input->get('date_to') ?: date('Y-m-t');
        
        // Build filters
        $filters = [];
        if ($project_id) {
            $filters['project_id'] = $project_id;
        }
        if ($currency) {
            $filters['currency'] = $currency;
        }
        if ($date_from) {
            $filters['date_from'] = $date_from;
        }
        if ($date_to) {
            $filters['date_to'] = $date_to;
        }
        
        // Get budget statistics
        $data['budget_stats'] = $this->budget_model->get_statistics($filters);
        
        // Get filter options
        $data['projects'] = $this->projects_model->get();
        $data['currencies'] = ['USD', 'EUR', 'GBP', 'CAD', 'AUD'];
        
        $data['current_filters'] = [
            'project_id' => $project_id,
            'currency' => $currency,
            'date_from' => $date_from,
            'date_to' => $date_to
        ];
        
        $this->load->view('admin/project_enhancement/budget/reports', $data);
    }

    /**
     * Variance analysis
     */
    public function variance($project_id = null, $period = 'monthly')
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('budget_variance_analysis');
        
        if ($project_id) {
            // Validate project
            $project = $this->projects_model->get($project_id);
            if (!$project) {
                show_404();
            }
            $data['project'] = $project;
            
            // Get variance analysis for specific project
            $data['variance_analysis'] = $this->budget_model->get_variance_analysis($project_id, $period);
        } else {
            // Get variance analysis for all projects
            $data['projects'] = $this->projects_model->get();
            $variance_data = [];
            
            foreach ($data['projects'] as $project) {
                $variance_data[$project['id']] = [
                    'project' => $project,
                    'analysis' => $this->budget_model->get_variance_analysis($project['id'], $period)
                ];
            }
            $data['variance_data'] = $variance_data;
        }
        
        $data['selected_project_id'] = $project_id;
        $data['selected_period'] = $period;
        
        $this->load->view('admin/project_enhancement/budget/variance', $data);
    }

    /**
     * Currency conversion
     */
    public function convert_currency()
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $amount = $this->input->post('amount');
        $from_currency = $this->input->post('from_currency');
        $to_currency = $this->input->post('to_currency');

        if (!$amount || !$from_currency || !$to_currency) {
            header('HTTP/1.1 400 Bad Request');
            exit;
        }

        $converted_amount = $this->budget_model->convert_currency($amount, $from_currency, $to_currency);

        header('Content-Type: application/json');
        echo json_encode([
            'converted_amount' => round($converted_amount, 2),
            'exchange_rate' => round($converted_amount / $amount, 4)
        ]);
    }

    /**
     * Budget alerts
     */
    public function alerts($project_id = null)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $data['title'] = _l('budget_alerts');
        
        if ($project_id) {
            // Validate project
            $project = $this->projects_model->get($project_id);
            if (!$project) {
                show_404();
            }
            $data['project'] = $project;
            $data['alerts'] = $this->budget_model->get_budget_alerts($project_id);
        } else {
            // Get alerts for all projects
            $all_alerts = [];
            $projects = $this->projects_model->get();
            
            foreach ($projects as $project) {
                $project_alerts = $this->budget_model->get_budget_alerts($project['id']);
                foreach ($project_alerts as &$alert) {
                    $alert['project_name'] = $project['name'];
                }
                $all_alerts = array_merge($all_alerts, $project_alerts);
            }
            
            // Sort by severity
            usort($all_alerts, function($a, $b) {
                $severity_order = ['critical' => 1, 'warning' => 2, 'info' => 3];
                return $severity_order[$a['severity']] <=> $severity_order[$b['severity']];
            });
            
            $data['alerts'] = $all_alerts;
        }
        
        $data['selected_project_id'] = $project_id;
        $data['projects'] = $this->projects_model->get();
        
        $this->load->view('admin/project_enhancement/budget/alerts', $data);
    }

    /**
     * Bulk actions
     */
    public function bulk_action()
    {
        // Check permissions
        if (!staff_can('edit', 'project_enhancement') || !staff_can('manage_budgets', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        $action = $this->input->post('bulk_action');
        $budget_ids = $this->input->post('budget_ids');

        if (!$action || empty($budget_ids)) {
            set_alert('danger', _l('no_items_selected'));
            redirect(admin_url('project_enhancement/budget'));
        }

        $success_count = 0;
        
        switch ($action) {
            case 'delete':
                if (staff_can('delete', 'project_enhancement')) {
                    foreach ($budget_ids as $id) {
                        if ($this->budget_model->delete($id)) {
                            $success_count++;
                        }
                    }
                    set_alert('success', sprintf(_l('bulk_delete_success'), $success_count));
                }
                break;
                
            case 'approve':
                if (staff_can('manage_budget_approvals', 'project_enhancement')) {
                    foreach ($budget_ids as $id) {
                        if ($this->budget_model->approve($id, get_staff_user_id())) {
                            $success_count++;
                        }
                    }
                    set_alert('success', sprintf(_l('bulk_approve_success'), $success_count));
                }
                break;
        }

        redirect(admin_url('project_enhancement/budget'));
    }

    /**
     * Export budget data
     */
    public function export($format = 'csv', $project_id = null)
    {
        // Check permissions
        if (!staff_can('view', 'project_enhancement')) {
            access_denied('project_enhancement');
        }

        // Get budget data
        if ($project_id) {
            $budgets = $this->budget_model->get_by_project($project_id);
            $filename_suffix = '_project_' . $project_id;
        } else {
            $budgets = $this->budget_model->get_all();
            $filename_suffix = '_all';
        }

        $filename = 'budget_export' . $filename_suffix . '_' . date('Y-m-d_H-i-s');

        switch ($format) {
            case 'csv':
                $this->export_csv($budgets, $filename);
                break;
            case 'excel':
                $this->export_excel($budgets, $filename);
                break;
            default:
                show_404();
        }
    }

    /**
     * Handle budget form submission
     */
    private function handle_budget_form($id = null)
    {
        $data = [
            'project_id' => $this->input->post('project_id'),
            'category' => $this->input->post('category'),
            'allocated_amount' => $this->input->post('allocated_amount'),
            'currency' => $this->input->post('currency'),
            'notes' => $this->input->post('notes'),
            'created_by' => get_staff_user_id()
        ];

        if ($id) {
            // Update existing budget
            if ($this->budget_model->update($id, $data)) {
                set_alert('success', _l('budget_updated_successfully'));
                redirect(admin_url('project_enhancement/budget/view/' . $id));
            } else {
                set_alert('danger', _l('operation_failed'));
            }
        } else {
            // Create new budget
            $budget_id = $this->budget_model->add($data);
            if ($budget_id) {
                set_alert('success', _l('budget_created_successfully'));
                redirect(admin_url('project_enhancement/budget/view/' . $budget_id));
            } else {
                set_alert('danger', _l('operation_failed'));
            }
        }
    }

    /**
     * Handle transaction form submission
     */
    private function handle_transaction_form()
    {
        $data = [
            'project_id' => $this->input->post('project_id'),
            'budget_id' => $this->input->post('budget_id'),
            'amount' => $this->input->post('amount'),
            'transaction_type' => $this->input->post('transaction_type'),
            'description' => $this->input->post('description'),
            'reference' => $this->input->post('reference'),
            'created_by' => get_staff_user_id()
        ];

        $transaction_id = $this->budget_model->add_transaction($data);
        if ($transaction_id) {
            set_alert('success', _l('transaction_created_successfully'));
            redirect(admin_url('project_enhancement/budget/transactions/' . $data['budget_id']));
        } else {
            set_alert('danger', _l('operation_failed'));
        }
    }

    /**
     * Export budgets as CSV
     */
    private function export_csv($budgets, $filename)
    {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        // CSV headers
        fputcsv($output, [
            'ID',
            'Project',
            'Category',
            'Allocated Amount',
            'Spent Amount',
            'Remaining Amount',
            'Utilization %',
            'Currency',
            'Approved By',
            'Created By',
            'Created Date'
        ]);

        // CSV data
        foreach ($budgets as $budget) {
            $remaining = $budget['allocated_amount'] - $budget['spent_amount'];
            $utilization = $budget['allocated_amount'] > 0 ? ($budget['spent_amount'] / $budget['allocated_amount']) * 100 : 0;
            
            fputcsv($output, [
                $budget['id'],
                $budget['project_name'] ?? '',
                $budget['category'],
                $budget['allocated_amount'],
                $budget['spent_amount'],
                round($remaining, 2),
                round($utilization, 2) . '%',
                $budget['currency'],
                ($budget['approver_firstname'] ?? '') . ' ' . ($budget['approver_lastname'] ?? ''),
                ($budget['creator_firstname'] ?? '') . ' ' . ($budget['creator_lastname'] ?? ''),
                $budget['created_at']
            ]);
        }

        fclose($output);
    }

    /**
     * Export budgets as Excel
     */
    private function export_excel($budgets, $filename)
    {
        // This would require PHPSpreadsheet or similar library
        // For now, fall back to CSV
        $this->export_csv($budgets, $filename);
    }
}