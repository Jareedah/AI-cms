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
                                    <i class="fa fa-flag"></i> 
                                    <?= $milestone->name; ?>
                                    <span class="label label-<?= milestone_status_color($milestone->status); ?> mleft10">
                                        <?= _l('milestone_status_' . $milestone->status); ?>
                                    </span>
                                </h4>
                                <p class="text-muted mtop5">
                                    <?= _l('project'); ?>: 
                                    <a href="<?= admin_url('projects/view/' . $milestone->project_id); ?>">
                                        <?= $milestone->project_name; ?>
                                    </a>
                                </p>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group">
                                    <?php if (staff_can('edit', 'project_enhancement')): ?>
                                        <a href="<?= admin_url('project_enhancement/milestones/edit/' . $milestone->id); ?>" class="btn btn-info">
                                            <i class="fa fa-edit"></i> <?= _l('edit'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                            <i class="fa fa-cog"></i> <?= _l('actions'); ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="<?= admin_url('project_enhancement/milestones/update_progress/' . $milestone->id); ?>">
                                                <i class="fa fa-tasks"></i> <?= _l('update_progress'); ?>
                                            </a></li>
                                            <li><a href="<?= admin_url('project_enhancement/milestones/dependencies/' . $milestone->id); ?>">
                                                <i class="fa fa-sitemap"></i> <?= _l('manage_dependencies'); ?>
                                            </a></li>
                                            <?php if ($milestone->status !== 'completed'): ?>
                                                <li><a href="<?= admin_url('project_enhancement/milestones/mark_complete/' . $milestone->id); ?>">
                                                    <i class="fa fa-check"></i> <?= _l('mark_complete'); ?>
                                                </a></li>
                                            <?php endif; ?>
                                            <li class="divider"></li>
                                            <li><a href="<?= admin_url('project_enhancement/milestones/duplicate/' . $milestone->id); ?>">
                                                <i class="fa fa-copy"></i> <?= _l('duplicate'); ?>
                                            </a></li>
                                            <li><a href="<?= admin_url('project_enhancement/milestones/export/' . $milestone->id); ?>">
                                                <i class="fa fa-download"></i> <?= _l('export'); ?>
                                            </a></li>
                                            <?php if (staff_can('delete', 'project_enhancement')): ?>
                                                <li class="divider"></li>
                                                <li><a href="<?= admin_url('project_enhancement/milestones/delete/' . $milestone->id); ?>" 
                                                       class="text-danger _delete">
                                                    <i class="fa fa-trash"></i> <?= _l('delete'); ?>
                                                </a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                    <a href="<?= admin_url('project_enhancement/milestones'); ?>" class="btn btn-default">
                                        <i class="fa fa-arrow-left"></i> <?= _l('back_to_milestones'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-md-8">
                <!-- Overview -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('overview'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <!-- Progress Bar -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="progress-container">
                                    <div class="progress-header">
                                        <span class="progress-label"><?= _l('progress'); ?></span>
                                        <span class="progress-percentage"><?= $milestone->progress_percentage; ?>%</span>
                                    </div>
                                    <div class="progress progress-lg">
                                        <div class="progress-bar progress-bar-<?= $milestone->progress_percentage >= 75 ? 'success' : ($milestone->progress_percentage >= 50 ? 'warning' : 'danger'); ?>" 
                                             style="width: <?= $milestone->progress_percentage; ?>%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Key Information -->
                        <div class="row mtop20">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="bold"><?= _l('priority'); ?>:</td>
                                        <td>
                                            <span class="label label-<?= priority_color($milestone->priority); ?>">
                                                <?= _l('priority_' . $milestone->priority); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?= _l('assigned_to'); ?>:</td>
                                        <td>
                                            <?php if ($milestone->assigned_to): ?>
                                                <a href="<?= admin_url('staff/profile/' . $milestone->assigned_to); ?>">
                                                    <?= $milestone->assigned_firstname . ' ' . $milestone->assigned_lastname; ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted"><?= _l('unassigned'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?= _l('start_date'); ?>:</td>
                                        <td><?= _d($milestone->start_date); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?= _l('due_date'); ?>:</td>
                                        <td>
                                            <?php if ($milestone->due_date): ?>
                                                <span class="text-<?= strtotime($milestone->due_date) < time() && $milestone->status !== 'completed' ? 'danger' : 'muted'; ?>">
                                                    <?= _d($milestone->due_date); ?>
                                                    <?php if (strtotime($milestone->due_date) < time() && $milestone->status !== 'completed'): ?>
                                                        <small class="text-danger">(<?= _l('overdue'); ?>)</small>
                                                    <?php endif; ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted"><?= _l('no_due_date'); ?></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="bold"><?= _l('estimated_hours'); ?>:</td>
                                        <td>
                                            <?= $milestone->estimated_hours ? $milestone->estimated_hours . ' ' . _l('hours') : _l('not_specified'); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?= _l('actual_hours'); ?>:</td>
                                        <td>
                                            <?= number_format($milestone->actual_hours ?? 0, 2); ?> <?= _l('hours'); ?>
                                            <?php if ($milestone->estimated_hours && $milestone->actual_hours): ?>
                                                <?php $variance = (($milestone->actual_hours - $milestone->estimated_hours) / $milestone->estimated_hours) * 100; ?>
                                                <small class="text-<?= $variance > 0 ? 'danger' : 'success'; ?>">
                                                    (<?= $variance > 0 ? '+' : ''; ?><?= number_format($variance, 1); ?>%)
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?= _l('created_date'); ?>:</td>
                                        <td><?= _dt($milestone->created_at); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="bold"><?= _l('last_updated'); ?>:</td>
                                        <td><?= _dt($milestone->updated_at); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Description -->
                        <?php if (!empty($milestone->description)): ?>
                            <div class="row mtop20">
                                <div class="col-md-12">
                                    <h6><?= _l('description'); ?></h6>
                                    <div class="milestone-description">
                                        <?= nl2br($milestone->description); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dependencies -->
                <?php if (!empty($dependencies) || !empty($dependents)): ?>
                    <div class="panel_s">
                        <div class="panel-header">
                            <h5 class="panel-title"><?= _l('dependencies'); ?></h5>
                        </div>
                        <div class="panel-body">
                            <?php if (!empty($dependencies)): ?>
                                <h6><?= _l('depends_on'); ?></h6>
                                <div class="dependency-list">
                                    <?php foreach ($dependencies as $dependency): ?>
                                        <div class="dependency-item">
                                            <div class="dependency-info">
                                                <a href="<?= admin_url('project_enhancement/milestones/view/' . $dependency['depends_on_milestone_id']); ?>">
                                                    <?= $dependency['dependency_name']; ?>
                                                </a>
                                                <small class="text-muted">(<?= $dependency['dependency_project_name']; ?>)</small>
                                                <span class="label label-<?= milestone_status_color($dependency['dependency_status']); ?> mleft5">
                                                    <?= _l('milestone_status_' . $dependency['dependency_status']); ?>
                                                </span>
                                            </div>
                                            <div class="dependency-progress">
                                                <div class="progress progress-xs">
                                                    <div class="progress-bar progress-bar-<?= $dependency['dependency_progress'] >= 75 ? 'success' : ($dependency['dependency_progress'] >= 50 ? 'warning' : 'danger'); ?>" 
                                                         style="width: <?= $dependency['dependency_progress']; ?>%">
                                                    </div>
                                                </div>
                                                <small><?= $dependency['dependency_progress']; ?>%</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($dependents)): ?>
                                <h6 class="mtop20"><?= _l('blocks_milestones'); ?></h6>
                                <div class="dependency-list">
                                    <?php foreach ($dependents as $dependent): ?>
                                        <div class="dependency-item">
                                            <div class="dependency-info">
                                                <a href="<?= admin_url('project_enhancement/milestones/view/' . $dependent['milestone_id']); ?>">
                                                    <?= $dependent['milestone_name']; ?>
                                                </a>
                                                <small class="text-muted">(<?= $dependent['project_name']; ?>)</small>
                                                <span class="label label-<?= milestone_status_color($dependent['status']); ?> mleft5">
                                                    <?= _l('milestone_status_' . $dependent['status']); ?>
                                                </span>
                                            </div>
                                            <div class="dependency-progress">
                                                <div class="progress progress-xs">
                                                    <div class="progress-bar progress-bar-<?= $dependent['progress_percentage'] >= 75 ? 'success' : ($dependent['progress_percentage'] >= 50 ? 'warning' : 'danger'); ?>" 
                                                         style="width: <?= $dependent['progress_percentage']; ?>%">
                                                    </div>
                                                </div>
                                                <small><?= $dependent['progress_percentage']; ?>%</small>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Time Entries -->
                <?php if (!empty($time_entries)): ?>
                    <div class="panel_s">
                        <div class="panel-header">
                            <h5 class="panel-title">
                                <?= _l('time_entries'); ?>
                                <span class="badge"><?= count($time_entries); ?></span>
                            </h5>
                        </div>
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= _l('staff_member'); ?></th>
                                            <th><?= _l('date'); ?></th>
                                            <th><?= _l('duration'); ?></th>
                                            <th><?= _l('description'); ?></th>
                                            <th><?= _l('status'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($time_entries as $entry): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= admin_url('staff/profile/' . $entry['staff_id']); ?>">
                                                        <?= $entry['staff_firstname'] . ' ' . $entry['staff_lastname']; ?>
                                                    </a>
                                                </td>
                                                <td><?= _d($entry['date']); ?></td>
                                                <td><?= format_duration($entry['duration']); ?></td>
                                                <td><?= character_limiter($entry['description'], 50); ?></td>
                                                <td>
                                                    <span class="label label-<?= time_entry_status_color($entry['status']); ?>">
                                                        <?= _l('time_entry_status_' . $entry['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-right">
                                <a href="<?= admin_url('project_enhancement/time_tracking?milestone_id=' . $milestone->id); ?>" class="btn btn-default btn-sm">
                                    <?= _l('view_all_time_entries'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Activity Log -->
                <?php if (!empty($activity_log)): ?>
                    <div class="panel_s">
                        <div class="panel-header">
                            <h5 class="panel-title"><?= _l('activity_log'); ?></h5>
                        </div>
                        <div class="panel-body">
                            <div class="activity-timeline">
                                <?php foreach ($activity_log as $activity): ?>
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
                                                    <?= $activity['staff_name']; ?> • <?= time_ago($activity['created_at']); ?>
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Quick Actions -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('quick_actions'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <div class="list-group">
                            <a href="<?= admin_url('project_enhancement/time_tracking/create?milestone_id=' . $milestone->id); ?>" class="list-group-item">
                                <i class="fa fa-clock text-success"></i> <?= _l('log_time'); ?>
                            </a>
                            <a href="<?= admin_url('project_enhancement/milestones/update_progress/' . $milestone->id); ?>" class="list-group-item">
                                <i class="fa fa-tasks text-info"></i> <?= _l('update_progress'); ?>
                            </a>
                            <?php if ($milestone->status !== 'completed'): ?>
                                <a href="<?= admin_url('project_enhancement/milestones/mark_complete/' . $milestone->id); ?>" class="list-group-item">
                                    <i class="fa fa-check text-success"></i> <?= _l('mark_complete'); ?>
                                </a>
                            <?php endif; ?>
                            <a href="<?= admin_url('project_enhancement/milestones/dependencies/' . $milestone->id); ?>" class="list-group-item">
                                <i class="fa fa-sitemap text-warning"></i> <?= _l('manage_dependencies'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Statistics -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('statistics'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <div class="milestone-stats">
                            <div class="stat-item">
                                <div class="stat-value"><?= count($time_entries ?? []); ?></div>
                                <div class="stat-label"><?= _l('time_entries'); ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= count($dependencies ?? []); ?></div>
                                <div class="stat-label"><?= _l('dependencies'); ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= count($dependents ?? []); ?></div>
                                <div class="stat-label"><?= _l('dependents'); ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= number_format($milestone->actual_hours ?? 0, 1); ?>h</div>
                                <div class="stat-label"><?= _l('total_hours'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Project Information -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('project_information'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <p><strong><?= _l('project_name'); ?>:</strong><br>
                           <a href="<?= admin_url('projects/view/' . $milestone->project_id); ?>">
                               <?= $milestone->project_name; ?>
                           </a>
                        </p>
                        <p><strong><?= _l('project_status'); ?>:</strong><br>
                           <span class="label label-<?= project_status_color($project_info->status ?? 'in_progress'); ?>">
                               <?= _l('project_status_' . ($project_info->status ?? 'in_progress')); ?>
                           </span>
                        </p>
                        <?php if (isset($project_info->deadline)): ?>
                            <p><strong><?= _l('project_deadline'); ?>:</strong><br>
                               <?= $project_info->deadline ? _d($project_info->deadline) : _l('no_deadline'); ?>
                            </p>
                        <?php endif; ?>
                        <a href="<?= admin_url('projects/view/' . $milestone->project_id); ?>" class="btn btn-default btn-block">
                            <i class="fa fa-eye"></i> <?= _l('view_project'); ?>
                        </a>
                    </div>
                </div>

                <!-- Related Milestones -->
                <?php if (!empty($related_milestones)): ?>
                    <div class="panel_s">
                        <div class="panel-header">
                            <h5 class="panel-title"><?= _l('related_milestones'); ?></h5>
                        </div>
                        <div class="panel-body">
                            <div class="related-milestones">
                                <?php foreach ($related_milestones as $related): ?>
                                    <div class="related-milestone">
                                        <a href="<?= admin_url('project_enhancement/milestones/view/' . $related['id']); ?>">
                                            <?= $related['name']; ?>
                                        </a>
                                        <div class="progress progress-xs mtop5">
                                            <div class="progress-bar progress-bar-<?= $related['progress_percentage'] >= 75 ? 'success' : ($related['progress_percentage'] >= 50 ? 'warning' : 'danger'); ?>" 
                                                 style="width: <?= $related['progress_percentage']; ?>%">
                                            </div>
                                        </div>
                                        <small class="text-muted"><?= $related['progress_percentage']; ?>% • <?= _l('milestone_status_' . $related['status']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
    'use strict';
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Progress update functionality
    initProgressUpdate();
    
    // Activity auto-refresh
    setInterval(function() {
        refreshActivityLog();
    }, 300000); // 5 minutes
});

/**
 * Initialize progress update functionality
 */
function initProgressUpdate() {
    // Quick progress buttons
    $('.quick-progress-btn').on('click', function() {
        var percentage = $(this).data('percentage');
        updateProgress(percentage);
    });
}

/**
 * Update milestone progress
 */
function updateProgress(percentage) {
    $.ajax({
        url: admin_url + 'project_enhancement/milestones/update_progress_ajax',
        type: 'POST',
        data: {
            milestone_id: <?= $milestone->id; ?>,
            progress_percentage: percentage
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
 * Refresh activity log
 */
function refreshActivityLog() {
    $.ajax({
        url: admin_url + 'project_enhancement/milestones/get_activity_log/' + <?= $milestone->id; ?>,
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
                        ${activity.staff_name} • ${activity.time_ago}
                    </small>
                </div>
            </div>
        </div>
    `;
}
</script>

<style>
.progress-container {
    margin-bottom: 20px;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
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

.table-borderless td {
    border: none;
    padding: 8px 15px 8px 0;
}

.milestone-description {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 4px;
    border-left: 4px solid #007bff;
}

.dependency-list {
    margin-bottom: 20px;
}

.dependency-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    border: 1px solid #e4e7ea;
    border-radius: 4px;
    margin-bottom: 10px;
    background: #f8f9fa;
}

.dependency-info {
    flex: 1;
}

.dependency-progress {
    width: 120px;
    text-align: right;
}

.progress-xs {
    height: 6px;
    margin-bottom: 5px;
}

.activity-timeline {
    position: relative;
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

.activity-meta {
    font-size: 12px;
}

.milestone-stats {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.stat-item {
    text-align: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 4px;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.related-milestones {
    max-height: 300px;
    overflow-y: auto;
}

.related-milestone {
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.related-milestone:last-child {
    border-bottom: none;
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

.list-group-item {
    border: none;
    padding: 12px 15px;
}

.list-group-item:hover {
    background-color: #f8f9fa;
}

@media (max-width: 768px) {
    .milestone-stats {
        grid-template-columns: 1fr;
    }
    
    .dependency-item {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .dependency-progress {
        width: 100%;
        margin-top: 10px;
    }
}
</style>