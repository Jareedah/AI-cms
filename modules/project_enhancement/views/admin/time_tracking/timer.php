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
                                    <i class="fa fa-clock"></i> 
                                    <?= _l('time_tracker'); ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <a href="<?= admin_url('project_enhancement/time_tracking'); ?>" class="btn btn-default">
                                        <i class="fa fa-list"></i> <?= _l('time_entries'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/time_tracking/reports'); ?>" class="btn btn-default">
                                        <i class="fa fa-chart-bar"></i> <?= _l('reports'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Timer Section -->
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Timer Display -->
                        <div class="timer-container">
                            <div class="timer-display">
                                <div class="timer-time" id="timer-display">
                                    00:00:00
                                </div>
                                <div class="timer-status" id="timer-status">
                                    <?= _l('timer_stopped'); ?>
                                </div>
                            </div>

                            <!-- Timer Controls -->
                            <div class="timer-controls">
                                <button type="button" id="start-timer" class="btn btn-success btn-lg timer-btn">
                                    <i class="fa fa-play"></i> <?= _l('start'); ?>
                                </button>
                                <button type="button" id="pause-timer" class="btn btn-warning btn-lg timer-btn" style="display: none;">
                                    <i class="fa fa-pause"></i> <?= _l('pause'); ?>
                                </button>
                                <button type="button" id="stop-timer" class="btn btn-danger btn-lg timer-btn" style="display: none;">
                                    <i class="fa fa-stop"></i> <?= _l('stop'); ?>
                                </button>
                                <button type="button" id="reset-timer" class="btn btn-default btn-lg timer-btn">
                                    <i class="fa fa-refresh"></i> <?= _l('reset'); ?>
                                </button>
                            </div>
                        </div>

                        <!-- Timer Configuration -->
                        <div class="timer-config">
                            <?= form_open('', ['id' => 'timer-form']); ?>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <?= render_select('project_id', $projects, ['id', 'name'], 'project', '', ['required' => true, 'data-width' => '100%', 'id' => 'timer-project']); ?>
                                </div>
                                <div class="col-md-6">
                                    <?= render_select('milestone_id', [], ['id', 'name'], 'milestone', '', ['data-width' => '100%', 'id' => 'timer-milestone', 'data-none-selected-text' => _l('no_milestone')]); ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <?= render_select('category_id', $time_categories, ['id', 'name'], 'category', '', ['data-width' => '100%', 'id' => 'timer-category']); ?>
                                </div>
                                <div class="col-md-6">
                                    <?= render_input('hourly_rate', 'hourly_rate', '', 'number', ['step' => '0.01', 'min' => '0', 'id' => 'timer-hourly-rate']); ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <?= render_textarea('description', 'description', '', ['rows' => 3, 'placeholder' => _l('time_entry_description_placeholder'), 'id' => 'timer-description']); ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <input type="checkbox" id="timer-billable" name="billable" value="1" checked>
                                        <label for="timer-billable"><?= _l('billable'); ?></label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="checkbox">
                                        <input type="checkbox" id="timer-auto-submit" name="auto_submit" value="1">
                                        <label for="timer-auto-submit"><?= _l('auto_submit_when_stopped'); ?></label>
                                    </div>
                                </div>
                            </div>

                            <?= form_close(); ?>
                        </div>

                        <!-- Quick Actions -->
                        <div class="timer-quick-actions">
                            <h6><?= _l('quick_actions'); ?></h6>
                            <div class="quick-action-buttons">
                                <button type="button" class="btn btn-default btn-sm" onclick="setQuickTime(15)">
                                    +15m
                                </button>
                                <button type="button" class="btn btn-default btn-sm" onclick="setQuickTime(30)">
                                    +30m
                                </button>
                                <button type="button" class="btn btn-default btn-sm" onclick="setQuickTime(60)">
                                    +1h
                                </button>
                                <button type="button" class="btn btn-default btn-sm" onclick="setQuickTime(120)">
                                    +2h
                                </button>
                                <button type="button" class="btn btn-info btn-sm" onclick="showManualTimeModal()">
                                    <i class="fa fa-edit"></i> <?= _l('manual_time'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Current Session -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('current_session'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <div class="session-info">
                            <div class="session-item">
                                <strong><?= _l('total_time'); ?>:</strong>
                                <span id="session-total-time">00:00:00</span>
                            </div>
                            <div class="session-item">
                                <strong><?= _l('estimated_cost'); ?>:</strong>
                                <span id="session-cost">$0.00</span>
                            </div>
                            <div class="session-item">
                                <strong><?= _l('started_at'); ?>:</strong>
                                <span id="session-start-time">-</span>
                            </div>
                            <div class="session-item">
                                <strong><?= _l('breaks_taken'); ?>:</strong>
                                <span id="session-breaks">0</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Today's Summary -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('todays_summary'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <div class="today-stats">
                            <div class="stat-item">
                                <div class="stat-value"><?= $today_stats['total_hours'] ?? '0.0'; ?>h</div>
                                <div class="stat-label"><?= _l('total_hours'); ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $today_stats['entries_count'] ?? 0; ?></div>
                                <div class="stat-label"><?= _l('entries'); ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value"><?= $today_stats['projects_count'] ?? 0; ?></div>
                                <div class="stat-label"><?= _l('projects'); ?></div>
                            </div>
                            <div class="stat-item">
                                <div class="stat-value">$<?= number_format($today_stats['total_earnings'] ?? 0, 2); ?></div>
                                <div class="stat-label"><?= _l('earnings'); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Entries -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('recent_entries'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <?php if (!empty($recent_entries)): ?>
                            <div class="recent-entries">
                                <?php foreach ($recent_entries as $entry): ?>
                                    <div class="recent-entry">
                                        <div class="entry-info">
                                            <strong><?= $entry['project_name']; ?></strong>
                                            <?php if ($entry['milestone_name']): ?>
                                                <br><small class="text-muted"><?= $entry['milestone_name']; ?></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="entry-time">
                                            <?= format_duration($entry['duration']); ?>
                                        </div>
                                        <div class="entry-actions">
                                            <button type="button" class="btn btn-xs btn-default" onclick="continueEntry(<?= $entry['id']; ?>)" 
                                                    data-toggle="tooltip" title="<?= _l('continue_this_entry'); ?>">
                                                <i class="fa fa-play"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center"><?= _l('no_recent_entries'); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Timer Settings -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('timer_settings'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <div class="timer-settings">
                            <div class="setting-item">
                                <label>
                                    <input type="checkbox" id="setting-sound-notifications" checked>
                                    <?= _l('sound_notifications'); ?>
                                </label>
                            </div>
                            <div class="setting-item">
                                <label>
                                    <input type="checkbox" id="setting-break-reminders" checked>
                                    <?= _l('break_reminders'); ?>
                                </label>
                            </div>
                            <div class="setting-item">
                                <label>
                                    <input type="checkbox" id="setting-idle-detection" checked>
                                    <?= _l('idle_detection'); ?>
                                </label>
                            </div>
                            <div class="setting-item">
                                <label for="setting-break-interval"><?= _l('break_reminder_interval'); ?>:</label>
                                <select id="setting-break-interval" class="form-control">
                                    <option value="30">30 <?= _l('minutes'); ?></option>
                                    <option value="60" selected>1 <?= _l('hour'); ?></option>
                                    <option value="90">1.5 <?= _l('hours'); ?></option>
                                    <option value="120">2 <?= _l('hours'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Manual Time Entry Modal -->
<div class="modal fade" id="manual-time-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= _l('manual_time_entry'); ?></h4>
            </div>
            <div class="modal-body">
                <?= form_open('', ['id' => 'manual-time-form']); ?>
                <div class="row">
                    <div class="col-md-6">
                        <?= render_input('manual_hours', 'hours', '', 'number', ['step' => '0.25', 'min' => '0', 'required' => true]); ?>
                    </div>
                    <div class="col-md-6">
                        <?= render_input('manual_minutes', 'minutes', '', 'number', ['min' => '0', 'max' => '59']); ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <?= render_textarea('manual_description', 'description', '', ['rows' => 3, 'required' => true]); ?>
                    </div>
                </div>
                <?= form_close(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('cancel'); ?></button>
                <button type="button" class="btn btn-info" onclick="addManualTime()"><?= _l('add_time'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
    'use strict';
    
    // Initialize timer
    initializeTimer();
    
    // Initialize project/milestone dependency
    initializeProjectMilestone();
    
    // Load timer settings
    loadTimerSettings();
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Check for active timer on page load
    checkActiveTimer();
});

// Timer variables
var timerInterval;
var timerSeconds = 0;
var timerRunning = false;
var timerPaused = false;
var sessionStartTime = null;
var breakCount = 0;
var lastActivityTime = Date.now();

/**
 * Initialize timer functionality
 */
function initializeTimer() {
    $('#start-timer').on('click', startTimer);
    $('#pause-timer').on('click', pauseTimer);
    $('#stop-timer').on('click', stopTimer);
    $('#reset-timer').on('click', resetTimer);
    
    // Update cost when hourly rate changes
    $('#timer-hourly-rate').on('input', updateSessionCost);
    
    // Idle detection
    if (localStorage.getItem('timer_idle_detection') !== 'false') {
        setInterval(checkIdleTime, 60000); // Check every minute
        $(document).on('mousemove keypress click', function() {
            lastActivityTime = Date.now();
        });
    }
    
    // Break reminders
    if (localStorage.getItem('timer_break_reminders') !== 'false') {
        var breakInterval = parseInt(localStorage.getItem('timer_break_interval') || 60) * 60000;
        setInterval(checkBreakReminder, breakInterval);
    }
}

/**
 * Start timer
 */
function startTimer() {
    if (!validateTimerForm()) {
        return;
    }
    
    if (!timerRunning) {
        // Starting fresh timer
        sessionStartTime = new Date();
        updateSessionInfo();
        
        // Save timer state to server
        saveTimerState('start');
    }
    
    timerRunning = true;
    timerPaused = false;
    
    timerInterval = setInterval(function() {
        timerSeconds++;
        updateTimerDisplay();
        updateSessionCost();
    }, 1000);
    
    updateTimerControls();
    updateTimerStatus('<?= _l('timer_running'); ?>');
    
    // Play sound notification
    if (localStorage.getItem('timer_sound_notifications') !== 'false') {
        playNotificationSound('start');
    }
}

/**
 * Pause timer
 */
function pauseTimer() {
    clearInterval(timerInterval);
    timerPaused = true;
    timerRunning = false;
    
    updateTimerControls();
    updateTimerStatus('<?= _l('timer_paused'); ?>');
    
    // Save timer state
    saveTimerState('pause');
    
    // Play sound notification
    if (localStorage.getItem('timer_sound_notifications') !== 'false') {
        playNotificationSound('pause');
    }
}

/**
 * Stop timer
 */
function stopTimer() {
    if (timerSeconds === 0) {
        alert('<?= _l('no_time_to_save'); ?>');
        return;
    }
    
    clearInterval(timerInterval);
    
    // Check auto-submit setting
    if ($('#timer-auto-submit').is(':checked')) {
        submitTimeEntry();
    } else {
        if (confirm('<?= _l('save_time_entry_confirm'); ?>')) {
            submitTimeEntry();
        }
    }
    
    resetTimer();
    
    // Play sound notification
    if (localStorage.getItem('timer_sound_notifications') !== 'false') {
        playNotificationSound('stop');
    }
}

/**
 * Reset timer
 */
function resetTimer() {
    clearInterval(timerInterval);
    timerSeconds = 0;
    timerRunning = false;
    timerPaused = false;
    sessionStartTime = null;
    breakCount = 0;
    
    updateTimerDisplay();
    updateTimerControls();
    updateTimerStatus('<?= _l('timer_stopped'); ?>');
    updateSessionInfo();
    
    // Clear timer state from server
    saveTimerState('reset');
}

/**
 * Update timer display
 */
function updateTimerDisplay() {
    var hours = Math.floor(timerSeconds / 3600);
    var minutes = Math.floor((timerSeconds % 3600) / 60);
    var seconds = timerSeconds % 60;
    
    var display = pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
    $('#timer-display').text(display);
    $('#session-total-time').text(display);
    
    // Update page title
    if (timerRunning) {
        document.title = display + ' - <?= _l('time_tracker'); ?>';
    } else {
        document.title = '<?= _l('time_tracker'); ?>';
    }
}

/**
 * Update timer controls
 */
function updateTimerControls() {
    if (timerRunning) {
        $('#start-timer').hide();
        $('#pause-timer').show();
        $('#stop-timer').show();
    } else if (timerPaused) {
        $('#start-timer').show().find('i').removeClass('fa-play').addClass('fa-play');
        $('#start-timer').find('span').text('<?= _l('resume'); ?>');
        $('#pause-timer').hide();
        $('#stop-timer').show();
    } else {
        $('#start-timer').show().find('i').removeClass('fa-play').addClass('fa-play');
        $('#start-timer').find('span').text('<?= _l('start'); ?>');
        $('#pause-timer').hide();
        $('#stop-timer').hide();
    }
}

/**
 * Update timer status
 */
function updateTimerStatus(status) {
    $('#timer-status').text(status);
}

/**
 * Update session info
 */
function updateSessionInfo() {
    if (sessionStartTime) {
        $('#session-start-time').text(formatTime(sessionStartTime));
    } else {
        $('#session-start-time').text('-');
    }
    
    $('#session-breaks').text(breakCount);
    updateSessionCost();
}

/**
 * Update session cost
 */
function updateSessionCost() {
    var hourlyRate = parseFloat($('#timer-hourly-rate').val()) || 0;
    var hours = timerSeconds / 3600;
    var cost = hourlyRate * hours;
    
    $('#session-cost').text('$' + cost.toFixed(2));
}

/**
 * Validate timer form
 */
function validateTimerForm() {
    var projectId = $('#timer-project').val();
    
    if (!projectId) {
        alert('<?= _l('project_required'); ?>');
        $('#timer-project').focus();
        return false;
    }
    
    return true;
}

/**
 * Submit time entry
 */
function submitTimeEntry() {
    var formData = {
        project_id: $('#timer-project').val(),
        milestone_id: $('#timer-milestone').val(),
        category_id: $('#timer-category').val(),
        duration: timerSeconds,
        description: $('#timer-description').val(),
        billable: $('#timer-billable').is(':checked') ? 1 : 0,
        hourly_rate: $('#timer-hourly-rate').val(),
        date: formatDate(new Date())
    };
    
    $.ajax({
        url: admin_url + 'project_enhancement/time_tracking/submit_timer_entry',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                alert('<?= _l('time_entry_saved_successfully'); ?>');
                
                // Refresh recent entries
                refreshRecentEntries();
                
                // Refresh today's stats
                refreshTodayStats();
                
                // Clear description
                $('#timer-description').val('');
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
 * Initialize project/milestone dependency
 */
function initializeProjectMilestone() {
    $('#timer-project').on('change', function() {
        var projectId = $(this).val();
        loadProjectMilestones(projectId);
        loadProjectHourlyRate(projectId);
    });
}

/**
 * Load project milestones
 */
function loadProjectMilestones(projectId) {
    if (!projectId) {
        $('#timer-milestone').empty().selectpicker('refresh');
        return;
    }
    
    $.ajax({
        url: admin_url + 'project_enhancement/milestones/get_by_project/' + projectId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            var $select = $('#timer-milestone');
            $select.empty();
            $select.append('<option value=""><?= _l('no_milestone'); ?></option>');
            
            if (response.success && response.milestones) {
                response.milestones.forEach(function(milestone) {
                    $select.append('<option value="' + milestone.id + '">' + milestone.name + '</option>');
                });
            }
            
            $select.selectpicker('refresh');
        }
    });
}

/**
 * Load project hourly rate
 */
function loadProjectHourlyRate(projectId) {
    $.ajax({
        url: admin_url + 'projects/get_hourly_rate/' + projectId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.hourly_rate) {
                $('#timer-hourly-rate').val(response.hourly_rate);
                updateSessionCost();
            }
        }
    });
}

/**
 * Set quick time
 */
function setQuickTime(minutes) {
    timerSeconds += minutes * 60;
    updateTimerDisplay();
    updateSessionCost();
}

/**
 * Show manual time modal
 */
function showManualTimeModal() {
    $('#manual-time-modal').modal('show');
}

/**
 * Add manual time
 */
function addManualTime() {
    var hours = parseFloat($('#manual_hours').val()) || 0;
    var minutes = parseFloat($('#manual_minutes').val()) || 0;
    var description = $('#manual_description').val();
    
    if (hours === 0 && minutes === 0) {
        alert('<?= _l('time_required'); ?>');
        return;
    }
    
    if (!description.trim()) {
        alert('<?= _l('description_required'); ?>');
        return;
    }
    
    var totalSeconds = (hours * 3600) + (minutes * 60);
    timerSeconds += totalSeconds;
    
    updateTimerDisplay();
    updateSessionCost();
    
    $('#manual-time-modal').modal('hide');
    $('#manual-time-form')[0].reset();
}

/**
 * Continue previous entry
 */
function continueEntry(entryId) {
    $.ajax({
        url: admin_url + 'project_enhancement/time_tracking/get_entry/' + entryId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                var entry = response.entry;
                
                // Fill form with entry data
                $('#timer-project').val(entry.project_id).selectpicker('refresh');
                $('#timer-milestone').val(entry.milestone_id).selectpicker('refresh');
                $('#timer-category').val(entry.category_id).selectpicker('refresh');
                $('#timer-description').val(entry.description);
                $('#timer-hourly-rate').val(entry.hourly_rate);
                $('#timer-billable').prop('checked', entry.billable == 1);
                
                // Load milestones for the project
                loadProjectMilestones(entry.project_id);
            }
        }
    });
}

/**
 * Load timer settings
 */
function loadTimerSettings() {
    $('#setting-sound-notifications').prop('checked', localStorage.getItem('timer_sound_notifications') !== 'false');
    $('#setting-break-reminders').prop('checked', localStorage.getItem('timer_break_reminders') !== 'false');
    $('#setting-idle-detection').prop('checked', localStorage.getItem('timer_idle_detection') !== 'false');
    $('#setting-break-interval').val(localStorage.getItem('timer_break_interval') || 60);
    
    // Save settings on change
    $('.timer-settings input, .timer-settings select').on('change', function() {
        var setting = $(this).attr('id').replace('setting-', '').replace('-', '_');
        var value = $(this).is(':checkbox') ? $(this).is(':checked') : $(this).val();
        localStorage.setItem('timer_' + setting, value);
    });
}

/**
 * Check for active timer
 */
function checkActiveTimer() {
    $.ajax({
        url: admin_url + 'project_enhancement/time_tracking/get_active_timer',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success && response.timer) {
                // Restore timer state
                var timer = response.timer;
                timerSeconds = timer.elapsed_seconds;
                sessionStartTime = new Date(timer.start_time);
                
                // Fill form
                $('#timer-project').val(timer.project_id).selectpicker('refresh');
                $('#timer-description').val(timer.description);
                
                updateTimerDisplay();
                updateSessionInfo();
                
                if (timer.status === 'running') {
                    startTimer();
                } else if (timer.status === 'paused') {
                    timerPaused = true;
                    updateTimerControls();
                    updateTimerStatus('<?= _l('timer_paused'); ?>');
                }
            }
        }
    });
}

/**
 * Save timer state to server
 */
function saveTimerState(action) {
    $.ajax({
        url: admin_url + 'project_enhancement/time_tracking/save_timer_state',
        type: 'POST',
        data: {
            action: action,
            project_id: $('#timer-project').val(),
            milestone_id: $('#timer-milestone').val(),
            category_id: $('#timer-category').val(),
            description: $('#timer-description').val(),
            elapsed_seconds: timerSeconds,
            start_time: sessionStartTime ? sessionStartTime.toISOString() : null
        }
    });
}

/**
 * Check idle time
 */
function checkIdleTime() {
    if (timerRunning && (Date.now() - lastActivityTime) > 300000) { // 5 minutes
        if (confirm('<?= _l('idle_time_detected'); ?>')) {
            pauseTimer();
        }
    }
}

/**
 * Check break reminder
 */
function checkBreakReminder() {
    if (timerRunning && sessionStartTime) {
        var elapsed = (Date.now() - sessionStartTime.getTime()) / 60000; // minutes
        var breakInterval = parseInt(localStorage.getItem('timer_break_interval') || 60);
        
        if (elapsed >= breakInterval && elapsed % breakInterval < 1) {
            if (confirm('<?= _l('break_reminder_message'); ?>')) {
                breakCount++;
                updateSessionInfo();
                pauseTimer();
            }
        }
    }
}

/**
 * Play notification sound
 */
function playNotificationSound(type) {
    // Simple beep sound implementation
    var context = new (window.AudioContext || window.webkitAudioContext)();
    var oscillator = context.createOscillator();
    var gainNode = context.createGain();
    
    oscillator.connect(gainNode);
    gainNode.connect(context.destination);
    
    var frequency = type === 'start' ? 800 : type === 'stop' ? 400 : 600;
    oscillator.frequency.value = frequency;
    oscillator.type = 'sine';
    
    gainNode.gain.setValueAtTime(0.3, context.currentTime);
    gainNode.gain.exponentialRampToValueAtTime(0.01, context.currentTime + 0.1);
    
    oscillator.start(context.currentTime);
    oscillator.stop(context.currentTime + 0.1);
}

/**
 * Refresh recent entries
 */
function refreshRecentEntries() {
    $.ajax({
        url: admin_url + 'project_enhancement/time_tracking/get_recent_entries',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update recent entries display
                location.reload(); // Simple refresh for now
            }
        }
    });
}

