<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <!-- Header -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin">
                                    <i class="fa fa-dollar-sign"></i> 
                                    <?= _l('budget_management'); ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <a href="<?= admin_url('project_enhancement/budget/create'); ?>" class="btn btn-info">
                                        <i class="fa fa-plus"></i> <?= _l('create_budget'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/budget/transactions'); ?>" class="btn btn-default">
                                        <i class="fa fa-exchange-alt"></i> <?= _l('transactions'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/budget/variance'); ?>" class="btn btn-default">
                                        <i class="fa fa-chart-line"></i> <?= _l('variance_analysis'); ?>
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                            <i class="fa fa-download"></i> <?= _l('export'); ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="<?= admin_url('project_enhancement/budget/export/csv'); ?>">CSV</a></li>
                                            <li><a href="<?= admin_url('project_enhancement/budget/export/excel'); ?>">Excel</a></li>
                                            <li><a href="<?= admin_url('project_enhancement/budget/export/pdf'); ?>">PDF</a></li>
                                        </ul>
                                    </div>
                                    <a href="<?= admin_url('project_enhancement/budget/reports'); ?>" class="btn btn-default">
                                        <i class="fa fa-chart-bar"></i> <?= _l('reports'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Overview Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-info">$<?= number_format($budget_stats['total_budget'] ?? 0, 2); ?></h3>
                        <p class="text-muted"><?= _l('total_budget'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-success">$<?= number_format($budget_stats['total_spent'] ?? 0, 2); ?></h3>
                        <p class="text-muted"><?= _l('total_spent'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-warning">$<?= number_format($budget_stats['remaining_budget'] ?? 0, 2); ?></h3>
                        <p class="text-muted"><?= _l('remaining_budget'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-danger"><?= $budget_stats['over_budget_count'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('over_budget_projects'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Performance Chart -->
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('budget_performance'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <canvas id="budget-performance-chart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('budget_distribution'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <canvas id="budget-distribution-chart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open(admin_url('project_enhancement/budget'), ['method' => 'GET', 'id' => 'budget-filter-form']); ?>
                        <div class="row">
                            <div class="col-md-2">
                                <?= render_select('project_id', $projects, ['id', 'name'], 'project', $current_filters['project_id'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_projects')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_select('status', [
                                    ['id' => 'draft', 'name' => _l('budget_status_draft')],
                                    ['id' => 'approved', 'name' => _l('budget_status_approved')],
                                    ['id' => 'active', 'name' => _l('budget_status_active')],
                                    ['id' => 'completed', 'name' => _l('budget_status_completed')],
                                    ['id' => 'over_budget', 'name' => _l('budget_status_over_budget')]
                                ], ['id', 'name'], 'status', $current_filters['status'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_statuses')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_select('category', $budget_categories, ['id', 'name'], 'category', $current_filters['category'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_categories')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_date_input('date_from', 'date_from', $current_filters['date_from'], ['placeholder' => _l('date_from')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_date_input('date_to', 'date_to', $current_filters['date_to'], ['placeholder' => _l('date_to')]); ?>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info btn-block">
                                    <i class="fa fa-search"></i> <?= _l('filter'); ?>
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget List -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- View Toggle -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info" onclick="toggleBudgetView('cards')">
                                        <i class="fa fa-th-large"></i> <?= _l('card_view'); ?>
                                    </button>
                                    <button type="button" class="btn btn-default" onclick="toggleBudgetView('table')">
                                        <i class="fa fa-table"></i> <?= _l('table_view'); ?>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="form-group">
                                    <select id="bulk-actions" class="form-control" style="width: 200px; display: inline-block;">
                                        <option value=""><?= _l('bulk_actions'); ?></option>
                                        <?php if (staff_can('approve_budgets', 'project_enhancement')): ?>
                                            <option value="approve"><?= _l('approve_selected'); ?></option>
                                        <?php endif; ?>
                                        <option value="export"><?= _l('export_selected'); ?></option>
                                        <?php if (staff_can('delete', 'project_enhancement')): ?>
                                            <option value="delete"><?= _l('delete_selected'); ?></option>
                                        <?php endif; ?>
                                    </select>
                                    <button type="button" class="btn btn-default" onclick="applyBulkAction()">
                                        <?= _l('apply'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Card View -->
                        <div id="budget-cards-view">
                            <div class="row">
                                <?php if (!empty($budgets)): ?>
                                    <?php foreach ($budgets as $budget): ?>
                                        <div class="col-md-6 col-lg-4">
                                            <div class="budget-card">
                                                <div class="budget-header">
                                                    <div class="budget-checkbox">
                                                        <input type="checkbox" name="budget_ids[]" value="<?= $budget['id']; ?>" class="budget-checkbox-item">
                                                    </div>
                                                    <div class="budget-title">
                                                        <h5>
                                                            <a href="<?= admin_url('project_enhancement/budget/view/' . $budget['id']); ?>">
                                                                <?= $budget['project_name']; ?>
                                                            </a>
                                                        </h5>
                                                        <span class="label label-<?= budget_status_color($budget['status']); ?>">
                                                            <?= _l('budget_status_' . $budget['status']); ?>
                                                        </span>
                                                    </div>
                                                    <div class="budget-actions">
                                                        <div class="btn-group">
                                                            <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                                                <i class="fa fa-cog"></i> <span class="caret"></span>
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-right">
                                                                <li><a href="<?= admin_url('project_enhancement/budget/view/' . $budget['id']); ?>">
                                                                    <i class="fa fa-eye"></i> <?= _l('view'); ?>
                                                                </a></li>
                                                                <?php if (staff_can('edit', 'project_enhancement')): ?>
                                                                    <li><a href="<?= admin_url('project_enhancement/budget/edit/' . $budget['id']); ?>">
                                                                        <i class="fa fa-edit"></i> <?= _l('edit'); ?>
                                                                    </a></li>
                                                                <?php endif; ?>
                                                                <li><a href="<?= admin_url('project_enhancement/budget/transactions/' . $budget['id']); ?>">
                                                                    <i class="fa fa-exchange-alt"></i> <?= _l('transactions'); ?>
                                                                </a></li>
                                                                <?php if ($budget['status'] === 'draft' && staff_can('approve_budgets', 'project_enhancement')): ?>
                                                                    <li><a href="<?= admin_url('project_enhancement/budget/approve/' . $budget['id']); ?>">
                                                                        <i class="fa fa-check text-success"></i> <?= _l('approve'); ?>
                                                                    </a></li>
                                                                <?php endif; ?>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="budget-content">
                                                    <div class="budget-amounts">
                                                        <div class="amount-item">
                                                            <span class="amount-label"><?= _l('total_budget'); ?>:</span>
                                                            <span class="amount-value">$<?= number_format($budget['total_amount'], 2); ?></span>
                                                        </div>
                                                        <div class="amount-item">
                                                            <span class="amount-label"><?= _l('spent'); ?>:</span>
                                                            <span class="amount-value text-<?= $budget['spent_amount'] > $budget['total_amount'] ? 'danger' : 'success'; ?>">
                                                                $<?= number_format($budget['spent_amount'], 2); ?>
                                                            </span>
                                                        </div>
                                                        <div class="amount-item">
                                                            <span class="amount-label"><?= _l('remaining'); ?>:</span>
                                                            <span class="amount-value text-<?= $budget['remaining_amount'] < 0 ? 'danger' : 'info'; ?>">
                                                                $<?= number_format($budget['remaining_amount'], 2); ?>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="budget-progress">
                                                        <div class="progress-info">
                                                            <span><?= _l('budget_utilization'); ?></span>
                                                            <span class="text-<?= $budget['utilization_percentage'] > 100 ? 'danger' : ($budget['utilization_percentage'] > 80 ? 'warning' : 'success'); ?>">
                                                                <?= number_format($budget['utilization_percentage'], 1); ?>%
                                                            </span>
                                                        </div>
                                                        <div class="progress">
                                                            <div class="progress-bar progress-bar-<?= $budget['utilization_percentage'] > 100 ? 'danger' : ($budget['utilization_percentage'] > 80 ? 'warning' : 'success'); ?>" 
                                                                 style="width: <?= min($budget['utilization_percentage'], 100); ?>%">
                                                            </div>
                                                            <?php if ($budget['utilization_percentage'] > 100): ?>
                                                                <div class="progress-bar progress-bar-danger progress-bar-striped" 
                                                                     style="width: <?= min($budget['utilization_percentage'] - 100, 20); ?>%">
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <div class="budget-meta">
                                                        <div class="meta-item">
                                                            <i class="fa fa-calendar"></i>
                                                            <?= _d($budget['start_date']); ?> - <?= $budget['end_date'] ? _d($budget['end_date']) : _l('ongoing'); ?>
                                                        </div>
                                                        <div class="meta-item">
                                                            <i class="fa fa-tag"></i>
                                                            <?= $budget['category_name'] ?? _l('uncategorized'); ?>
                                                        </div>
                                                        <?php if ($budget['utilization_percentage'] > 90): ?>
                                                            <div class="meta-item text-<?= $budget['utilization_percentage'] > 100 ? 'danger' : 'warning'; ?>">
                                                                <i class="fa fa-exclamation-triangle"></i>
                                                                <?= $budget['utilization_percentage'] > 100 ? _l('over_budget') : _l('approaching_limit'); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-md-12">
                                        <div class="text-center">
                                            <h4 class="text-muted"><?= _l('no_budgets_found'); ?></h4>
                                            <p class="text-muted"><?= _l('create_first_budget'); ?></p>
                                            <a href="<?= admin_url('project_enhancement/budget/create'); ?>" class="btn btn-info">
                                                <i class="fa fa-plus"></i> <?= _l('create_budget'); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="budget-table-view" style="display: none;">
                            <div class="table-responsive">
                                <table class="table table-striped dt-table" id="budgets-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="select-all-budgets">
                                            </th>
                                            <th><?= _l('project'); ?></th>
                                            <th><?= _l('total_budget'); ?></th>
                                            <th><?= _l('spent'); ?></th>
                                            <th><?= _l('remaining'); ?></th>
                                            <th><?= _l('utilization'); ?></th>
                                            <th><?= _l('status'); ?></th>
                                            <th><?= _l('period'); ?></th>
                                            <th><?= _l('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($budgets)): ?>
                                            <?php foreach ($budgets as $budget): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="budget_ids[]" value="<?= $budget['id']; ?>" class="budget-checkbox-item">
                                                    </td>
                                                    <td>
                                                        <a href="<?= admin_url('projects/view/' . $budget['project_id']); ?>">
                                                            <?= $budget['project_name']; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <span class="font-medium">$<?= number_format($budget['total_amount'], 2); ?></span>
                                                    </td>
                                                    <td>
                                                        <span class="text-<?= $budget['spent_amount'] > $budget['total_amount'] ? 'danger' : 'success'; ?>">
                                                            $<?= number_format($budget['spent_amount'], 2); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="text-<?= $budget['remaining_amount'] < 0 ? 'danger' : 'info'; ?>">
                                                            $<?= number_format($budget['remaining_amount'], 2); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="utilization-display">
                                                            <span class="text-<?= $budget['utilization_percentage'] > 100 ? 'danger' : ($budget['utilization_percentage'] > 80 ? 'warning' : 'success'); ?>">
                                                                <?= number_format($budget['utilization_percentage'], 1); ?>%
                                                            </span>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar progress-bar-<?= $budget['utilization_percentage'] > 100 ? 'danger' : ($budget['utilization_percentage'] > 80 ? 'warning' : 'success'); ?>" 
                                                                     style="width: <?= min($budget['utilization_percentage'], 100); ?>%">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="label label-<?= budget_status_color($budget['status']); ?>">
                                                            <?= _l('budget_status_' . $budget['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?= _d($budget['start_date']); ?><br>
                                                            <?= $budget['end_date'] ? _d($budget['end_date']) : _l('ongoing'); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= admin_url('project_enhancement/budget/view/' . $budget['id']); ?>" 
                                                               class="btn btn-default btn-xs" data-toggle="tooltip" title="<?= _l('view'); ?>">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                            <?php if (staff_can('edit', 'project_enhancement')): ?>
                                                                <a href="<?= admin_url('project_enhancement/budget/edit/' . $budget['id']); ?>" 
                                                                   class="btn btn-info btn-xs" data-toggle="tooltip" title="<?= _l('edit'); ?>">
                                                                    <i class="fa fa-edit"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                                                    <i class="fa fa-cog"></i> <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-right">
                                                                    <li><a href="<?= admin_url('project_enhancement/budget/transactions/' . $budget['id']); ?>">
                                                                        <i class="fa fa-exchange-alt"></i> <?= _l('transactions'); ?>
                                                                    </a></li>
                                                                    <li><a href="<?= admin_url('project_enhancement/budget/variance/' . $budget['project_id']); ?>">
                                                                        <i class="fa fa-chart-line"></i> <?= _l('variance_analysis'); ?>
                                                                    </a></li>
                                                                    <?php if ($budget['status'] === 'draft' && staff_can('approve_budgets', 'project_enhancement')): ?>
                                                                        <li><a href="<?= admin_url('project_enhancement/budget/approve/' . $budget['id']); ?>">
                                                                            <i class="fa fa-check text-success"></i> <?= _l('approve'); ?>
                                                                        </a></li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Alerts -->
        <?php if (!empty($budget_alerts)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s panel-warning">
                        <div class="panel-header">
                            <h5 class="panel-title">
                                <i class="fa fa-exclamation-triangle"></i> <?= _l('budget_alerts'); ?>
                            </h5>
                        </div>
                        <div class="panel-body">
                            <div class="budget-alerts">
                                <?php foreach ($budget_alerts as $alert): ?>
                                    <div class="alert-item alert-<?= $alert['type']; ?>">
                                        <div class="alert-icon">
                                            <i class="fa <?= $alert['icon']; ?>"></i>
                                        </div>
                                        <div class="alert-content">
                                            <h6><?= $alert['title']; ?></h6>
                                            <p><?= $alert['message']; ?></p>
                                            <?php if (!empty($alert['action_url'])): ?>
                                                <a href="<?= $alert['action_url']; ?>" class="btn btn-sm btn-<?= $alert['type']; ?>">
                                                    <?= $alert['action_text']; ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
    'use strict';
    
    // Initialize DataTable
    $('#budgets-table').DataTable({
        "order": [[1, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 8] }
        ]
    });
    
    // Initialize charts
    initializeBudgetCharts();
    
    // Initialize view toggle
    initializeBudgetViewToggle();
    
    // Initialize bulk actions
    initializeBulkActions();
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-submit filter form
    $('#budget-filter-form select, #budget-filter-form input').on('change', function() {
        $('#budget-filter-form').submit();
    });
});

/**
 * Initialize budget charts
 */
function initializeBudgetCharts() {
    // Budget Performance Chart
    var performanceCtx = document.getElementById('budget-performance-chart');
    if (performanceCtx) {
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($chart_data['performance']['labels'] ?? []); ?>,
                datasets: [{
                    label: '<?= _l('budgeted'); ?>',
                    data: <?= json_encode($chart_data['performance']['budgeted'] ?? []); ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: '<?= _l('actual'); ?>',
                    data: <?= json_encode($chart_data['performance']['actual'] ?? []); ?>,
                    borderColor: '#28a745',
                    backgroundColor: 'rgba(40, 167, 69, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': $' + context.parsed.y.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
    
    // Budget Distribution Chart
    var distributionCtx = document.getElementById('budget-distribution-chart');
    if (distributionCtx) {
        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: <?= json_encode($chart_data['distribution']['labels'] ?? []); ?>,
                datasets: [{
                    data: <?= json_encode($chart_data['distribution']['data'] ?? []); ?>,
                    backgroundColor: [
                        '#007bff', '#28a745', '#ffc107', '#dc3545', 
                        '#6f42c1', '#fd7e14', '#20c997', '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': $' + context.parsed.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
}

/**
 * Initialize budget view toggle
 */
function initializeBudgetViewToggle() {
    var viewPreference = localStorage.getItem('budget_view_preference') || 'cards';
    toggleBudgetView(viewPreference);
}

/**
 * Toggle between card and table view
 */
function toggleBudgetView(view) {
    if (view === 'table') {
        $('#budget-cards-view').hide();
        $('#budget-table-view').show();
    } else {
        $('#budget-cards-view').show();
        $('#budget-table-view').hide();
        view = 'cards';
    }
    
    localStorage.setItem('budget_view_preference', view);
    
    // Update button states
    $('.btn-group button').removeClass('btn-info').addClass('btn-default');
    $('button[onclick="toggleBudgetView(\'' + view + '\')"]').removeClass('btn-default').addClass('btn-info');
}

/**
 * Initialize bulk actions
 */
function initializeBulkActions() {
    // Select all checkboxes
    $('#select-all-budgets').on('change', function() {
        $('.budget-checkbox-item').prop('checked', this.checked);
    });
    
    // Individual checkbox change
    $('.budget-checkbox-item').on('change', function() {
        var allChecked = $('.budget-checkbox-item:checked').length === $('.budget-checkbox-item').length;
        $('#select-all-budgets').prop('checked', allChecked);
    });
}

/**
 * Apply bulk action
 */
function applyBulkAction() {
    var action = $('#bulk-actions').val();
    var selectedBudgets = $('.budget-checkbox-item:checked');
    
    if (!action) {
        alert('<?= _l('select_action'); ?>');
        return;
    }
    
    if (selectedBudgets.length === 0) {
        alert('<?= _l('no_budgets_selected'); ?>');
        return;
    }
    
    var budgetIds = [];
    selectedBudgets.each(function() {
        budgetIds.push($(this).val());
    });
    
    switch(action) {
        case 'approve':
            if (confirm('<?= _l('confirm_approve_budgets'); ?>')) {
                performBulkAction('approve', budgetIds);
            }
            break;
        case 'export':
            window.location.href = admin_url + 'project_enhancement/budget/export_selected?ids=' + budgetIds.join(',');
            break;
        case 'delete':
            if (confirm('<?= _l('confirm_delete_budgets'); ?>')) {
                performBulkAction('delete', budgetIds);
            }
            break;
    }
}

/**
 * Perform bulk action via AJAX
 */
function performBulkAction(action, budgetIds) {
    $.ajax({
        url: admin_url + 'project_enhancement/budget/bulk_action',
        type: 'POST',
        data: {
            action: action,
            budget_ids: budgetIds
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || '<?= _l('operation_failed'); ?>');
            }
        },
        error: function() {
            alert('<?= _l('operation_failed'); ?>');
        }
    });
}

/**
 * Quick approve budget
 */
function quickApproveBudget(budgetId) {
    if (!confirm('<?= _l('confirm_approve_budget'); ?>')) {
        return;
    }
    
    $.ajax({
        url: admin_url + 'project_enhancement/budget/approve/' + budgetId,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert(response.message || '<?= _l('operation_failed'); ?>');
            }
        },
        error: function() {
            alert('<?= _l('operation_failed'); ?>');
        }
    });
}
</script>

<style>
.budget-card {
    border: 1px solid #e4e7ea;
    border-radius: 8px;
    background: #fff;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.budget-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.budget-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e4e7ea;
}

.budget-checkbox {
    margin-right: 15px;
}

.budget-title {
    flex: 1;
}

.budget-title h5 {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.budget-title a {
    color: #333;
    text-decoration: none;
}

.budget-content {
    padding: 20px;
}

.budget-amounts {
    margin-bottom: 20px;
}

.amount-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
    font-size: 14px;
}

.amount-label {
    color: #666;
}

.amount-value {
    font-weight: 600;
}

.budget-progress {
    margin-bottom: 20px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 12px;
}

.budget-meta {
    font-size: 12px;
    color: #666;
}

.meta-item {
    margin-bottom: 5px;
}

.meta-item i {
    margin-right: 5px;
    width: 12px;
}

.utilization-display {
    text-align: center;
}

.progress-xs {
    height: 4px;
    margin-top: 3px;
}

.panel-header {
    background: #f8f9fa;
    border-bottom: 1px solid #e4e7ea;
    padding: 15px 20px;
}

.panel-title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
}

.budget-alerts {
    max-height: 300px;
    overflow-y: auto;
}

.alert-item {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border: 1px solid #e4e7ea;
    border-radius: 6px;
    margin-bottom: 10px;
    background: #fff;
}

.alert-danger {
    border-color: #dc3545;
    background-color: #f8d7da;
}

.alert-warning {
    border-color: #ffc107;
    background-color: #fff3cd;
}

.alert-info {
    border-color: #17a2b8;
    background-color: #d1ecf1;
}

.alert-icon {
    margin-right: 15px;
    font-size: 24px;
    width: 40px;
    text-align: center;
}

.alert-content {
    flex: 1;
}

.alert-content h6 {
    margin: 0 0 8px 0;
    font-weight: 600;
}

.alert-content p {
    margin: 0 0 10px 0;
    color: #666;
}

/* Chart container */
canvas {
    max-height: 300px !important;
}

/* Status colors */
.label-draft { background-color: #6c757d; }
.label-approved { background-color: #17a2b8; }
.label-active { background-color: #28a745; }
.label-completed { background-color: #007bff; }
.label-over_budget { background-color: #dc3545; }

.progress-bar-success { background-color: #28a745; }
.progress-bar-warning { background-color: #ffc107; }
.progress-bar-danger { background-color: #dc3545; }

/* Responsive adjustments */
@media (max-width: 768px) {
    .budget-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .budget-checkbox {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .budget-actions {
        margin-top: 10px;
    }
    
    .amount-item {
        font-size: 12px;
    }
}

/* Animation for progress bars */
.progress-bar {
    transition: width 0.6s ease;
}

/* Hover effects */
.budget-card:hover .budget-title a {
    color: #007bff;
}

.btn-group .dropdown-menu {
    min-width: 160px;
}
</style>