<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4 class="no-margin">
                                    <i class="fa fa-project-diagram"></i> 
                                    <?= _l('project_enhancement_dashboard'); ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <a href="<?= admin_url('project_enhancement/milestones/create'); ?>" class="btn btn-info">
                                        <i class="fa fa-plus"></i> <?= _l('new_milestone'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/time_tracking/timer'); ?>" class="btn btn-success">
                                        <i class="fa fa-play"></i> <?= _l('start_timer'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/reports'); ?>" class="btn btn-primary">
                                        <i class="fa fa-chart-bar"></i> <?= _l('reports'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="panel_s">
                    <div class="panel-body padding-10">
                        <div class="widget-drilldown">
                            <div class="widget-drilldown-item">
                                <div class="row">
                                    <div class="col-md-8">
                                        <span class="text-dark"><?= _l('active_projects'); ?></span>
                                        <h3 class="text-info no-margin font-medium">
                                            <?= $stats['active_projects'] ?? 0; ?>
                                        </h3>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <i class="fa fa-project-diagram fa-2x text-info"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="panel_s">
                    <div class="panel-body padding-10">
                        <div class="widget-drilldown">
                            <div class="widget-drilldown-item">
                                <div class="row">
                                    <div class="col-md-8">
                                        <span class="text-dark"><?= _l('pending_milestones'); ?></span>
                                        <h3 class="text-warning no-margin font-medium">
                                            <?= $stats['pending_milestones'] ?? 0; ?>
                                        </h3>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <i class="fa fa-flag fa-2x text-warning"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="panel_s">
                    <div class="panel-body padding-10">
                        <div class="widget-drilldown">
                            <div class="widget-drilldown-item">
                                <div class="row">
                                    <div class="col-md-8">
                                        <span class="text-dark"><?= _l('hours_this_month'); ?></span>
                                        <h3 class="text-success no-margin font-medium">
                                            <?= number_format($stats['hours_this_month'] ?? 0, 1); ?>h
                                        </h3>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <i class="fa fa-clock fa-2x text-success"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6">
                <div class="panel_s">
                    <div class="panel-body padding-10">
                        <div class="widget-drilldown">
                            <div class="widget-drilldown-item">
                                <div class="row">
                                    <div class="col-md-8">
                                        <span class="text-dark"><?= _l('budget_utilization'); ?></span>
                                        <h3 class="text-danger no-margin font-medium">
                                            <?= number_format($stats['budget_utilization'] ?? 0, 1); ?>%
                                        </h3>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <i class="fa fa-dollar-sign fa-2x text-danger"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row">
            <!-- Project Progress Chart -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-header">
                        <h4 class="panel-title"><?= _l('project_progress_overview'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <canvas id="projectProgressChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Time Tracking Chart -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-header">
                        <h4 class="panel-title"><?= _l('time_tracking_trends'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <canvas id="timeTrackingChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget and Resource Charts -->
        <div class="row">
            <!-- Budget Overview -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-header">
                        <h4 class="panel-title"><?= _l('budget_overview'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <canvas id="budgetChart" height="300"></canvas>
                    </div>
                </div>
            </div>

            <!-- Resource Utilization -->
            <div class="col-md-6">
                <div class="panel_s">
                    <div class="panel-header">
                        <h4 class="panel-title"><?= _l('resource_utilization'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <canvas id="resourceChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity and Alerts -->
        <div class="row">
            <!-- Recent Activity -->
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-header">
                        <h4 class="panel-title"><?= _l('recent_activity'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <div class="activity-feed">
                            <?php if (!empty($recent_activities)): ?>
                                <?php foreach ($recent_activities as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fa <?= $activity['icon']; ?> text-<?= $activity['color']; ?>"></i>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-text">
                                                <?= $activity['description']; ?>
                                            </div>
                                            <div class="activity-time text-muted">
                                                <small><?= time_ago($activity['created_at']); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-muted"><?= _l('no_recent_activity'); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerts and Notifications -->
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-header">
                        <h4 class="panel-title"><?= _l('alerts_notifications'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($alerts)): ?>
                            <?php foreach ($alerts as $alert): ?>
                                <div class="alert alert-<?= $alert['severity'] === 'critical' ? 'danger' : ($alert['severity'] === 'warning' ? 'warning' : 'info'); ?> alert-dismissible">
                                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                                    <h5><i class="fa fa-<?= $alert['icon']; ?>"></i> <?= $alert['title']; ?></h5>
                                    <?= $alert['message']; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="alert alert-success">
                                <i class="fa fa-check-circle"></i> <?= _l('no_alerts_all_good'); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h4 class="panel-title"><?= _l('quick_actions'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <div class="list-group">
                            <a href="<?= admin_url('project_enhancement/milestones/create'); ?>" class="list-group-item">
                                <i class="fa fa-plus text-info"></i> <?= _l('create_milestone'); ?>
                            </a>
                            <a href="<?= admin_url('project_enhancement/time_tracking/create'); ?>" class="list-group-item">
                                <i class="fa fa-clock text-success"></i> <?= _l('log_time_entry'); ?>
                            </a>
                            <a href="<?= admin_url('project_enhancement/resources/create'); ?>" class="list-group-item">
                                <i class="fa fa-users text-warning"></i> <?= _l('allocate_resource'); ?>
                            </a>
                            <a href="<?= admin_url('project_enhancement/budget/create'); ?>" class="list-group-item">
                                <i class="fa fa-dollar-sign text-danger"></i> <?= _l('create_budget'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-header">
                        <h4 class="panel-title"><?= _l('upcoming_deadlines'); ?></h4>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($upcoming_deadlines)): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th><?= _l('project'); ?></th>
                                            <th><?= _l('milestone'); ?></th>
                                            <th><?= _l('due_date'); ?></th>
                                            <th><?= _l('progress'); ?></th>
                                            <th><?= _l('status'); ?></th>
                                            <th><?= _l('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($upcoming_deadlines as $deadline): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= admin_url('projects/view/' . $deadline['project_id']); ?>">
                                                        <?= $deadline['project_name']; ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="<?= admin_url('project_enhancement/milestones/view/' . $deadline['id']); ?>">
                                                        <?= $deadline['name']; ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <span class="text-<?= days_between(date('Y-m-d'), $deadline['due_date']) <= 3 ? 'danger' : 'muted'; ?>">
                                                        <?= _d($deadline['due_date']); ?>
                                                        <small>(<?= time_ago($deadline['due_date']); ?>)</small>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar progress-bar-<?= $deadline['progress_percentage'] >= 75 ? 'success' : ($deadline['progress_percentage'] >= 50 ? 'warning' : 'danger'); ?>" 
                                                             style="width: <?= $deadline['progress_percentage']; ?>%">
                                                        </div>
                                                    </div>
                                                    <small><?= $deadline['progress_percentage']; ?>%</small>
                                                </td>
                                                <td>
                                                    <span class="label label-<?= milestone_status_color($deadline['status']); ?>">
                                                        <?= _l('milestone_status_' . $deadline['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <a href="<?= admin_url('project_enhancement/milestones/view/' . $deadline['id']); ?>" 
                                                           class="btn btn-default btn-xs">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="<?= admin_url('project_enhancement/milestones/edit/' . $deadline['id']); ?>" 
                                                           class="btn btn-default btn-xs">
                                                            <i class="fa fa-edit"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center"><?= _l('no_upcoming_deadlines'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart Data -->
<script>
    var chartData = <?= json_encode($chart_data ?? []); ?>;
</script>

<!-- Dashboard JavaScript -->
<script src="<?= module_dir_url('project_enhancement', 'assets/js/dashboard.js'); ?>"></script>

<?php init_tail(); ?>

<style>
.activity-feed {
    max-height: 400px;
    overflow-y: auto;
}

.activity-item {
    display: flex;
    margin-bottom: 15px;
    padding-bottom: 15px;
    border-bottom: 1px solid #eee;
}

.activity-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.activity-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 15px;
}

.activity-content {
    flex: 1;
}

.activity-text {
    font-weight: 500;
    margin-bottom: 5px;
}

.activity-time {
    font-size: 12px;
}

.widget-drilldown-item {
    padding: 15px;
}

.progress-sm {
    height: 10px;
}

.quick-actions .list-group-item {
    border: none;
    padding: 10px 15px;
}

.quick-actions .list-group-item:hover {
    background-color: #f8f9fa;
}

.panel-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e4e7ea;
    background: #f8f9fa;
}

.panel-title {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}
</style>