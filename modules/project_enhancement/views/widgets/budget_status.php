<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Load project enhancement models
$CI = &get_instance();
$CI->load->model('project_enhancement/budget_model');

// Get budget data
$total_allocated = $CI->budget_model->get_total_allocated_budget();
$total_spent = $CI->budget_model->get_total_spent_budget();
$budget_alerts = $CI->budget_model->get_budget_alerts_count();
$over_budget_projects = $CI->budget_model->get_over_budget_projects_count();

$utilization_percentage = $total_allocated > 0 ? round(($total_spent / $total_allocated) * 100, 1) : 0;
?>

<div class="widget widget-budget-status" id="widget-<?= create_widget_id(); ?>" data-name="<?= _l('budget_status_widget'); ?>">
    <div class="widget-dragger"></div>
    
    <div class="panel_s">
        <div class="panel-body">
            <div class="widget-header">
                <h4 class="no-margin">
                    <i class="fa fa-dollar-sign text-warning"></i>
                    <?= _l('budget_status'); ?>
                </h4>
                <div class="widget-actions">
                    <a href="<?= admin_url('project_enhancement/budget'); ?>" class="btn btn-default btn-xs">
                        <i class="fa fa-eye"></i> <?= _l('view_all'); ?>
                    </a>
                </div>
            </div>
            
            <div class="widget-content">
                <!-- Budget Overview -->
                <div class="budget-overview">
                    <div class="budget-circle">
                        <svg width="80" height="80" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="35" fill="none" stroke="#e4e7ea" stroke-width="6"/>
                            <circle cx="40" cy="40" r="35" fill="none" 
                                    stroke="<?= $utilization_percentage > 90 ? '#e74c3c' : ($utilization_percentage > 75 ? '#f39c12' : '#28a745'); ?>" 
                                    stroke-width="6"
                                    stroke-dasharray="<?= 2 * pi() * 35; ?>" 
                                    stroke-dashoffset="<?= 2 * pi() * 35 * (1 - $utilization_percentage / 100); ?>"
                                    transform="rotate(-90 40 40)"/>
                        </svg>
                        <div class="budget-text">
                            <span class="percentage"><?= $utilization_percentage; ?>%</span>
                        </div>
                    </div>
                    <div class="budget-info">
                        <h5><?= _l('budget_utilization'); ?></h5>
                        <p class="text-muted">
                            <?= app_format_money($total_spent, get_option('project_enhancement_currency')); ?> / 
                            <?= app_format_money($total_allocated, get_option('project_enhancement_currency')); ?>
                        </p>
                    </div>
                </div>
                
                <!-- Budget Statistics -->
                <div class="budget-stats">
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="stat-item">
                                <div class="stat-number text-success">
                                    <?= app_format_money($total_allocated, get_option('project_enhancement_currency')); ?>
                                </div>
                                <div class="stat-label"><?= _l('allocated'); ?></div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="stat-item">
                                <div class="stat-number text-info">
                                    <?= app_format_money($total_spent, get_option('project_enhancement_currency')); ?>
                                </div>
                                <div class="stat-label"><?= _l('spent'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="stat-item">
                                <div class="stat-number text-warning"><?= $budget_alerts; ?></div>
                                <div class="stat-label"><?= _l('alerts'); ?></div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="stat-item">
                                <div class="stat-number text-danger"><?= $over_budget_projects; ?></div>
                                <div class="stat-label"><?= _l('over_budget'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Budget Alerts -->
                <?php if ($budget_alerts > 0): ?>
                    <div class="budget-alerts">
                        <h6><?= _l('budget_alerts'); ?></h6>
                        <?php
                        $recent_alerts = $CI->budget_model->get_recent_budget_alerts(3);
                        if (!empty($recent_alerts)): ?>
                            <ul class="alert-list">
                                <?php foreach ($recent_alerts as $alert): ?>
                                    <li class="alert-item">
                                        <div class="alert-info">
                                            <div class="alert-message">
                                                <?= character_limiter($alert['message'], 35); ?>
                                            </div>
                                            <small class="text-muted"><?= $alert['project_name']; ?></small>
                                        </div>
                                        <div class="alert-severity">
                                            <span class="label label-<?= $alert['severity'] === 'high' ? 'danger' : ($alert['severity'] === 'medium' ? 'warning' : 'info'); ?> label-xs">
                                                <?= ucfirst($alert['severity']); ?>
                                            </span>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="no-alerts">
                        <p class="text-success text-center">
                            <i class="fa fa-check-circle"></i> <?= _l('no_budget_alerts'); ?>
                        </p>
                    </div>
                <?php endif; ?>
                
                <!-- Quick Actions -->
                <div class="widget-actions-footer">
                    <div class="btn-group btn-group-justified">
                        <a href="<?= admin_url('project_enhancement/budget/create'); ?>" class="btn btn-default btn-xs">
                            <i class="fa fa-plus"></i> <?= _l('add_budget'); ?>
                        </a>
                        <a href="<?= admin_url('project_enhancement/budget/reports'); ?>" class="btn btn-default btn-xs">
                            <i class="fa fa-chart-line"></i> <?= _l('reports'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.widget-budget-status {
    min-height: 280px;
}

.widget-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #e4e7ea;
}

.widget-content {
    padding: 0;
}

.budget-overview {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.budget-circle {
    position: relative;
    margin-right: 15px;
}

.budget-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.budget-text .percentage {
    font-size: 16px;
    font-weight: 600;
    color: #f39c12;
}

.budget-info h5 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
}

.budget-info p {
    margin: 0;
    font-size: 12px;
}

.budget-stats {
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
    padding: 10px 5px;
}

.stat-number {
    font-size: 12px;
    font-weight: 600;
    line-height: 1.2;
}

.stat-label {
    font-size: 10px;
    color: #6c757d;
    margin-top: 5px;
    text-transform: uppercase;
}

.budget-alerts h6 {
    margin: 0 0 10px 0;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
}

.alert-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.alert-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f1f2f3;
}

.alert-item:last-child {
    border-bottom: none;
}

.alert-info {
    flex: 1;
}

.alert-message {
    font-size: 12px;
    font-weight: 500;
    color: #333;
    line-height: 1.2;
    margin-bottom: 2px;
}

.alert-info small {
    font-size: 10px;
    line-height: 1;
}

.alert-severity .label-xs {
    font-size: 9px;
    padding: 2px 6px;
}

.no-alerts {
    padding: 20px;
    text-align: center;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 20px;
}

.widget-actions-footer {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e4e7ea;
}

.widget-actions-footer .btn {
    font-size: 11px;
    padding: 6px 12px;
}

/* Animation for budget circle */
.budget-circle circle:last-child {
    transition: stroke-dashoffset 0.8s ease-in-out;
}
</style>