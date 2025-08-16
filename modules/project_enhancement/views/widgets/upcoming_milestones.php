<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Load project enhancement models
$CI = &get_instance();
$CI->load->model('project_enhancement/milestones_model');

// Get upcoming milestones data
$upcoming_milestones = $CI->milestones_model->get_upcoming_milestones(5);
$overdue_milestones = $CI->milestones_model->get_overdue_milestones(3);
$due_this_week = $CI->milestones_model->get_milestones_due_this_week();
?>

<div class="widget widget-upcoming-milestones" id="widget-<?= create_widget_id(); ?>" data-name="<?= _l('upcoming_milestones_widget'); ?>">
    <div class="widget-dragger"></div>
    
    <div class="panel_s">
        <div class="panel-body">
            <div class="widget-header">
                <h4 class="no-margin">
                    <i class="fa fa-calendar-alt text-primary"></i>
                    <?= _l('upcoming_milestones'); ?>
                </h4>
                <div class="widget-actions">
                    <a href="<?= admin_url('project_enhancement/milestones'); ?>" class="btn btn-default btn-xs">
                        <i class="fa fa-eye"></i> <?= _l('view_all'); ?>
                    </a>
                </div>
            </div>
            
            <div class="widget-content">
                <!-- Quick Stats -->
                <div class="milestone-stats">
                    <div class="row">
                        <div class="col-xs-4">
                            <div class="stat-item">
                                <div class="stat-number text-info"><?= count($upcoming_milestones); ?></div>
                                <div class="stat-label"><?= _l('upcoming'); ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="stat-item">
                                <div class="stat-number text-warning"><?= count($due_this_week); ?></div>
                                <div class="stat-label"><?= _l('this_week'); ?></div>
                            </div>
                        </div>
                        <div class="col-xs-4">
                            <div class="stat-item">
                                <div class="stat-number text-danger"><?= count($overdue_milestones); ?></div>
                                <div class="stat-label"><?= _l('overdue'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Overdue Milestones (Priority) -->
                <?php if (!empty($overdue_milestones)): ?>
                    <div class="overdue-section">
                        <h6 class="section-title text-danger">
                            <i class="fa fa-exclamation-triangle"></i> <?= _l('overdue_milestones'); ?>
                        </h6>
                        <ul class="milestone-list">
                            <?php foreach ($overdue_milestones as $milestone): ?>
                                <li class="milestone-item overdue">
                                    <div class="milestone-info">
                                        <a href="<?= admin_url('project_enhancement/milestones/view/' . $milestone['id']); ?>" class="milestone-name">
                                            <?= character_limiter($milestone['name'], 25); ?>
                                        </a>
                                        <small class="text-muted"><?= $milestone['project_name']; ?></small>
                                        <div class="milestone-meta">
                                            <span class="overdue-badge">
                                                <?php 
                                                $days_overdue = (time() - strtotime($milestone['due_date'])) / (60 * 60 * 24);
                                                echo floor($days_overdue) . ' ' . _l('days_overdue');
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="milestone-progress">
                                        <div class="progress progress-xs">
                                            <div class="progress-bar progress-bar-danger" 
                                                 style="width: <?= $milestone['progress_percentage']; ?>%">
                                            </div>
                                        </div>
                                        <small><?= $milestone['progress_percentage']; ?>%</small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Upcoming Milestones -->
                <div class="upcoming-section">
                    <h6 class="section-title">
                        <i class="fa fa-calendar"></i> <?= _l('upcoming_milestones'); ?>
                    </h6>
                    <?php if (!empty($upcoming_milestones)): ?>
                        <ul class="milestone-list">
                            <?php foreach ($upcoming_milestones as $milestone): ?>
                                <li class="milestone-item">
                                    <div class="milestone-info">
                                        <a href="<?= admin_url('project_enhancement/milestones/view/' . $milestone['id']); ?>" class="milestone-name">
                                            <?= character_limiter($milestone['name'], 25); ?>
                                        </a>
                                        <small class="text-muted"><?= $milestone['project_name']; ?></small>
                                        <div class="milestone-meta">
                                            <span class="due-date">
                                                <?php
                                                $due_date = strtotime($milestone['due_date']);
                                                $days_until = ceil(($due_date - time()) / (60 * 60 * 24));
                                                
                                                if ($days_until == 0) {
                                                    echo '<span class="text-warning">' . _l('due_today') . '</span>';
                                                } elseif ($days_until == 1) {
                                                    echo '<span class="text-warning">' . _l('due_tomorrow') . '</span>';
                                                } elseif ($days_until <= 7) {
                                                    echo '<span class="text-info">' . $days_until . ' ' . _l('days_left') . '</span>';
                                                } else {
                                                    echo '<span class="text-muted">' . _d($milestone['due_date']) . '</span>';
                                                }
                                                ?>
                                            </span>
                                            <span class="priority-badge priority-<?= $milestone['priority']; ?>">
                                                <?= ucfirst($milestone['priority']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="milestone-progress">
                                        <div class="progress progress-xs">
                                            <div class="progress-bar progress-bar-<?= $milestone['progress_percentage'] >= 75 ? 'success' : ($milestone['progress_percentage'] >= 50 ? 'info' : 'warning'); ?>" 
                                                 style="width: <?= $milestone['progress_percentage']; ?>%">
                                            </div>
                                        </div>
                                        <small><?= $milestone['progress_percentage']; ?>%</small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted text-center"><?= _l('no_upcoming_milestones'); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Actions -->
                <div class="widget-actions-footer">
                    <div class="btn-group btn-group-justified">
                        <a href="<?= admin_url('project_enhancement/milestones/create'); ?>" class="btn btn-default btn-xs">
                            <i class="fa fa-plus"></i> <?= _l('new_milestone'); ?>
                        </a>
                        <a href="<?= admin_url('project_enhancement/milestones/gantt'); ?>" class="btn btn-default btn-xs">
                            <i class="fa fa-sitemap"></i> <?= _l('gantt_view'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.widget-upcoming-milestones {
    min-height: 350px;
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

.milestone-stats {
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 6px;
}

.stat-item {
    text-align: center;
    padding: 5px;
}

.stat-number {
    font-size: 18px;
    font-weight: 600;
    line-height: 1;
}

.stat-label {
    font-size: 10px;
    color: #6c757d;
    margin-top: 5px;
    text-transform: uppercase;
}

.section-title {
    margin: 0 0 15px 0;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    padding-bottom: 5px;
    border-bottom: 1px solid #f1f2f3;
}

.overdue-section {
    margin-bottom: 25px;
}

.upcoming-section {
    margin-bottom: 20px;
}

.milestone-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.milestone-item {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 10px 0;
    border-bottom: 1px solid #f1f2f3;
}

.milestone-item:last-child {
    border-bottom: none;
}

.milestone-item.overdue {
    background: #fdf2f2;
    padding: 10px;
    border-radius: 4px;
    border: 1px solid #f5c6cb;
    margin-bottom: 8px;
}

.milestone-item.overdue:last-child {
    margin-bottom: 0;
}

.milestone-info {
    flex: 1;
    padding-right: 10px;
}

.milestone-name {
    display: block;
    font-size: 12px;
    font-weight: 500;
    color: #333;
    text-decoration: none;
    line-height: 1.2;
    margin-bottom: 3px;
}

.milestone-name:hover {
    color: #3498db;
    text-decoration: none;
}

.milestone-info small {
    font-size: 10px;
    line-height: 1;
    display: block;
    margin-bottom: 5px;
}

.milestone-meta {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.overdue-badge {
    background: #dc3545;
    color: white;
    padding: 1px 4px;
    border-radius: 2px;
    font-size: 9px;
    font-weight: 500;
}

.due-date {
    font-size: 9px;
    font-weight: 500;
}

.priority-badge {
    padding: 1px 4px;
    border-radius: 2px;
    font-size: 8px;
    font-weight: 500;
    text-transform: uppercase;
}

.priority-low {
    background: #d1ecf1;
    color: #0c5460;
}

.priority-medium {
    background: #fff3cd;
    color: #856404;
}

.priority-high {
    background: #f8d7da;
    color: #721c24;
}

.priority-critical {
    background: #dc3545;
    color: white;
}

.milestone-progress {
    text-align: right;
    min-width: 60px;
}

.progress-xs {
    height: 4px;
    margin-bottom: 3px;
}

.milestone-progress small {
    font-size: 9px;
    color: #6c757d;
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
</style>