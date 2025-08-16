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
                                    <i class="fa fa-chart-bar"></i> 
                                    <?= _l('project_enhancement_reports'); ?>
                                </h4>
                                <p class="text-muted mtop5"><?= _l('comprehensive_project_analytics'); ?></p>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                            <i class="fa fa-calendar"></i> <?= _l('date_range'); ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="#" onclick="setDateRange('today')"><?= _l('today'); ?></a></li>
                                            <li><a href="#" onclick="setDateRange('week')"><?= _l('this_week'); ?></a></li>
                                            <li><a href="#" onclick="setDateRange('month')"><?= _l('this_month'); ?></a></li>
                                            <li><a href="#" onclick="setDateRange('quarter')"><?= _l('this_quarter'); ?></a></li>
                                            <li><a href="#" onclick="setDateRange('year')"><?= _l('this_year'); ?></a></li>
                                            <li class="divider"></li>
                                            <li><a href="#" onclick="showCustomDateRange()"><?= _l('custom_range'); ?></a></li>
                                        </ul>
                                    </div>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                            <i class="fa fa-download"></i> <?= _l('export'); ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="#" onclick="exportReport('pdf')"><?= _l('export_pdf'); ?></a></li>
                                            <li><a href="#" onclick="exportReport('excel')"><?= _l('export_excel'); ?></a></li>
                                            <li><a href="#" onclick="exportReport('csv')"><?= _l('export_csv'); ?></a></li>
                                        </ul>
                                    </div>
                                    <button type="button" class="btn btn-info" onclick="refreshReports()">
                                        <i class="fa fa-refresh"></i> <?= _l('refresh'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- KPI Dashboard -->
        <div class="row">
            <div class="col-md-3">
                <div class="panel_s kpi-card">
                    <div class="panel-body text-center">
                        <div class="kpi-icon">
                            <i class="fa fa-project-diagram text-info"></i>
                        </div>
                        <h3 class="kpi-value text-info"><?= $kpi_data['total_projects'] ?? 0; ?></h3>
                        <p class="kpi-label"><?= _l('total_projects'); ?></p>
                        <div class="kpi-trend">
                            <span class="trend-value text-<?= ($kpi_data['projects_trend'] ?? 0) >= 0 ? 'success' : 'danger'; ?>">
                                <i class="fa fa-arrow-<?= ($kpi_data['projects_trend'] ?? 0) >= 0 ? 'up' : 'down'; ?>"></i>
                                <?= abs($kpi_data['projects_trend'] ?? 0); ?>%
                            </span>
                            <small class="text-muted"><?= _l('vs_last_period'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s kpi-card">
                    <div class="panel-body text-center">
                        <div class="kpi-icon">
                            <i class="fa fa-flag text-success"></i>
                        </div>
                        <h3 class="kpi-value text-success"><?= $kpi_data['completed_milestones'] ?? 0; ?></h3>
                        <p class="kpi-label"><?= _l('completed_milestones'); ?></p>
                        <div class="kpi-trend">
                            <span class="trend-value text-<?= ($kpi_data['milestones_trend'] ?? 0) >= 0 ? 'success' : 'danger'; ?>">
                                <i class="fa fa-arrow-<?= ($kpi_data['milestones_trend'] ?? 0) >= 0 ? 'up' : 'down'; ?>"></i>
                                <?= abs($kpi_data['milestones_trend'] ?? 0); ?>%
                            </span>
                            <small class="text-muted"><?= _l('vs_last_period'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s kpi-card">
                    <div class="panel-body text-center">
                        <div class="kpi-icon">
                            <i class="fa fa-clock text-warning"></i>
                        </div>
                        <h3 class="kpi-value text-warning"><?= number_format($kpi_data['total_hours'] ?? 0, 1); ?>h</h3>
                        <p class="kpi-label"><?= _l('total_hours_logged'); ?></p>
                        <div class="kpi-trend">
                            <span class="trend-value text-<?= ($kpi_data['hours_trend'] ?? 0) >= 0 ? 'success' : 'danger'; ?>">
                                <i class="fa fa-arrow-<?= ($kpi_data['hours_trend'] ?? 0) >= 0 ? 'up' : 'down'; ?>"></i>
                                <?= abs($kpi_data['hours_trend'] ?? 0); ?>%
                            </span>
                            <small class="text-muted"><?= _l('vs_last_period'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s kpi-card">
                    <div class="panel-body text-center">
                        <div class="kpi-icon">
                            <i class="fa fa-dollar-sign text-primary"></i>
                        </div>
                        <h3 class="kpi-value text-primary">$<?= number_format($kpi_data['total_budget'] ?? 0, 0); ?></h3>
                        <p class="kpi-label"><?= _l('total_budget'); ?></p>
                        <div class="kpi-trend">
                            <span class="trend-value text-<?= ($kpi_data['budget_trend'] ?? 0) >= 0 ? 'success' : 'danger'; ?>">
                                <i class="fa fa-arrow-<?= ($kpi_data['budget_trend'] ?? 0) >= 0 ? 'up' : 'down'; ?>"></i>
                                <?= abs($kpi_data['budget_trend'] ?? 0); ?>%
                            </span>
                            <small class="text-muted"><?= _l('vs_last_period'); ?></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 1 -->
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('project_progress_overview'); ?></h5>
                        <div class="panel-actions">
                            <div class="btn-group btn-group-xs">
                                <button type="button" class="btn btn-default active" onclick="switchProgressView('monthly')">
                                    <?= _l('monthly'); ?>
                                </button>
                                <button type="button" class="btn btn-default" onclick="switchProgressView('weekly')">
                                    <?= _l('weekly'); ?>
                                </button>
                                <button type="button" class="btn btn-default" onclick="switchProgressView('daily')">
                                    <?= _l('daily'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <canvas id="project-progress-chart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('project_status_distribution'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <canvas id="project-status-chart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row 2 -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('time_tracking_analytics'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <canvas id="time-tracking-chart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('budget_utilization_trends'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <canvas id="budget-utilization-chart" height="250"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resource Analytics -->
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('resource_utilization_heatmap'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <canvas id="resource-heatmap-chart" height="250"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('top_performers'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($top_performers)): ?>
                            <div class="performers-list">
                                <?php foreach ($top_performers as $index => $performer): ?>
                                    <div class="performer-item">
                                        <div class="performer-rank">
                                            <span class="rank-badge rank-<?= $index + 1; ?>"><?= $index + 1; ?></span>
                                        </div>
                                        <div class="performer-avatar">
                                            <img src="<?= staff_profile_image_url($performer['staff_id']); ?>" 
                                                 alt="<?= $performer['name']; ?>" class="img-circle">
                                        </div>
                                        <div class="performer-info">
                                            <h6><?= $performer['name']; ?></h6>
                                            <p class="text-muted"><?= $performer['role']; ?></p>
                                        </div>
                                        <div class="performer-stats">
                                            <div class="stat-value"><?= number_format($performer['hours'], 1); ?>h</div>
                                            <div class="stat-label"><?= _l('hours_logged'); ?></div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fa fa-users fa-2x text-muted"></i>
                                <p class="text-muted"><?= _l('no_performance_data'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Analytics Tables -->
        <div class="row">
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('project_performance_summary'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th><?= _l('project'); ?></th>
                                        <th><?= _l('progress'); ?></th>
                                        <th><?= _l('budget_status'); ?></th>
                                        <th><?= _l('timeline'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($project_summary)): ?>
                                        <?php foreach ($project_summary as $project): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= admin_url('projects/view/' . $project['id']); ?>">
                                                        <?= character_limiter($project['name'], 25); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="progress progress-xs">
                                                        <div class="progress-bar progress-bar-<?= $project['progress'] >= 75 ? 'success' : ($project['progress'] >= 50 ? 'info' : 'warning'); ?>" 
                                                             style="width: <?= $project['progress']; ?>%">
                                                        </div>
                                                    </div>
                                                    <small><?= $project['progress']; ?>%</small>
                                                </td>
                                                <td>
                                                    <span class="label label-<?= $project['budget_status_color']; ?>">
                                                        <?= $project['budget_utilization']; ?>%
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="text-<?= $project['timeline_status_color']; ?>">
                                                        <i class="fa <?= $project['timeline_icon']; ?>"></i>
                                                        <?= $project['timeline_status']; ?>
                                                    </span>
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
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('milestone_completion_trends'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <canvas id="milestone-trends-chart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Advanced Analytics -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('advanced_analytics'); ?></h5>
                        <div class="panel-actions">
                            <div class="btn-group btn-group-xs">
                                <button type="button" class="btn btn-default active" onclick="switchAnalyticsTab('productivity')">
                                    <?= _l('productivity'); ?>
                                </button>
                                <button type="button" class="btn btn-default" onclick="switchAnalyticsTab('efficiency')">
                                    <?= _l('efficiency'); ?>
                                </button>
                                <button type="button" class="btn btn-default" onclick="switchAnalyticsTab('forecasting')">
                                    <?= _l('forecasting'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <!-- Productivity Tab -->
                        <div id="productivity-analytics" class="analytics-tab active">
                            <div class="row">
                                <div class="col-md-8">
                                    <canvas id="productivity-chart" height="200"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div class="productivity-metrics">
                                        <div class="metric-item">
                                            <h4 class="metric-value text-info"><?= number_format($analytics_data['avg_hours_per_project'] ?? 0, 1); ?>h</h4>
                                            <p class="metric-label"><?= _l('avg_hours_per_project'); ?></p>
                                        </div>
                                        <div class="metric-item">
                                            <h4 class="metric-value text-success"><?= number_format($analytics_data['completion_rate'] ?? 0, 1); ?>%</h4>
                                            <p class="metric-label"><?= _l('completion_rate'); ?></p>
                                        </div>
                                        <div class="metric-item">
                                            <h4 class="metric-value text-warning"><?= number_format($analytics_data['avg_project_duration'] ?? 0, 0); ?> <?= _l('days'); ?></h4>
                                            <p class="metric-label"><?= _l('avg_project_duration'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Efficiency Tab -->
                        <div id="efficiency-analytics" class="analytics-tab" style="display: none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <canvas id="efficiency-chart" height="200"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <canvas id="resource-efficiency-chart" height="200"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Forecasting Tab -->
                        <div id="forecasting-analytics" class="analytics-tab" style="display: none;">
                            <div class="row">
                                <div class="col-md-8">
                                    <canvas id="forecasting-chart" height="200"></canvas>
                                </div>
                                <div class="col-md-4">
                                    <div class="forecasting-insights">
                                        <div class="insight-item">
                                            <h6><?= _l('projected_completion'); ?></h6>
                                            <p class="text-info"><?= $forecasting_data['projected_completion'] ?? _l('calculating'); ?></p>
                                        </div>
                                        <div class="insight-item">
                                            <h6><?= _l('budget_forecast'); ?></h6>
                                            <p class="text-warning">$<?= number_format($forecasting_data['budget_forecast'] ?? 0, 0); ?></p>
                                        </div>
                                        <div class="insight-item">
                                            <h6><?= _l('resource_demand'); ?></h6>
                                            <p class="text-success"><?= $forecasting_data['resource_demand'] ?? _l('optimal'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alerts and Recommendations -->
        <?php if (!empty($recommendations)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s panel-info">
                        <div class="panel-header">
                            <h5 class="panel-title">
                                <i class="fa fa-lightbulb"></i> <?= _l('ai_recommendations'); ?>
                            </h5>
                        </div>
                        <div class="panel-body">
                            <div class="recommendations-grid">
                                <?php foreach ($recommendations as $recommendation): ?>
                                    <div class="recommendation-card">
                                        <div class="recommendation-icon">
                                            <i class="fa <?= $recommendation['icon']; ?> text-<?= $recommendation['type']; ?>"></i>
                                        </div>
                                        <div class="recommendation-content">
                                            <h6><?= $recommendation['title']; ?></h6>
                                            <p><?= $recommendation['description']; ?></p>
                                            <div class="recommendation-impact">
                                                <span class="impact-label"><?= _l('expected_impact'); ?>:</span>
                                                <span class="impact-value text-<?= $recommendation['impact_color']; ?>">
                                                    <?= $recommendation['impact']; ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="recommendation-action">
                                            <?php if (!empty($recommendation['action_url'])): ?>
                                                <a href="<?= $recommendation['action_url']; ?>" class="btn btn-sm btn-<?= $recommendation['type']; ?>">
                                                    <?= $recommendation['action_text']; ?>
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

<!-- Custom Date Range Modal -->
<div class="modal fade" id="custom-date-range-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= _l('custom_date_range'); ?></h4>
            </div>
            <div class="modal-body">
                <?= form_open('', ['id' => 'custom-date-range-form']); ?>
                <div class="row">
                    <div class="col-md-6">
                        <?= render_date_input('custom_date_from', 'date_from', '', ['required' => true]); ?>
                    </div>
                    <div class="col-md-6">
                        <?= render_date_input('custom_date_to', 'date_to', '', ['required' => true]); ?>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('cancel'); ?></button>
                <button type="button" class="btn btn-info" onclick="applyCustomDateRange()"><?= _l('apply'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
    'use strict';
    
    // Initialize all charts
    initializeCharts();
    
    // Auto-refresh every 5 minutes
    setInterval(function() {
        refreshReports();
    }, 300000);
});

/**
 * Initialize all charts
 */
function initializeCharts() {
    initializeProjectProgressChart();
    initializeProjectStatusChart();
    initializeTimeTrackingChart();
    initializeBudgetUtilizationChart();
    initializeResourceHeatmapChart();
    initializeMilestoneTrendsChart();
    initializeProductivityChart();
    initializeEfficiencyCharts();
    initializeForecastingChart();
}

/**
 * Initialize project progress chart
 */
function initializeProjectProgressChart() {
    var ctx = document.getElementById('project-progress-chart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_data['progress']['labels'] ?? []); ?>,
            datasets: [{
                label: '<?= _l('planned_progress'); ?>',
                data: <?= json_encode($chart_data['progress']['planned'] ?? []); ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }, {
                label: '<?= _l('actual_progress'); ?>',
                data: <?= json_encode($chart_data['progress']['actual'] ?? []); ?>,
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
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + context.parsed.y + '%';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize project status chart
 */
function initializeProjectStatusChart() {
    var ctx = document.getElementById('project-status-chart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($chart_data['status']['labels'] ?? []); ?>,
            datasets: [{
                data: <?= json_encode($chart_data['status']['data'] ?? []); ?>,
                backgroundColor: ['#28a745', '#007bff', '#ffc107', '#dc3545', '#6c757d']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

/**
 * Initialize time tracking chart
 */
function initializeTimeTrackingChart() {
    var ctx = document.getElementById('time-tracking-chart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_data['time']['labels'] ?? []); ?>,
            datasets: [{
                label: '<?= _l('hours_logged'); ?>',
                data: <?= json_encode($chart_data['time']['hours'] ?? []); ?>,
                backgroundColor: 'rgba(255, 193, 7, 0.8)',
                borderColor: '#ffc107',
                borderWidth: 1
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
                            return value + 'h';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize budget utilization chart
 */
function initializeBudgetUtilizationChart() {
    var ctx = document.getElementById('budget-utilization-chart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_data['budget']['labels'] ?? []); ?>,
            datasets: [{
                label: '<?= _l('budget_utilization'); ?>',
                data: <?= json_encode($chart_data['budget']['utilization'] ?? []); ?>,
                borderColor: '#dc3545',
                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 150,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize resource heatmap chart
 */
function initializeResourceHeatmapChart() {
    var ctx = document.getElementById('resource-heatmap-chart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($chart_data['resources']['staff'] ?? []); ?>,
            datasets: [{
                label: '<?= _l('utilization_percentage'); ?>',
                data: <?= json_encode($chart_data['resources']['utilization'] ?? []); ?>,
                backgroundColor: function(context) {
                    var value = context.parsed.y;
                    if (value > 90) return 'rgba(220, 53, 69, 0.8)';
                    if (value > 70) return 'rgba(255, 193, 7, 0.8)';
                    return 'rgba(40, 167, 69, 0.8)';
                },
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Initialize milestone trends chart
 */
function initializeMilestoneTrendsChart() {
    var ctx = document.getElementById('milestone-trends-chart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'area',
        data: {
            labels: <?= json_encode($chart_data['milestones']['labels'] ?? []); ?>,
            datasets: [{
                label: '<?= _l('completed_milestones'); ?>',
                data: <?= json_encode($chart_data['milestones']['completed'] ?? []); ?>,
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Initialize productivity chart
 */
function initializeProductivityChart() {
    var ctx = document.getElementById('productivity-chart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'radar',
        data: {
            labels: <?= json_encode($analytics_data['productivity']['labels'] ?? []); ?>,
            datasets: [{
                label: '<?= _l('current_period'); ?>',
                data: <?= json_encode($analytics_data['productivity']['current'] ?? []); ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.2)',
                pointBackgroundColor: '#007bff'
            }, {
                label: '<?= _l('previous_period'); ?>',
                data: <?= json_encode($analytics_data['productivity']['previous'] ?? []); ?>,
                borderColor: '#6c757d',
                backgroundColor: 'rgba(108, 117, 125, 0.1)',
                pointBackgroundColor: '#6c757d'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

/**
 * Initialize efficiency charts
 */
function initializeEfficiencyCharts() {
    // Efficiency Chart
    var efficiencyCtx = document.getElementById('efficiency-chart');
    if (efficiencyCtx) {
        new Chart(efficiencyCtx, {
            type: 'scatter',
            data: {
                datasets: [{
                    label: '<?= _l('project_efficiency'); ?>',
                    data: <?= json_encode($analytics_data['efficiency']['projects'] ?? []); ?>,
                    backgroundColor: 'rgba(0, 123, 255, 0.6)',
                    borderColor: '#007bff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: '<?= _l('planned_hours'); ?>'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: '<?= _l('actual_hours'); ?>'
                        }
                    }
                }
            }
        });
    }
    
    // Resource Efficiency Chart
    var resourceEfficiencyCtx = document.getElementById('resource-efficiency-chart');
    if (resourceEfficiencyCtx) {
        new Chart(resourceEfficiencyCtx, {
            type: 'bubble',
            data: {
                datasets: [{
                    label: '<?= _l('resource_efficiency'); ?>',
                    data: <?= json_encode($analytics_data['resource_efficiency'] ?? []); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.6)',
                    borderColor: '#28a745'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: '<?= _l('utilization_rate'); ?>'
                        }
                    },
                    y: {
                        title: {
                            display: true,
                            text: '<?= _l('productivity_score'); ?>'
                        }
                    }
                }
            }
        });
    }
}

/**
 * Initialize forecasting chart
 */
function initializeForecastingChart() {
    var ctx = document.getElementById('forecasting-chart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($forecasting_data['labels'] ?? []); ?>,
            datasets: [{
                label: '<?= _l('historical'); ?>',
                data: <?= json_encode($forecasting_data['historical'] ?? []); ?>,
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4
            }, {
                label: '<?= _l('forecast'); ?>',
                data: <?= json_encode($forecasting_data['forecast'] ?? []); ?>,
                borderColor: '#ffc107',
                backgroundColor: 'rgba(255, 193, 7, 0.1)',
                borderDash: [5, 5],
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

/**
 * Switch progress view
 */
function switchProgressView(period) {
    $('.btn-group button').removeClass('active');
    $('button[onclick="switchProgressView(\'' + period + '\')"]').addClass('active');
    
    // Reload chart with new period data
    // Implementation would fetch new data and update chart
}

/**
 * Switch analytics tab
 */
function switchAnalyticsTab(tab) {
    $('.analytics-tab').hide();
    $('#' + tab + '-analytics').show();
    
    $('.btn-group button').removeClass('active');
    $('button[onclick="switchAnalyticsTab(\'' + tab + '\')"]').addClass('active');
}

/**
 * Set date range
 */
function setDateRange(range) {
    // Implementation would update date range and refresh reports
    window.location.href = admin_url + 'project_enhancement/reports?range=' + range;
}

/**
 * Show custom date range modal
 */
function showCustomDateRange() {
    $('#custom-date-range-modal').modal('show');
}

/**
 * Apply custom date range
 */
function applyCustomDateRange() {
    var dateFrom = $('#custom_date_from').val();
    var dateTo = $('#custom_date_to').val();
    
    if (!dateFrom || !dateTo) {
        alert('<?= _l('please_select_date_range'); ?>');
        return;
    }
    
    window.location.href = admin_url + 'project_enhancement/reports?date_from=' + dateFrom + '&date_to=' + dateTo;
}

/**
 * Export report
 */
function exportReport(format) {
    window.open(admin_url + 'project_enhancement/reports/export/' + format, '_blank');
}

/**
 * Refresh reports
 */
function refreshReports() {
    location.reload();
}
</script>

<style>
.kpi-card {
    transition: all 0.3s ease;
}

.kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

.kpi-icon {
    font-size: 2.5rem;
    margin-bottom: 15px;
}

.kpi-value {
    margin-bottom: 5px;
    font-weight: 600;
}

.kpi-label {
    color: #666;
    margin-bottom: 10px;
    font-size: 13px;
}

.kpi-trend {
    font-size: 12px;
}

.trend-value {
    font-weight: 600;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    border-bottom: 1px solid #e4e7ea;
    padding: 15px 20px;
}

.panel-title {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
}

.panel-actions .btn-group {
    margin-left: 10px;
}

.performers-list {
    max-height: 300px;
    overflow-y: auto;
}

.performer-item {
    display: flex;
    align-items: center;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.performer-item:last-child {
    border-bottom: none;
}

.performer-rank {
    margin-right: 15px;
}

.rank-badge {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 12px;
}

.rank-1 { background-color: #ffd700; }
.rank-2 { background-color: #c0c0c0; }
.rank-3 { background-color: #cd7f32; }
.rank-badge:not(.rank-1):not(.rank-2):not(.rank-3) { background-color: #6c757d; }

.performer-avatar {
    margin-right: 15px;
}

.performer-avatar img {
    width: 40px;
    height: 40px;
    object-fit: cover;
}

.performer-info {
    flex: 1;
}

.performer-info h6 {
    margin: 0 0 2px 0;
    font-weight: 600;
}

.performer-stats {
    text-align: center;
}

.stat-value {
    font-weight: 600;
    color: #333;
    display: block;
}

.stat-label {
    font-size: 11px;
    color: #666;
}

.progress-xs {
    height: 4px;
}

.analytics-tab {
    min-height: 250px;
}

.productivity-metrics {
    padding: 20px 0;
}

.metric-item {
    text-align: center;
    margin-bottom: 30px;
}

.metric-value {
    margin-bottom: 5px;
    font-weight: 600;
}

.metric-label {
    color: #666;
    font-size: 13px;
    margin: 0;
}

.forecasting-insights {
    padding: 20px 0;
}

.insight-item {
    margin-bottom: 25px;
}

.insight-item h6 {
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.insight-item p {
    margin: 0;
    font-weight: 600;
}

.recommendations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.recommendation-card {
    display: flex;
    align-items: flex-start;
    padding: 20px;
    border: 1px solid #e4e7ea;
    border-radius: 8px;
    background: #fff;
    transition: all 0.3s ease;
}

.recommendation-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.recommendation-icon {
    margin-right: 15px;
    font-size: 24px;
    width: 40px;
    text-align: center;
}

.recommendation-content {
    flex: 1;
}

.recommendation-content h6 {
    margin: 0 0 8px 0;
    font-weight: 600;
}

.recommendation-content p {
    margin: 0 0 10px 0;
    color: #666;
    line-height: 1.4;
}

.recommendation-impact {
    font-size: 12px;
}

.impact-label {
    color: #666;
}

.impact-value {
    font-weight: 600;
}

.recommendation-action {
    margin-left: 15px;
}

/* Chart containers */
canvas {
    max-height: 300px !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .recommendations-grid {
        grid-template-columns: 1fr;
    }
    
    .panel-header {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .panel-actions {
        margin-top: 10px;
    }
    
    .performer-item {
        flex-direction: column;
        text-align: center;
    }
    
    .performer-rank,
    .performer-avatar {
        margin-right: 0;
        margin-bottom: 10px;
    }
}

/* Animation for charts */
@keyframes chartFadeIn {
    from { opacity: 0; transform: scale(0.9); }
    to { opacity: 1; transform: scale(1); }
}

.panel_s canvas {
    animation: chartFadeIn 0.5s ease-in-out;
}

/* Status colors */
.text-success { color: #28a745 !important; }
.text-warning { color: #ffc107 !important; }
.text-danger { color: #dc3545 !important; }
.text-info { color: #17a2b8 !important; }
</style>