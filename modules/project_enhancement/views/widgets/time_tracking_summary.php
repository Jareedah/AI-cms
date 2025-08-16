<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Load project enhancement models
$CI = &get_instance();
$CI->load->model('project_enhancement/time_tracking_model');

// Get time tracking data for current week
$today = date('Y-m-d');
$week_start = date('Y-m-d', strtotime('monday this week'));
$week_end = date('Y-m-d', strtotime('sunday this week'));

$today_hours = $CI->time_tracking_model->get_total_hours_by_date($today);
$week_hours = $CI->time_tracking_model->get_total_hours_by_period($week_start, $week_end);
$pending_entries = $CI->time_tracking_model->get_pending_approvals_count();
$billable_hours_today = $CI->time_tracking_model->get_billable_hours_by_date($today);

// Get active timer if any
$active_timer = $CI->time_tracking_model->get_active_timer(get_staff_user_id());
?>

<div class="widget widget-time-tracking" id="widget-<?= create_widget_id(); ?>" data-name="<?= _l('time_tracking_widget'); ?>">
    <div class="widget-dragger"></div>
    
    <div class="panel_s">
        <div class="panel-body">
            <div class="widget-header">
                <h4 class="no-margin">
                    <i class="fa fa-clock text-success"></i>
                    <?= _l('time_tracking'); ?>
                </h4>
                <div class="widget-actions">
                    <a href="<?= admin_url('project_enhancement/time_tracking/timer'); ?>" class="btn btn-success btn-xs">
                        <i class="fa fa-play"></i> <?= _l('timer'); ?>
                    </a>
                </div>
            </div>
            
            <div class="widget-content">
                <!-- Active Timer -->
                <?php if ($active_timer): ?>
                    <div class="active-timer">
                        <div class="timer-info">
                            <h6><?= _l('active_timer'); ?></h6>
                            <div class="timer-display" id="active-timer-display">
                                <?php
                                $elapsed = time() - strtotime($active_timer['start_time']);
                                $hours = floor($elapsed / 3600);
                                $minutes = floor(($elapsed % 3600) / 60);
                                $seconds = $elapsed % 60;
                                ?>
                                <span class="timer-time"><?= sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds); ?></span>
                            </div>
                            <p class="timer-project">
                                <small><?= $active_timer['project_name']; ?></small>
                            </p>
                        </div>
                        <div class="timer-actions">
                            <button type="button" class="btn btn-danger btn-xs" onclick="stopTimer()">
                                <i class="fa fa-stop"></i>
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="no-active-timer">
                        <p class="text-muted text-center"><?= _l('no_active_timer'); ?></p>
                        <a href="<?= admin_url('project_enhancement/time_tracking/timer'); ?>" class="btn btn-success btn-sm btn-block">
                            <i class="fa fa-play"></i> <?= _l('start_timer'); ?>
                        </a>
                    </div>
                <?php endif; ?>
                
                <!-- Time Statistics -->
                <div class="time-stats">
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="stat-item">
                                <div class="stat-number text-info"><?= number_format($today_hours, 1); ?>h</div>
                                <div class="stat-label"><?= _l('today'); ?></div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="stat-item">
                                <div class="stat-number text-primary"><?= number_format($week_hours, 1); ?>h</div>
                                <div class="stat-label"><?= _l('this_week'); ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xs-6">
                            <div class="stat-item">
                                <div class="stat-number text-success"><?= number_format($billable_hours_today, 1); ?>h</div>
                                <div class="stat-label"><?= _l('billable_today'); ?></div>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="stat-item">
                                <div class="stat-number text-warning"><?= $pending_entries; ?></div>
                                <div class="stat-label"><?= _l('pending_approval'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Time Entries -->
                <div class="recent-entries">
                    <h6><?= _l('recent_entries'); ?></h6>
                    <?php
                    $recent_entries = $CI->time_tracking_model->get_recent_entries(3, get_staff_user_id());
                    if (!empty($recent_entries)): ?>
                        <ul class="entry-list">
                            <?php foreach ($recent_entries as $entry): ?>
                                <li class="entry-item">
                                    <div class="entry-info">
                                        <div class="entry-description">
                                            <?= character_limiter($entry['description'] ?: _l('no_description'), 30); ?>
                                        </div>
                                        <small class="text-muted">
                                            <?= $entry['project_name']; ?> • <?= _d($entry['date']); ?>
                                        </small>
                                    </div>
                                    <div class="entry-duration">
                                        <span class="duration-badge">
                                            <?= seconds_to_time_format($entry['duration']); ?>
                                        </span>
                                        <?php if ($entry['billable']): ?>
                                            <i class="fa fa-dollar-sign text-success" data-toggle="tooltip" title="<?= _l('billable'); ?>"></i>
                                        <?php endif; ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted text-center"><?= _l('no_time_entries_found'); ?></p>
                    <?php endif; ?>
                </div>
                
                <!-- Quick Actions -->
                <div class="widget-actions-footer">
                    <div class="btn-group btn-group-justified">
                        <a href="<?= admin_url('project_enhancement/time_tracking/create'); ?>" class="btn btn-default btn-xs">
                            <i class="fa fa-plus"></i> <?= _l('add_entry'); ?>
                        </a>
                        <a href="<?= admin_url('project_enhancement/time_tracking'); ?>" class="btn btn-default btn-xs">
                            <i class="fa fa-list"></i> <?= _l('view_all'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
<?php if ($active_timer): ?>
// Update active timer display
setInterval(function() {
    var timerElement = document.getElementById('active-timer-display');
    if (timerElement) {
        var startTime = new Date('<?= $active_timer['start_time']; ?>');
        var now = new Date();
        var elapsed = Math.floor((now - startTime) / 1000);
        
        var hours = Math.floor(elapsed / 3600);
        var minutes = Math.floor((elapsed % 3600) / 60);
        var seconds = elapsed % 60;
        
        var timeString = String(hours).padStart(2, '0') + ':' + 
                        String(minutes).padStart(2, '0') + ':' + 
                        String(seconds).padStart(2, '0');
        
        timerElement.querySelector('.timer-time').textContent = timeString;
    }
}, 1000);

// Stop timer function
function stopTimer() {
    if (confirm('<?= _l('confirm_stop_timer'); ?>')) {
        $.ajax({
            url: admin_url + 'project_enhancement/time_tracking/stop_timer_ajax',
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert_float('danger', response.message || '<?= _l('operation_failed'); ?>');
                }
            },
            error: function() {
                alert_float('danger', '<?= _l('operation_failed'); ?>');
            }
        });
    }
}
<?php endif; ?>

