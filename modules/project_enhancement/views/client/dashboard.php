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
                            <div class="col-md-8">
                                <h4 class="no-margin">
                                    <i class="fa fa-project-diagram"></i> 
                                    <?= _l('project_dashboard'); ?>
                                </h4>
                                <p class="text-muted mtop5"><?= _l('project_progress_overview'); ?></p>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group">
                                    <a href="<?= site_url('clients/project/' . $project->id); ?>" class="btn btn-default">
                                        <i class="fa fa-eye"></i> <?= _l('project_details'); ?>
                                    </a>
                                    <?php if (get_option('project_enhancement_client_time_visibility')): ?>
                                        <a href="<?= site_url('clients/project_enhancement/time_tracking/' . $project->id); ?>" class="btn btn-default">
                                            <i class="fa fa-clock"></i> <?= _l('time_tracking'); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Overview Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <div class="project-stat-icon">
                            <i class="fa fa-flag text-info"></i>
                        </div>
                        <h3 class="text-info"><?= $project_stats['total_milestones'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('total_milestones'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <div class="project-stat-icon">
                            <i class="fa fa-check-circle text-success"></i>
                        </div>
                        <h3 class="text-success"><?= $project_stats['completed_milestones'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('completed_milestones'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <div class="project-stat-icon">
                            <i class="fa fa-clock text-warning"></i>
                        </div>
                        <h3 class="text-warning"><?= number_format($project_stats['total_hours'] ?? 0, 1); ?>h</h3>
                        <p class="text-muted"><?= _l('total_hours_logged'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <div class="project-stat-icon">
                            <i class="fa fa-percentage text-primary"></i>
                        </div>
                        <h3 class="text-primary"><?= number_format($project_stats['overall_progress'] ?? 0, 1); ?>%</h3>
                        <p class="text-muted"><?= _l('overall_progress'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Progress Overview -->
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('project_progress_timeline'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <div class="project-progress-container">
                            <div class="progress-header">
                                <span class="progress-label"><?= _l('overall_progress'); ?></span>
                                <span class="progress-percentage"><?= number_format($project_stats['overall_progress'] ?? 0, 1); ?>%</span>
                            </div>
                            <div class="progress progress-lg">
                                <div class="progress-bar progress-bar-<?= ($project_stats['overall_progress'] ?? 0) >= 75 ? 'success' : (($project_stats['overall_progress'] ?? 0) >= 50 ? 'info' : 'warning'); ?>" 
                                     style="width: <?= $project_stats['overall_progress'] ?? 0; ?>%">
                                </div>
                            </div>
                        </div>
                        
                        <div class="project-timeline">
                            <canvas id="project-timeline-chart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('project_information'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <div class="project-info">
                            <div class="info-item">
                                <strong><?= _l('project_name'); ?>:</strong>
                                <span><?= $project->name; ?></span>
                            </div>
                            <div class="info-item">
                                <strong><?= _l('start_date'); ?>:</strong>
                                <span><?= _d($project->start_date); ?></span>
                            </div>
                            <div class="info-item">
                                <strong><?= _l('deadline'); ?>:</strong>
                                <span><?= $project->deadline ? _d($project->deadline) : _l('no_deadline'); ?></span>
                            </div>
                            <div class="info-item">
                                <strong><?= _l('status'); ?>:</strong>
                                <span class="label label-<?= project_status_color($project->status); ?>">
                                    <?= _l('project_status_' . $project->status); ?>
                                </span>
                            </div>
                            <?php if (get_option('project_enhancement_client_budget_visibility') && !empty($project_budget)): ?>
                                <div class="info-item">
                                    <strong><?= _l('budget'); ?>:</strong>
                                    <span>$<?= number_format($project_budget['total_amount'], 2); ?></span>
                                </div>
                                <div class="info-item">
                                    <strong><?= _l('budget_used'); ?>:</strong>
                                    <span class="text-<?= $project_budget['utilization_percentage'] > 90 ? 'danger' : 'success'; ?>">
                                        <?= number_format($project_budget['utilization_percentage'], 1); ?>%
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Milestones Overview -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title">
                            <?= _l('project_milestones'); ?>
                            <span class="badge"><?= count($milestones ?? []); ?></span>
                        </h5>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($milestones)): ?>
                            <div class="milestones-grid">
                                <?php foreach ($milestones as $milestone): ?>
                                    <div class="milestone-card">
                                        <div class="milestone-header">
                                            <div class="milestone-status">
                                                <span class="status-indicator status-<?= $milestone['status']; ?>"></span>
                                            </div>
                                            <div class="milestone-title">
                                                <h6><?= $milestone['name']; ?></h6>
                                                <span class="label label-<?= milestone_status_color($milestone['status']); ?>">
                                                    <?= _l('milestone_status_' . $milestone['status']); ?>
                                                </span>
                                            </div>
                                            <div class="milestone-progress-circle">
                                                <div class="progress-circle" data-progress="<?= $milestone['progress_percentage']; ?>">
                                                    <span><?= $milestone['progress_percentage']; ?>%</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="milestone-content">
                                            <?php if (!empty($milestone['description'])): ?>
                                                <p class="milestone-description">
                                                    <?= character_limiter($milestone['description'], 100); ?>
                                                </p>
                                            <?php endif; ?>
                                            
                                            <div class="milestone-meta">
                                                <div class="meta-item">
                                                    <i class="fa fa-calendar"></i>
                                                    <span>
                                                        <?= _d($milestone['start_date']); ?>
                                                        <?php if ($milestone['due_date']): ?>
                                                            - <?= _d($milestone['due_date']); ?>
                                                        <?php endif; ?>
                                                    </span>
                                                </div>
                                                <?php if ($milestone['estimated_hours']): ?>
                                                    <div class="meta-item">
                                                        <i class="fa fa-clock"></i>
                                                        <span><?= $milestone['estimated_hours']; ?>h <?= _l('estimated'); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($milestone['assigned_to']): ?>
                                                    <div class="meta-item">
                                                        <i class="fa fa-user"></i>
                                                        <span><?= $milestone['assigned_firstname'] . ' ' . $milestone['assigned_lastname']; ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="milestone-progress">
                                            <div class="progress progress-sm">
                                                <div class="progress-bar progress-bar-<?= $milestone['progress_percentage'] >= 75 ? 'success' : ($milestone['progress_percentage'] >= 50 ? 'info' : 'warning'); ?>" 
                                                     style="width: <?= $milestone['progress_percentage']; ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fa fa-flag fa-3x text-muted"></i>
                                <h4 class="text-muted"><?= _l('no_milestones_yet'); ?></h4>
                                <p class="text-muted"><?= _l('milestones_will_appear_here'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Time Tracking -->
        <div class="row">
            <?php if (get_option('project_enhancement_client_time_visibility')): ?>
                <div class="col-md-6">
                    <div class="panel_s">
                        <div class="panel-header">
                            <h5 class="panel-title">
                                <?= _l('recent_time_entries'); ?>
                                <span class="badge"><?= count($recent_time_entries ?? []); ?></span>
                            </h5>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($recent_time_entries)): ?>
                                <div class="time-entries-list">
                                    <?php foreach ($recent_time_entries as $entry): ?>
                                        <div class="time-entry-item">
                                            <div class="entry-header">
                                                <span class="entry-date"><?= _d($entry['date']); ?></span>
                                                <span class="entry-duration"><?= format_duration($entry['duration']); ?></span>
                                            </div>
                                            <div class="entry-content">
                                                <div class="entry-description">
                                                    <?= $entry['description']; ?>
                                                </div>
                                                <div class="entry-meta">
                                                    <span class="entry-staff">
                                                        <i class="fa fa-user"></i> <?= $entry['staff_firstname'] . ' ' . $entry['staff_lastname']; ?>
                                                    </span>
                                                    <?php if ($entry['milestone_name']): ?>
                                                        <span class="entry-milestone">
                                                            <i class="fa fa-flag"></i> <?= $entry['milestone_name']; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="text-center mtop15">
                                    <a href="<?= site_url('clients/project_enhancement/time_tracking/' . $project->id); ?>" class="btn btn-default btn-sm">
                                        <?= _l('view_all_time_entries'); ?>
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="text-center">
                                    <i class="fa fa-clock fa-2x text-muted"></i>
                                    <p class="text-muted"><?= _l('no_time_entries_yet'); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="col-md-<?= get_option('project_enhancement_client_time_visibility') ? '6' : '12'; ?>">
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title">
                            <?= _l('project_activity'); ?>
                            <span class="badge"><?= count($project_activity ?? []); ?></span>
                        </h5>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($project_activity)): ?>
                            <div class="activity-timeline">
                                <?php foreach ($project_activity as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fa <?= $activity['icon']; ?> text-<?= $activity['color']; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-description">
                                                <?= $activity['description']; ?>
                                            </div>
                                            <div class="activity-meta">
                                                <small class="text-muted">
                                                    <?= time_ago($activity['created_at']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center">
                                <i class="fa fa-history fa-2x text-muted"></i>
                                <p class="text-muted"><?= _l('no_recent_activity'); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Information (if enabled) -->
        <?php if (get_option('project_enhancement_client_budget_visibility') && !empty($project_budget)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-header">
                            <h5 class="panel-title"><?= _l('budget_overview'); ?></h5>
                        </div>
                        <div class="panel-body">
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="budget-chart-container">
                                        <canvas id="budget-utilization-chart" height="200"></canvas>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="budget-summary">
                                        <div class="budget-item">
                                            <span class="budget-label"><?= _l('total_budget'); ?>:</span>
                                            <span class="budget-value">$<?= number_format($project_budget['total_amount'], 2); ?></span>
                                        </div>
                                        <div class="budget-item">
                                            <span class="budget-label"><?= _l('amount_spent'); ?>:</span>
                                            <span class="budget-value text-<?= $project_budget['spent_amount'] > $project_budget['total_amount'] ? 'danger' : 'success'; ?>">
                                                $<?= number_format($project_budget['spent_amount'], 2); ?>
                                            </span>
                                        </div>
                                        <div class="budget-item">
                                            <span class="budget-label"><?= _l('remaining_budget'); ?>:</span>
                                            <span class="budget-value text-<?= $project_budget['remaining_amount'] < 0 ? 'danger' : 'info'; ?>">
                                                $<?= number_format($project_budget['remaining_amount'], 2); ?>
                                            </span>
                                        </div>
                                        <div class="budget-progress">
                                            <div class="progress-info">
                                                <span><?= _l('budget_utilization'); ?></span>
                                                <span class="text-<?= $project_budget['utilization_percentage'] > 90 ? 'danger' : 'success'; ?>">
                                                    <?= number_format($project_budget['utilization_percentage'], 1); ?>%
                                                </span>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-<?= $project_budget['utilization_percentage'] > 90 ? 'danger' : ($project_budget['utilization_percentage'] > 70 ? 'warning' : 'success'); ?>" 
                                                     style="width: <?= min($project_budget['utilization_percentage'], 100); ?>%">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Project Team (if enabled) -->
        <?php if (get_option('project_enhancement_client_team_visibility') && !empty($project_team)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-header">
                            <h5 class="panel-title">
                                <?= _l('project_team'); ?>
                                <span class="badge"><?= count($project_team); ?></span>
                            </h5>
                        </div>
                        <div class="panel-body">
                            <div class="team-members">
                                <?php foreach ($project_team as $member): ?>
                                    <div class="team-member">
                                        <div class="member-avatar">
                                            <img src="<?= staff_profile_image_url($member['staffid']); ?>" 
                                                 alt="<?= $member['firstname'] . ' ' . $member['lastname']; ?>"
                                                 class="img-circle">
                                        </div>
                                        <div class="member-info">
                                            <h6><?= $member['firstname'] . ' ' . $member['lastname']; ?></h6>
                                            <p class="text-muted"><?= $member['role'] ?? _l('team_member'); ?></p>
                                            <?php if (!empty($member['skills'])): ?>
                                                <div class="member-skills">
                                                    <?php foreach (explode(',', $member['skills']) as $skill): ?>
                                                        <span class="skill-tag"><?= trim($skill); ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="member-stats">
                                            <div class="stat-item">
                                                <span class="stat-value"><?= number_format($member['total_hours'] ?? 0, 1); ?>h</span>
                                                <span class="stat-label"><?= _l('hours_logged'); ?></span>
                                            </div>
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
    
    // Initialize charts
    initializeCharts();
    
    // Initialize progress circles
    initializeProgressCircles();
    
    // Auto-refresh activity
    setInterval(function() {
        refreshProjectActivity();
    }, 300000); // 5 minutes
});

/**
 * Initialize all charts
 */
function initializeCharts() {
    // Project Timeline Chart
    var timelineCtx = document.getElementById('project-timeline-chart');
    if (timelineCtx) {
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode($timeline_data['labels'] ?? []); ?>,
                datasets: [{
                    label: '<?= _l('planned_progress'); ?>',
                    data: <?= json_encode($timeline_data['planned'] ?? []); ?>,
                    borderColor: '#007bff',
                    backgroundColor: 'rgba(0, 123, 255, 0.1)',
                    tension: 0.4
                }, {
                    label: '<?= _l('actual_progress'); ?>',
                    data: <?= json_encode($timeline_data['actual'] ?? []); ?>,
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
    
    // Budget Utilization Chart
    var budgetCtx = document.getElementById('budget-utilization-chart');
    if (budgetCtx) {
        new Chart(budgetCtx, {
            type: 'doughnut',
            data: {
                labels: ['<?= _l('spent'); ?>', '<?= _l('remaining'); ?>'],
                datasets: [{
                    data: [
                        <?= $project_budget['spent_amount'] ?? 0; ?>,
                        <?= max(0, ($project_budget['remaining_amount'] ?? 0)); ?>
                    ],
                    backgroundColor: [
                        '<?= ($project_budget['utilization_percentage'] ?? 0) > 90 ? "#dc3545" : "#28a745"; ?>',
                        '#e9ecef'
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
 * Initialize progress circles
 */
function initializeProgressCircles() {
    $('.progress-circle').each(function() {
        var progress = $(this).data('progress');
        var circle = $(this);
        
        // Create SVG circle
        var svg = '<svg class="progress-ring" width="60" height="60">' +
                  '<circle class="progress-ring-circle" stroke="#e9ecef" stroke-width="4" fill="transparent" r="26" cx="30" cy="30"/>' +
                  '<circle class="progress-ring-progress" stroke="' + getProgressColor(progress) + '" stroke-width="4" fill="transparent" r="26" cx="30" cy="30"/>' +
                  '</svg>';
        
        circle.prepend(svg);
        
        // Animate circle
        var circumference = 2 * Math.PI * 26;
        var offset = circumference - (progress / 100) * circumference;
        
        circle.find('.progress-ring-progress').css({
            'stroke-dasharray': circumference,
            'stroke-dashoffset': offset,
            'transition': 'stroke-dashoffset 0.5s ease-in-out'
        });
    });
}

/**
 * Get progress color based on percentage
 */
function getProgressColor(progress) {
    if (progress >= 75) return '#28a745';
    if (progress >= 50) return '#17a2b8';
    if (progress >= 25) return '#ffc107';
    return '#dc3545';
}

/**
 * Refresh project activity
 */
function refreshProjectActivity() {
    $.ajax({
        url: site_url + 'clients/project_enhancement/get_activity/<?= $project->id; ?>',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.activities.length > 0) {
                updateActivityDisplay(response.activities);
            }
        }
    });
}

/**
 * Update activity display
 */
function updateActivityDisplay(activities) {
    var $timeline = $('.activity-timeline');
    var currentCount = $timeline.find('.activity-item').length;
    
    if (activities.length > currentCount) {
        // Add new activities
        var newActivities = activities.slice(0, activities.length - currentCount);
        newActivities.forEach(function(activity) {
            var activityHtml = createActivityItem(activity);
            $timeline.prepend(activityHtml);
        });
        
        // Animate new items
        $timeline.find('.activity-item').slice(0, newActivities.length).hide().fadeIn();
    }
}

/**
 * Create activity item HTML
 */
function createActivityItem(activity) {
    return `
        <div class="activity-item">
            <div class="activity-icon">
                <i class="fa ${activity.icon} text-${activity.color}"></i>
            </div>
            <div class="activity-content">
                <div class="activity-description">
                    ${activity.description}
                </div>
                <div class="activity-meta">
                    <small class="text-muted">
                        ${activity.time_ago}
                    </small>
                </div>
            </div>
        </div>
    `;
}
</script>

<style>
.project-stat-icon {
    font-size: 2rem;
    margin-bottom: 10px;
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

.project-progress-container {
    margin-bottom: 30px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
}

.progress-label {
    font-weight: 600;
    color: #333;
}

.progress-percentage {
    font-weight: 600;
    color: #666;
}

.progress-lg {
    height: 20px;
}

.project-info .info-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    padding-bottom: 8px;
    border-bottom: 1px solid #f0f0f0;
}

.project-info .info-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.milestones-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.milestone-card {
    border: 1px solid #e4e7ea;
    border-radius: 8px;
    background: #fff;
    transition: all 0.3s ease;
    overflow: hidden;
}

.milestone-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.milestone-header {
    display: flex;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e4e7ea;
}

.milestone-status {
    margin-right: 15px;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
}

.status-not_started { background-color: #6c757d; }
.status-in_progress { background-color: #007bff; }
.status-completed { background-color: #28a745; }
.status-on_hold { background-color: #ffc107; }

.milestone-title {
    flex: 1;
}

.milestone-title h6 {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.milestone-progress-circle {
    position: relative;
    width: 60px;
    height: 60px;
}

.progress-circle {
    position: relative;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 600;
}

.progress-ring {
    position: absolute;
    top: 0;
    left: 0;
    transform: rotate(-90deg);
}

.milestone-content {
    padding: 20px;
}

.milestone-description {
    color: #666;
    margin-bottom: 15px;
    line-height: 1.4;
}

.milestone-meta {
    font-size: 12px;
    color: #999;
}

.meta-item {
    display: flex;
    align-items: center;
    margin-bottom: 5px;
}

.meta-item i {
    margin-right: 8px;
    width: 12px;
}

.milestone-progress {
    padding: 0 20px 20px;
}

.progress-sm {
    height: 6px;
}

.time-entries-list {
    max-height: 400px;
    overflow-y: auto;
}

.time-entry-item {
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.time-entry-item:last-child {
    border-bottom: none;
}

.entry-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.entry-date {
    font-weight: 600;
    color: #333;
}

.entry-duration {
    font-weight: 600;
    color: #007bff;
}

.entry-description {
    color: #666;
    margin-bottom: 8px;
}

.entry-meta {
    display: flex;
    gap: 15px;
    font-size: 11px;
    color: #999;
}

.activity-timeline {
    position: relative;
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    margin-bottom: 20px;
    position: relative;
}

.activity-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 19px;
    top: 40px;
    bottom: -20px;
    width: 2px;
    background: #e4e7ea;
}

.activity-icon {
    flex-shrink: 0;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #fff;
    border: 2px solid #e4e7ea;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
    position: relative;
    z-index: 1;
}

.activity-content {
    flex: 1;
    padding-top: 5px;
}

.activity-description {
    font-weight: 500;
    margin-bottom: 5px;
}

.budget-summary .budget-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 14px;
}

.budget-label {
    color: #666;
}

.budget-value {
    font-weight: 600;
}

.budget-progress {
    margin-top: 20px;
}

.progress-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 12px;
}

.team-members {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.team-member {
    display: flex;
    align-items: center;
    padding: 15px;
    border: 1px solid #e4e7ea;
    border-radius: 8px;
    background: #fff;
}

.member-avatar {
    margin-right: 15px;
}

.member-avatar img {
    width: 50px;
    height: 50px;
    object-fit: cover;
}

.member-info {
    flex: 1;
}

.member-info h6 {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.member-skills {
    margin-top: 8px;
}

.skill-tag {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    margin: 2px 3px 2px 0;
}

.member-stats {
    text-align: center;
}

.stat-item {
    margin-bottom: 5px;
}

.stat-value {
    display: block;
    font-weight: 600;
    color: #333;
}

.stat-label {
    font-size: 11px;
    color: #666;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .milestones-grid {
        grid-template-columns: 1fr;
    }
    
    .team-members {
        grid-template-columns: 1fr;
    }
    
    .milestone-header {
        flex-direction: column;
        text-align: center;
    }
    
    .milestone-status {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .milestone-progress-circle {
        margin-top: 10px;
    }
}

/* Chart containers */
canvas {
    max-height: 300px !important;
}

.budget-chart-container canvas {
    max-height: 200px !important;
}
</style>