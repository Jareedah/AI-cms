<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Load project enhancement models
$CI = &get_instance();
$CI->load->model('project_enhancement/milestones_model');
$CI->load->model('projects_model');

// Get project progress data
$total_projects = $CI->projects_model->get('', 'active')->num_rows();
$total_milestones = $CI->milestones_model->get_total_milestones();
$completed_milestones = $CI->milestones_model->get_completed_milestones_count();
$overdue_milestones = $CI->milestones_model->get_overdue_milestones_count();

$progress_percentage = $total_milestones > 0 ? round(($completed_milestones / $total_milestones) * 100, 1) : 0;
?>

<div class="widget widget-project-progress" id="widget-<?= create_widget_id(); ?>" data-name="<?= _l('project_progress_widget'); ?>">
    <div class="widget-dragger"></div>
    
    <div class="panel_s">
        <div class="panel-body">
            <div class="widget-header">
                <h4 class="no-margin">
                    <i class="fa fa-project-diagram text-info"></i>
                    <?= _l('project_progress'); ?>
                </h4>
                <div class="widget-actions">
                    <a href="<?= admin_url('project_enhancement/milestones'); ?>" class="btn btn-default btn-xs">
                        <i class="fa fa-eye"></i> <?= _l('view_all'); ?>
                    </a>
                </div>
            </div>
            
            <div class="widget-content">
                <!-- Overall Progress -->
                <div class="progress-overview">
                    <div class="progress-circle">
                        <svg width="80" height="80" viewBox="0 0 80 80">
                            <circle cx="40" cy="40" r="35" fill="none" stroke="#e4e7ea" stroke-width="6"/>
                            <circle cx="40" cy="40" r="35" fill="none" stroke="#3498db" stroke-width="6"
                                    stroke-dasharray="<?= 2 * pi() * 35; ?>" 
                                    stroke-dashoffset="<?= 2 * pi() * 35 * (1 - $progress_percentage / 100); ?>"
                                    transform="rotate(-90 40 40)"/>
                        </svg>
                        <div class="progress-text">
                            <span class="percentage"><?= $progress_percentage; ?>%</span>
                        </div>
                    </div>
                    <div class="progress-info">
                        <h5><?= _l('overall_progress'); ?></h5>
                        <p class="text-muted"><?= $completed_milestones; ?> / <?= $total_milestones; ?> <?= _l('milestones_completed'); ?></p>
                    </div>
                </div>
                
                <!-- Statistics -->
                <div class="project-stats">
                    <div class="row">
                        <div class="col-xs-4">
                            <div class="stat-item">
                                <div class="stat-number text-info"><?= $total_projects; ?></div>
                                <div class="stat-label"><?= _l('active_projects'); ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="stat-item">
                                <div class="stat-number text-success"><?= $completed_milestones; ?></div>
                                <div class="stat-label"><?= _l('completed'); ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="stat-item">
                                <div class="stat-number text-danger"><?= $overdue_milestones; ?></div>
                                <div class="stat-label"><?= _l('overdue'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Milestones -->
                <div class="recent-milestones">
                    <h6><?= _l('recent_milestones'); ?></h6>
                    <?php
                    $recent_milestones = $CI->milestones_model->get_recent_milestones(3);
                    if (!empty($recent_milestones)): ?>
                        <ul class="milestone-list">
                            <?php foreach ($recent_milestones as $milestone): ?>
                                <li class="milestone-item">
                                    <div class="milestone-info">
                                        <a href="<?= admin_url('project_enhancement/milestones/view/' . $milestone['id']); ?>" class="milestone-name">
                                            <?= character_limiter($milestone['name'], 25); ?>
                                        </a>
                                        <small class="text-muted"><?= $milestone['project_name']; ?></small>
                                    </div>
                                    <div class="milestone-status">
                                        <span class="label label-<?= milestone_status_color($milestone['status']); ?> label-xs">
                                            <?= _l('milestone_status_' . $milestone['status']); ?>
                                        </span>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted text-center"><?= _l('no_milestones_found'); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Actions -->
                <div class="widget-actions-footer">
                    <div class="btn-group btn-group-justified">
                        <a href="<?= admin_url('project_enhancement/milestones/create'); ?>" class="btn btn-default btn-xs">
                            <i class="fa fa-plus"></i> <?= _l('new_milestone'); ?>
                        </a>
                        <a href="<?= admin_url('project_enhancement/reports'); ?>" class="btn btn-default btn-xs">
                            <i class="fa fa-chart-bar"></i> <?= _l('reports'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.widget-project-progress {
    min-height: 300px;
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

.progress-overview {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.progress-circle {
    position: relative;
    margin-right: 15px;
}

.progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.progress-text .percentage {
    font-size: 16px;
    font-weight: 600;
    color: #3498db;
}

.progress-info h5 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
}

.progress-info p {
    margin: 0;
    font-size: 12px;
}

.project-stats {
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
    padding: 10px 5px;
}

.stat-number {
    font-size: 20px;
    font-weight: 600;
    line-height: 1;
}

.stat-label {
    font-size: 11px;
    color: #6c757d;
    margin-top: 5px;
}

.recent-milestones h6 {
    margin: 0 0 10px 0;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
}

.milestone-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.milestone-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f1f2f3;
}

.milestone-item:last-child {
    border-bottom: none;
}

.milestone-info {
    flex: 1;
}

.milestone-name {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #333;
    text-decoration: none;
    line-height: 1.2;
}

.milestone-name:hover {
    color: #3498db;
    text-decoration: none;
}

.milestone-info small {
    font-size: 10px;
    line-height: 1;
}

.milestone-status .label-xs {
    font-size: 9px;
    padding: 2px 6px;
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

/* Animation for progress circle */
.progress-circle circle:last-child {
    transition: stroke-dashoffset 0.8s ease-in-out;
}
</style>