/**
 * Refresh today's stats
 */
function refreshTodayStats() {
    $.ajax({
        url: admin_url + 'project_enhancement/time_tracking/get_today_stats',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Update today's stats display
                location.reload(); // Simple refresh for now
            }
        }
    });
}

/**
 * Helper functions
 */
function pad(num) {
    return (num < 10 ? '0' : '') + num;
}

function formatTime(date) {
    return date.toLocaleTimeString();
}

function formatDate(date) {
    return date.toISOString().split('T')[0];
}
</script>

<style>
.timer-container {
    text-align: center;
    margin-bottom: 30px;
}

.timer-display {
    margin-bottom: 30px;
}

.timer-time {
    font-size: 4rem;
    font-weight: 300;
    color: #333;
    font-family: 'Courier New', monospace;
    margin-bottom: 10px;
}

.timer-status {
    font-size: 1.2rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.timer-controls {
    margin-bottom: 30px;
}

.timer-btn {
    margin: 0 5px;
    min-width: 120px;
}

.timer-config {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.timer-quick-actions {
    text-align: center;
}

.quick-action-buttons {
    margin-top: 10px;
}

.quick-action-buttons .btn {
    margin: 0 5px 5px 0;
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

.session-info {
    font-size: 14px;
}

.session-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.session-item:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.today-stats {
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
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 11px;
    color: #666;
    text-transform: uppercase;
}

.recent-entries {
    max-height: 300px;
    overflow-y: auto;
}

.recent-entry {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.recent-entry:last-child {
    border-bottom: none;
}

.entry-info {
    flex: 1;
}

.entry-time {
    font-weight: 600;
    color: #333;
    margin-right: 10px;
}

.timer-settings {
    font-size: 14px;
}

.setting-item {
    margin-bottom: 15px;
}

.setting-item label {
    font-weight: normal;
    margin-bottom: 5px;
}

@media (max-width: 768px) {
    .timer-time {
        font-size: 2.5rem;
    }
    
    .timer-btn {
        min-width: 100px;
        margin-bottom: 10px;
    }
    
    .today-stats {
        grid-template-columns: 1fr;
    }
    
    .recent-entry {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .entry-time {
        margin-right: 0;
        margin-top: 5px;
    }
}

/* Timer animation */
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.timer-time.running {
    animation: pulse 2s infinite;
}

/* Button states */
.timer-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.timer-btn.btn-success:hover {
    background-color: #28a745;
    border-color: #28a745;
}

.timer-btn.btn-warning:hover {
    background-color: #ffc107;
    border-color: #ffc107;
}

.timer-btn.btn-danger:hover {
    background-color: #dc3545;
    border-color: #dc3545;
}
</style>