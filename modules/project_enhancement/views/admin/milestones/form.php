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
                                    <i class="fa fa-flag"></i> 
                                    <?= isset($milestone) ? _l('edit_milestone') : _l('new_milestone'); ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
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
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open(admin_url('project_enhancement/milestones/' . (isset($milestone) ? 'edit/' . $milestone->id : 'create')), ['id' => 'milestone-form']); ?>
                        
                        <!-- Basic Information -->
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="form-section-title"><?= _l('basic_information'); ?></h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= render_input('name', 'milestone_name', isset($milestone) ? $milestone->name : '', 'text', ['required' => true]); ?>
                            </div>
                            <div class="col-md-6">
                                <?= render_select('project_id', $projects, ['id', 'name'], 'project', isset($milestone) ? $milestone->project_id : ($selected_project_id ?? ''), ['required' => true, 'data-width' => '100%']); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <?= render_textarea('description', 'description', isset($milestone) ? $milestone->description : '', ['rows' => 4, 'placeholder' => _l('milestone_description_placeholder')]); ?>
                            </div>
                        </div>

                        <!-- Dates and Priority -->
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="form-section-title"><?= _l('scheduling_priority'); ?></h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <?= render_date_input('start_date', 'start_date', isset($milestone) ? _d($milestone->start_date) : '', ['required' => true]); ?>
                            </div>
                            <div class="col-md-4">
                                <?= render_date_input('due_date', 'due_date', isset($milestone) ? _d($milestone->due_date) : ''); ?>
                            </div>
                            <div class="col-md-4">
                                <?= render_select('priority', [
                                    ['id' => 'low', 'name' => _l('priority_low')],
                                    ['id' => 'medium', 'name' => _l('priority_medium')],
                                    ['id' => 'high', 'name' => _l('priority_high')],
                                    ['id' => 'critical', 'name' => _l('priority_critical')]
                                ], ['id', 'name'], 'priority', isset($milestone) ? $milestone->priority : 'medium', ['data-width' => '100%']); ?>
                            </div>
                        </div>

                        <!-- Assignment and Status -->
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="form-section-title"><?= _l('assignment_status'); ?></h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <?= render_select('assigned_to', $staff_members, ['staffid', ['firstname', 'lastname']], 'assigned_to', isset($milestone) ? $milestone->assigned_to : '', ['data-width' => '100%', 'data-none-selected-text' => _l('unassigned')]); ?>
                            </div>
                            <div class="col-md-6">
                                <?= render_select('status', [
                                    ['id' => 'not_started', 'name' => _l('milestone_status_not_started')],
                                    ['id' => 'in_progress', 'name' => _l('milestone_status_in_progress')],
                                    ['id' => 'completed', 'name' => _l('milestone_status_completed')],
                                    ['id' => 'on_hold', 'name' => _l('milestone_status_on_hold')]
                                ], ['id', 'name'], 'status', isset($milestone) ? $milestone->status : 'not_started', ['data-width' => '100%']); ?>
                            </div>
                        </div>

                        <!-- Progress and Budget -->
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="form-section-title"><?= _l('progress_budget'); ?></h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="progress_percentage"><?= _l('progress_percentage'); ?></label>
                                    <div class="input-group">
                                        <input type="number" id="progress_percentage" name="progress_percentage" 
                                               class="form-control" min="0" max="100" 
                                               value="<?= isset($milestone) ? $milestone->progress_percentage : 0; ?>">
                                        <div class="input-group-addon">%</div>
                                    </div>
                                    <div class="progress progress-sm" style="margin-top: 10px;">
                                        <div class="progress-bar" id="progress-bar" style="width: <?= isset($milestone) ? $milestone->progress_percentage : 0; ?>%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="estimated_hours"><?= _l('estimated_hours'); ?></label>
                                    <div class="input-group">
                                        <input type="number" id="estimated_hours" name="estimated_hours" 
                                               class="form-control" min="0" step="0.5" 
                                               value="<?= isset($milestone) ? $milestone->estimated_hours : ''; ?>">
                                        <div class="input-group-addon"><?= _l('hours'); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Dependencies -->
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="form-section-title"><?= _l('dependencies'); ?></h5>
                                <p class="text-muted"><?= _l('milestone_dependencies_help'); ?></p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="dependencies"><?= _l('depends_on_milestones'); ?></label>
                                    <select id="dependencies" name="dependencies[]" class="form-control selectpicker" 
                                            multiple data-width="100%" data-none-selected-text="<?= _l('no_dependencies'); ?>">
                                        <?php if (!empty($available_milestones)): ?>
                                            <?php foreach ($available_milestones as $available_milestone): ?>
                                                <option value="<?= $available_milestone['id']; ?>" 
                                                        <?= isset($milestone_dependencies) && in_array($available_milestone['id'], array_column($milestone_dependencies, 'depends_on_milestone_id')) ? 'selected' : ''; ?>>
                                                    <?= $available_milestone['project_name']; ?> - <?= $available_milestone['name']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                    <small class="text-muted"><?= _l('dependency_warning'); ?></small>
                                </div>
                            </div>
                        </div>

                        <!-- Notifications -->
                        <div class="row">
                            <div class="col-md-12">
                                <h5 class="form-section-title"><?= _l('notifications'); ?></h5>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="checkbox">
                                    <input type="checkbox" id="notify_assigned" name="notify_assigned" value="1" 
                                           <?= isset($milestone) && $milestone->notify_assigned ? 'checked' : ''; ?>>
                                    <label for="notify_assigned"><?= _l('notify_assigned_staff'); ?></label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="checkbox">
                                    <input type="checkbox" id="notify_before_due" name="notify_before_due" value="1" 
                                           <?= isset($milestone) && $milestone->notify_before_due ? 'checked' : ''; ?>>
                                    <label for="notify_before_due"><?= _l('notify_before_due_date'); ?></label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notify_days_before"><?= _l('notify_days_before'); ?></label>
                                    <input type="number" id="notify_days_before" name="notify_days_before" 
                                           class="form-control" min="1" max="30" 
                                           value="<?= isset($milestone) ? $milestone->notify_days_before : 3; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Custom Fields -->
                        <?php if (!empty($custom_fields)): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="form-section-title"><?= _l('custom_fields'); ?></h5>
                                </div>
                            </div>
                            
                            <?php foreach ($custom_fields as $field): ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <?= render_custom_field($field, isset($milestone) ? $milestone->id : null); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Form Actions -->
                        <div class="row">
                            <div class="col-md-12">
                                <hr>
                                <div class="btn-bottom-toolbar text-right">
                                    <button type="button" class="btn btn-default" onclick="window.history.back();">
                                        <?= _l('cancel'); ?>
                                    </button>
                                    <button type="submit" class="btn btn-info">
                                        <i class="fa fa-save"></i> <?= _l('save_milestone'); ?>
                                    </button>
                                    <?php if (isset($milestone)): ?>
                                        <div class="btn-group dropup">
                                            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                                                <?= _l('save_and'); ?> <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu">
                                                <li><a href="#" onclick="saveAndCreateNew()"><?= _l('save_and_create_new'); ?></a></li>
                                                <li><a href="#" onclick="saveAndView()"><?= _l('save_and_view'); ?></a></li>
                                                <li><a href="#" onclick="saveAndContinue()"><?= _l('save_and_continue_editing'); ?></a></li>
                                            </ul>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <?= form_close(); ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-md-4">
                <!-- Milestone Template -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('milestone_templates'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <p class="text-muted"><?= _l('milestone_template_help'); ?></p>
                        <div class="form-group">
                            <select id="milestone-template" class="form-control selectpicker" data-width="100%">
                                <option value=""><?= _l('select_template'); ?></option>
                                <?php if (!empty($milestone_templates)): ?>
                                    <?php foreach ($milestone_templates as $template): ?>
                                        <option value="<?= $template['id']; ?>" data-template='<?= json_encode($template); ?>'>
                                            <?= $template['name']; ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <button type="button" class="btn btn-default btn-block" onclick="applyTemplate()">
                            <i class="fa fa-magic"></i> <?= _l('apply_template'); ?>
                        </button>
                    </div>
                </div>

                <!-- Project Information -->
                <?php if (isset($project_info)): ?>
                    <div class="panel_s">
                        <div class="panel-header">
                            <h5 class="panel-title"><?= _l('project_information'); ?></h5>
                        </div>
                        <div class="panel-body">
                            <p><strong><?= _l('project_name'); ?>:</strong> <?= $project_info->name; ?></p>
                            <p><strong><?= _l('start_date'); ?>:</strong> <?= _d($project_info->start_date); ?></p>
                            <p><strong><?= _l('deadline'); ?>:</strong> <?= $project_info->deadline ? _d($project_info->deadline) : _l('no_deadline'); ?></p>
                            <p><strong><?= _l('status'); ?>:</strong> 
                                <span class="label label-<?= project_status_color($project_info->status); ?>">
                                    <?= _l('project_status_' . $project_info->status); ?>
                                </span>
                            </p>
                            <a href="<?= admin_url('projects/view/' . $project_info->id); ?>" class="btn btn-default btn-block">
                                <i class="fa fa-eye"></i> <?= _l('view_project'); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Tips -->
                <div class="panel_s">
                    <div class="panel-header">
                        <h5 class="panel-title"><?= _l('tips'); ?></h5>
                    </div>
                    <div class="panel-body">
                        <ul class="list-unstyled">
                            <li><i class="fa fa-lightbulb-o text-warning"></i> <?= _l('milestone_tip_1'); ?></li>
                            <li><i class="fa fa-lightbulb-o text-warning"></i> <?= _l('milestone_tip_2'); ?></li>
                            <li><i class="fa fa-lightbulb-o text-warning"></i> <?= _l('milestone_tip_3'); ?></li>
                            <li><i class="fa fa-lightbulb-o text-warning"></i> <?= _l('milestone_tip_4'); ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
$(document).ready(function() {
    'use strict';
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize progress slider
    initProgressSlider();
    
    // Initialize date validation
    initDateValidation();
    
    // Initialize dependency management
    initDependencyManagement();
    
    // Auto-fill project information
    $('#project_id').on('change', function() {
        loadProjectInfo($(this).val());
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

/**
 * Initialize form validation
 */
function initFormValidation() {
    $('#milestone-form').validate({
        rules: {
            name: {
                required: true,
                minlength: 3,
                maxlength: 255
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
                required: '<?= _l('milestone_name_required'); ?>',
                minlength: '<?= _l('milestone_name_min_length'); ?>',
                maxlength: '<?= _l('milestone_name_max_length'); ?>'
            },
            project_id: {
                required: '<?= _l('project_required'); ?>'
            },
            start_date: {
                required: '<?= _l('start_date_required'); ?>',
                date: '<?= _l('invalid_date_format'); ?>'
            },
            due_date: {
                date: '<?= _l('invalid_date_format'); ?>'
            }
        },
        submitHandler: function(form) {
            $(form).find('button[type="submit"]').prop('disabled', true);
            form.submit();
        }
    });
}

/**
 * Initialize progress slider
 */
function initProgressSlider() {
    $('#progress_percentage').on('input change', function() {
        var value = $(this).val();
        $('#progress-bar').css('width', value + '%');
        
        // Update progress bar color
        var progressBar = $('#progress-bar');
        progressBar.removeClass('progress-bar-danger progress-bar-warning progress-bar-success');
        
        if (value < 25) {
            progressBar.addClass('progress-bar-danger');
        } else if (value < 75) {
            progressBar.addClass('progress-bar-warning');
        } else {
            progressBar.addClass('progress-bar-success');
        }
    });
}

/**
 * Initialize date validation
 */
function initDateValidation() {
    $('#start_date, #due_date').on('change', function() {
        var startDate = new Date($('#start_date').val());
        var dueDate = new Date($('#due_date').val());
        
        if (startDate && dueDate && startDate > dueDate) {
            alert('<?= _l('start_date_before_due_date'); ?>');
            $('#due_date').val('');
        }
    });
}

/**
 * Initialize dependency management
 */
function initDependencyManagement() {
    $('#dependencies').on('change', function() {
        var selectedDependencies = $(this).val() || [];
        
        // Check for circular dependencies
        if (selectedDependencies.length > 0) {
            checkCircularDependencies(selectedDependencies);
        }
    });
}

/**
 * Check for circular dependencies
 */
function checkCircularDependencies(dependencies) {
    var milestoneId = <?= isset($milestone) ? $milestone->id : 'null'; ?>;
    
    if (!milestoneId) return;
    
    $.ajax({
        url: admin_url + 'project_enhancement/milestones/check_circular_dependencies',
        type: 'POST',
        data: {
            milestone_id: milestoneId,
            dependencies: dependencies
        },
        dataType: 'json',
        success: function(response) {
            if (response.has_circular) {
                alert('<?= _l('circular_dependency_detected'); ?>: ' + response.circular_path.join(' → '));
                // Remove the problematic dependency
                $('#dependencies').selectpicker('val', response.safe_dependencies);
            }
        }
    });
}

/**
 * Load project information
 */
function loadProjectInfo(projectId) {
    if (!projectId) return;
    
    $.ajax({
        url: admin_url + 'project_enhancement/milestones/get_project_info/' + projectId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateProjectInfo(response.project);
                loadAvailableMilestones(projectId);
            }
        }
    });
}

/**
 * Update project information display
 */
function updateProjectInfo(project) {
    // This would update the project info panel if it exists
    console.log('Project info loaded:', project);
}

/**
 * Load available milestones for dependencies
 */
function loadAvailableMilestones(projectId) {
    $.ajax({
        url: admin_url + 'project_enhancement/milestones/get_available_milestones/' + projectId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateDependencyOptions(response.milestones);
            }
        }
    });
}

/**
 * Update dependency options
 */
function updateDependencyOptions(milestones) {
    var $select = $('#dependencies');
    var currentValues = $select.val() || [];
    
    $select.empty();
    
    milestones.forEach(function(milestone) {
        $select.append(new Option(milestone.project_name + ' - ' + milestone.name, milestone.id));
    });
    
    $select.selectpicker('refresh');
    $select.selectpicker('val', currentValues);
}

/**
 * Apply milestone template
 */
function applyTemplate() {
    var selectedTemplate = $('#milestone-template').val();
    if (!selectedTemplate) {
        alert('<?= _l('select_template_first'); ?>');
        return;
    }
    
    var templateData = $('#milestone-template option:selected').data('template');
    
    if (confirm('<?= _l('confirm_apply_template'); ?>')) {
        // Apply template data to form
        if (templateData.name) $('#name').val(templateData.name);
        if (templateData.description) $('#description').val(templateData.description);
        if (templateData.priority) $('#priority').selectpicker('val', templateData.priority);
        if (templateData.estimated_hours) $('#estimated_hours').val(templateData.estimated_hours);
        
        // Refresh selectpickers
        $('.selectpicker').selectpicker('refresh');
        
        alert('<?= _l('template_applied_successfully'); ?>');
    }
}

/**
 * Save and create new
 */
function saveAndCreateNew() {
    $('#milestone-form').append('<input type="hidden" name="save_and_create_new" value="1">');
    $('#milestone-form').submit();
}

/**
 * Save and view
 */
function saveAndView() {
    $('#milestone-form').append('<input type="hidden" name="save_and_view" value="1">');
    $('#milestone-form').submit();
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
.form-section-title {
    color: #333;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
    margin-bottom: 20px;
    margin-top: 30px;
}

.form-section-title:first-child {
    margin-top: 0;
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

.progress-sm {
    height: 8px;
}

.btn-bottom-toolbar {
    padding: 15px 0;
    border-top: 1px solid #eee;
    margin-top: 20px;
}

.list-unstyled li {
    margin-bottom: 8px;
    padding-left: 5px;
}

.dependency-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    padding: 10px;
    margin-top: 10px;
}

.milestone-template-preview {
    background: #f8f9fa;
    border-radius: 4px;
    padding: 10px;
    margin-top: 10px;
    font-size: 12px;
}

@media (max-width: 768px) {
    .btn-bottom-toolbar {
        text-align: center;
    }
    
    .btn-bottom-toolbar .btn {
        margin-bottom: 5px;
    }
}
</style>