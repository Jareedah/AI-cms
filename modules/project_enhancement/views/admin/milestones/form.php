<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h4 class="no-margin">
                                    <i class="fa fa-flag"></i> 
                                    <?= isset($milestone) ? _l('edit_milestone') : _l('new_milestone'); ?>
                                </h4>
                            </div>
                            <div class="col-md-4 text-right">
                                <a href="<?= admin_url('project_enhancement/milestones'); ?>" class="btn btn-default">
                                    <i class="fa fa-arrow-left"></i> <?= _l('back_to_milestones'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open(admin_url('project_enhancement/milestones/' . (isset($milestone) ? 'edit/' . $milestone->id : 'create')), ['id' => 'milestone-form']); ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Information -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <?= render_input('name', 'milestone_name', isset($milestone) ? $milestone->name : '', 'text', ['required' => true]); ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <?= render_select('project_id', $projects, ['id', 'name'], 'project', isset($milestone) ? $milestone->project_id : '', ['required' => true, 'data-width' => '100%']); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?= render_select('assigned_to', $staff_members, ['staffid', ['firstname', 'lastname']], 'assigned_to', isset($milestone) ? $milestone->assigned_to : '', ['data-width' => '100%', 'data-none-selected-text' => _l('unassigned')]); ?>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <?= render_textarea('description', 'description', isset($milestone) ? $milestone->description : '', ['rows' => 4]); ?>
                                    </div>
                                </div>

                                <!-- Dates and Progress -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <?= render_date_input('start_date', 'start_date', isset($milestone) ? $milestone->start_date : '', ['required' => true]); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?= render_date_input('due_date', 'due_date', isset($milestone) ? $milestone->due_date : ''); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?= render_input('estimated_hours', 'estimated_hours', isset($milestone) ? $milestone->estimated_hours : '', 'number', ['step' => '0.25', 'min' => '0']); ?>
                                    </div>
                                </div>

                                <!-- Status and Priority -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <?= render_select('status', [
                                            ['id' => 'not_started', 'name' => _l('milestone_status_not_started')],
                                            ['id' => 'in_progress', 'name' => _l('milestone_status_in_progress')],
                                            ['id' => 'completed', 'name' => _l('milestone_status_completed')],
                                            ['id' => 'on_hold', 'name' => _l('milestone_status_on_hold')],
                                            ['id' => 'cancelled', 'name' => _l('milestone_status_cancelled')]
                                        ], ['id', 'name'], 'status', isset($milestone) ? $milestone->status : 'not_started', ['data-width' => '100%']); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?= render_select('priority', [
                                            ['id' => 'low', 'name' => _l('priority_low')],
                                            ['id' => 'medium', 'name' => _l('priority_medium')],
                                            ['id' => 'high', 'name' => _l('priority_high')],
                                            ['id' => 'critical', 'name' => _l('priority_critical')]
                                        ], ['id', 'name'], 'priority', isset($milestone) ? $milestone->priority : 'medium', ['data-width' => '100%']); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?= render_input('progress_percentage', 'progress_percentage', isset($milestone) ? $milestone->progress_percentage : '0', 'number', ['min' => '0', 'max' => '100', 'step' => '0.01']); ?>
                                    </div>
                                </div>

                                <?php if (isset($milestone)): ?>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <?= render_input('actual_hours', 'actual_hours', $milestone->actual_hours ?? '0', 'number', ['step' => '0.25', 'min' => '0', 'readonly' => true]); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?= render_input('order_number', 'order_number', $milestone->order_number ?? '0', 'number', ['min' => '0']); ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-4">
                                <!-- Dependencies -->
                                <?php if (isset($milestone)): ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h5 class="panel-title"><?= _l('dependencies'); ?></h5>
                                        </div>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <label><?= _l('depends_on_milestones'); ?></label>
                                                <?= render_select('dependencies[]', $available_milestones, ['id', 'name'], '', $current_dependencies ?? [], ['multiple' => true, 'data-width' => '100%']); ?>
                                                <small class="text-muted"><?= _l('dependencies_help'); ?></small>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Progress Information -->
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h5 class="panel-title"><?= _l('progress_information'); ?></h5>
                                    </div>
                                    <div class="panel-body">
                                        <?php if (isset($milestone)): ?>
                                            <div class="progress-display">
                                                <div class="progress progress-lg">
                                                    <div class="progress-bar progress-bar-<?= ($milestone->progress_percentage ?? 0) >= 75 ? 'success' : (($milestone->progress_percentage ?? 0) >= 50 ? 'info' : 'warning'); ?>" 
                                                         style="width: <?= $milestone->progress_percentage ?? 0; ?>%">
                                                    </div>
                                                </div>
                                                <p class="text-center"><?= $milestone->progress_percentage ?? 0; ?>% <?= _l('complete'); ?></p>
                                            </div>

                                            <?php if ($milestone->estimated_hours && $milestone->actual_hours): ?>
                                                <div class="hours-comparison">
                                                    <p><strong><?= _l('estimated_hours'); ?>:</strong> <?= $milestone->estimated_hours; ?>h</p>
                                                    <p><strong><?= _l('actual_hours'); ?>:</strong> <?= $milestone->actual_hours; ?>h</p>
                                                    <?php 
                                                    $variance = (($milestone->actual_hours - $milestone->estimated_hours) / $milestone->estimated_hours) * 100;
                                                    ?>
                                                    <p><strong><?= _l('variance'); ?>:</strong> 
                                                        <span class="text-<?= $variance > 0 ? 'danger' : 'success'; ?>">
                                                            <?= $variance > 0 ? '+' : ''; ?><?= number_format($variance, 1); ?>%
                                                        </span>
                                                    </p>
                                                </div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <p class="text-muted"><?= _l('progress_info_after_creation'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Quick Actions -->
                                <?php if (isset($milestone)): ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h5 class="panel-title"><?= _l('quick_actions'); ?></h5>
                                        </div>
                                        <div class="panel-body">
                                            <div class="btn-group-vertical btn-block">
                                                <a href="<?= admin_url('project_enhancement/milestones/view/' . $milestone->id); ?>" class="btn btn-default">
                                                    <i class="fa fa-eye"></i> <?= _l('view_milestone'); ?>
                                                </a>
                                                <a href="<?= admin_url('project_enhancement/time_tracking?milestone_id=' . $milestone->id); ?>" class="btn btn-default">
                                                    <i class="fa fa-clock"></i> <?= _l('time_entries'); ?>
                                                </a>
                                                <button type="button" class="btn btn-info" onclick="updateProgress()">
                                                    <i class="fa fa-tasks"></i> <?= _l('update_progress'); ?>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <div class="btn-bottom-toolbar text-right">
                                    <button type="button" class="btn btn-default" onclick="window.history.back();">
                                        <?= _l('cancel'); ?>
                                    </button>
                                    <button type="submit" class="btn btn-info">
                                        <i class="fa fa-save"></i> <?= _l('save'); ?>
                                    </button>
                                    <?php if (isset($milestone)): ?>
                                        <button type="button" class="btn btn-success" onclick="saveAndContinue()">
                                            <i class="fa fa-save"></i> <?= _l('save_and_continue'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Progress Update Modal -->
<?php if (isset($milestone)): ?>
<div class="modal fade" id="progress-update-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?= _l('update_progress'); ?></h4>
            </div>
            <div class="modal-body">
                <?= form_open('', ['id' => 'progress-update-form']); ?>
                <div class="form-group">
                    <label><?= _l('progress_percentage'); ?></label>
                    <div class="input-group">
                        <input type="number" class="form-control" name="progress_percentage" 
                               min="0" max="100" step="0.01" value="<?= $milestone->progress_percentage; ?>" required>
                        <div class="input-group-addon">%</div>
                    </div>
                </div>
                <div class="form-group">
                    <label><?= _l('progress_notes'); ?></label>
                    <textarea class="form-control" name="progress_notes" rows="3" 
                              placeholder="<?= _l('progress_notes_placeholder'); ?>"></textarea>
                </div>
                <?= form_close(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('cancel'); ?></button>
                <button type="button" class="btn btn-info" onclick="submitProgressUpdate()"><?= _l('update'); ?></button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
    'use strict';
    
    // Initialize form validation
    $('#milestone-form').validate({
        rules: {
            name: {
                required: true,
                minlength: 3
            },
            project_id: {
                required: true
            },
            start_date: {
                required: true,
                date: true
            },
            due_date: {
                date: true
            },
            progress_percentage: {
                min: 0,
                max: 100
            },
            estimated_hours: {
                min: 0
            }
        },
        messages: {
            name: {
                required: "<?= _l('milestone_name_required'); ?>",
                minlength: "<?= _l('milestone_name_min_length'); ?>"
            },
            project_id: {
                required: "<?= _l('project_required'); ?>"
            },
            start_date: {
                required: "<?= _l('start_date_required'); ?>"
            }
        }
    });
    
    // Date validation
    $('#due_date').on('change', function() {
        var startDate = $('#start_date').val();
        var dueDate = $(this).val();
        
        if (startDate && dueDate && new Date(dueDate) < new Date(startDate)) {
            alert('<?= _l('due_date_after_start_date'); ?>');
            $(this).val('');
        }
    });
    
    // Progress percentage slider
    $('#progress_percentage').on('input', function() {
        var value = $(this).val();
        $('.progress-bar').css('width', value + '%');
        $('.progress-bar').next('p').text(value + '% <?= _l('complete'); ?>');
        
        // Update progress bar color
        var colorClass = value >= 75 ? 'progress-bar-success' : (value >= 50 ? 'progress-bar-info' : 'progress-bar-warning');
        $('.progress-bar').removeClass('progress-bar-success progress-bar-info progress-bar-warning').addClass(colorClass);
    });
    
    // Auto-complete milestone name based on project
    $('#project_id').on('change', function() {
        var projectId = $(this).val();
        if (projectId && !$('#name').val()) {
            // Suggest milestone name based on project
            var projectName = $(this).find('option:selected').text();
            var suggestions = [
                'Planning Phase - ' + projectName,
                'Development Phase - ' + projectName,
                'Testing Phase - ' + projectName,
                'Deployment Phase - ' + projectName
            ];
            
            // Show suggestions (you could implement a dropdown here)
            console.log('Suggested milestone names:', suggestions);
        }
    });
});

/**
 * Update progress modal
 */
function updateProgress() {
    $('#progress-update-modal').modal('show');
}

/**
 * Submit progress update
 */
function submitProgressUpdate() {
    var formData = $('#progress-update-form').serialize();
    
    $.ajax({
        url: admin_url + 'project_enhancement/milestones/update_progress_ajax/<?= isset($milestone) ? $milestone->id : ''; ?>',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $('#progress-update-modal').modal('hide');
                
                // Update the progress display
                var percentage = response.progress_percentage;
                $('#progress_percentage').val(percentage);
                $('.progress-bar').css('width', percentage + '%');
                
                // Update color
                var colorClass = percentage >= 75 ? 'progress-bar-success' : (percentage >= 50 ? 'progress-bar-info' : 'progress-bar-warning');
                $('.progress-bar').removeClass('progress-bar-success progress-bar-info progress-bar-warning').addClass(colorClass);
                
                alert_float('success', '<?= _l('progress_updated_successfully'); ?>');
            } else {
                alert_float('danger', response.message || '<?= _l('operation_failed'); ?>');
            }
        },
        error: function() {
            alert_float('danger', '<?= _l('operation_failed'); ?>');
        }
    });
}

/**
 * Save and continue editing
 */
function saveAndContinue() {
    $('#milestone-form').append('<input type="hidden" name="save_and_continue" value="1">');
    $('#milestone-form').submit();
}
</script>

<style>
.progress-lg {
    height: 20px;
}

.panel-title {
    font-size: 14px;
    font-weight: 600;
}

.btn-group-vertical .btn {
    margin-bottom: 5px;
}

.hours-comparison p {
    margin-bottom: 8px;
}

.progress-display {
    margin-bottom: 20px;
}

.btn-bottom-toolbar {
    padding: 15px 0;
    border-top: 1px solid #e4e7ea;
    margin-top: 20px;
}
</style>