// Initialize tooltips
$(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<style>
.widget-time-tracking {
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

.active-timer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #e8f5e8;
    border-radius: 6px;
    margin-bottom: 20px;
    border: 1px solid #d4edda;
}

.timer-info h6 {
    margin: 0 0 5px 0;
    font-size: 12px;
    font-weight: 600;
    color: #28a745;
    text-transform: uppercase;
}

.timer-display {
    margin-bottom: 5px;
}

.timer-time {
    font-family: 'Courier New', monospace;
    font-size: 18px;
    font-weight: 600;
    color: #28a745;
}

.timer-project {
    margin: 0;
}

.timer-project small {
    font-size: 11px;
    color: #6c757d;
}

.no-active-timer {
    padding: 20px;
    text-align: center;
    background: #f8f9fa;
    border-radius: 6px;
    margin-bottom: 20px;
}

.time-stats {
    margin-bottom: 20px;
}

.stat-item {
    text-align: center;
    padding: 10px 5px;
}

.stat-number {
    font-size: 16px;
    font-weight: 600;
    line-height: 1;
}

.stat-label {
    font-size: 10px;
    color: #6c757d;
    margin-top: 5px;
    text-transform: uppercase;
}

.recent-entries h6 {
    margin: 0 0 10px 0;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
}

.entry-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.entry-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f1f2f3;
}

.entry-item:last-child {
    border-bottom: none;
}

.entry-info {
    flex: 1;
}

.entry-description {
    font-size: 12px;
    font-weight: 500;
    color: #333;
    line-height: 1.2;
    margin-bottom: 2px;
}

.entry-info small {
    font-size: 10px;
    line-height: 1;
}

.entry-duration {
    display: flex;
    align-items: center;
    gap: 5px;
}

.duration-badge {
    background: #e9ecef;
    color: #495057;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 10px;
    font-weight: 500;
    font-family: 'Courier New', monospace;
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