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
                                    <?= _l('milestone_management'); ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <a href="<?= admin_url('project_enhancement/milestones/create'); ?>" class="btn btn-info">
                                        <i class="fa fa-plus"></i> <?= _l('new_milestone'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/milestones/gantt'); ?>" class="btn btn-success">
                                        <i class="fa fa-chart-gantt"></i> <?= _l('gantt_view'); ?>
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                            <i class="fa fa-download"></i> <?= _l('export'); ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="<?= admin_url('project_enhancement/milestones/export/csv'); ?>">CSV</a></li>
                                            <li><a href="<?= admin_url('project_enhancement/milestones/export/excel'); ?>">Excel</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open(admin_url('project_enhancement/milestones'), ['method' => 'GET', 'id' => 'milestones-filter-form']); ?>
                        <div class="row">
                            <div class="col-md-3">
                                <?= render_select('project_id', $projects, ['id', 'name'], 'project', $current_filters['project_id'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_projects')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_select('status', [
                                    ['id' => 'not_started', 'name' => _l('milestone_status_not_started')],
                                    ['id' => 'in_progress', 'name' => _l('milestone_status_in_progress')],
                                    ['id' => 'completed', 'name' => _l('milestone_status_completed')],
                                    ['id' => 'on_hold', 'name' => _l('milestone_status_on_hold')]
                                ], ['id', 'name'], 'status', $current_filters['status'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_statuses')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_select('priority', [
                                    ['id' => 'low', 'name' => _l('priority_low')],
                                    ['id' => 'medium', 'name' => _l('priority_medium')],
                                    ['id' => 'high', 'name' => _l('priority_high')],
                                    ['id' => 'critical', 'name' => _l('priority_critical')]
                                ], ['id', 'name'], 'priority', $current_filters['priority'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_priorities')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_date_input('due_date_from', 'due_date_from', $current_filters['due_date_from'], ['placeholder' => _l('due_date_from')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_date_input('due_date_to', 'due_date_to', $current_filters['due_date_to'], ['placeholder' => _l('due_date_to')]); ?>
                            </div>
                            <div class="col-md-1">
                                <button type="submit" class="btn btn-info btn-block">
                                    <i class="fa fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-info"><?= $milestone_stats['total'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('total_milestones'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-warning"><?= $milestone_stats['in_progress'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('in_progress'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-success"><?= $milestone_stats['completed'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('completed'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-danger"><?= $milestone_stats['overdue'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('overdue'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Milestones Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Bulk Actions -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= form_open(admin_url('project_enhancement/milestones/bulk_action'), ['id' => 'bulk-actions-form']); ?>
                                <div class="form-group">
                                    <div class="input-group">
                                        <select name="bulk_action" class="form-control" required>
                                            <option value=""><?= _l('bulk_actions'); ?></option>
                                            <option value="complete"><?= _l('mark_as_completed'); ?></option>
                                            <option value="start"><?= _l('mark_as_in_progress'); ?></option>
                                            <option value="hold"><?= _l('mark_as_on_hold'); ?></option>
                                            <?php if (staff_can('delete', 'project_enhancement')): ?>
                                                <option value="delete"><?= _l('delete'); ?></option>
                                            <?php endif; ?>
                                        </select>
                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-info" disabled id="bulk-action-btn">
                                                <?= _l('apply'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?= form_close(); ?>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-default" onclick="toggleTableView('card')">
                                        <i class="fa fa-th-large"></i> <?= _l('card_view'); ?>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="toggleTableView('table')">
                                        <i class="fa fa-table"></i> <?= _l('table_view'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="table-view">
                            <div class="table-responsive">
                                <table class="table table-striped dt-table" id="milestones-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="select-all-milestones">
                                            </th>
                                            <th><?= _l('milestone_name'); ?></th>
                                            <th><?= _l('project'); ?></th>
                                            <th><?= _l('due_date'); ?></th>
                                            <th><?= _l('progress'); ?></th>
                                            <th><?= _l('status'); ?></th>
                                            <th><?= _l('priority'); ?></th>
                                            <th><?= _l('assigned_to'); ?></th>
                                            <th><?= _l('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($milestones)): ?>
                                            <?php foreach ($milestones as $milestone): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="milestone_ids[]" value="<?= $milestone['id']; ?>" class="milestone-checkbox">
                                                    </td>
                                                    <td>
                                                        <a href="<?= admin_url('project_enhancement/milestones/view/' . $milestone['id']); ?>" class="font-medium">
                                                            <?= $milestone['name']; ?>
                                                        </a>
                                                        <?php if (!empty($milestone['description'])): ?>
                                                            <br><small class="text-muted"><?= character_limiter($milestone['description'], 50); ?></small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <a href="<?= admin_url('projects/view/' . $milestone['project_id']); ?>">
                                                            <?= $milestone['project_name']; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php if ($milestone['due_date']): ?>
                                                            <span class="text-<?= strtotime($milestone['due_date']) < time() && $milestone['status'] !== 'completed' ? 'danger' : 'muted'; ?>">
                                                                <?= _d($milestone['due_date']); ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted"><?= _l('no_due_date'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="progress progress-sm">
                                                            <div class="progress-bar progress-bar-<?= $milestone['progress_percentage'] >= 75 ? 'success' : ($milestone['progress_percentage'] >= 50 ? 'warning' : 'danger'); ?>" 
                                                                 style="width: <?= $milestone['progress_percentage']; ?>%">
                                                            </div>
                                                        </div>
                                                        <small><?= $milestone['progress_percentage']; ?>%</small>
                                                    </td>
                                                    <td>
                                                        <span class="label label-<?= milestone_status_color($milestone['status']); ?>">
                                                            <?= _l('milestone_status_' . $milestone['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="label label-<?= priority_color($milestone['priority']); ?>">
                                                            <?= _l('priority_' . $milestone['priority']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($milestone['assigned_to']): ?>
                                                            <a href="<?= admin_url('staff/profile/' . $milestone['assigned_to']); ?>">
                                                                <?= $milestone['assigned_firstname'] . ' ' . $milestone['assigned_lastname']; ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted"><?= _l('unassigned'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= admin_url('project_enhancement/milestones/view/' . $milestone['id']); ?>" 
                                                               class="btn btn-default btn-xs" data-toggle="tooltip" title="<?= _l('view'); ?>">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                            <?php if (staff_can('edit', 'project_enhancement')): ?>
                                                                <a href="<?= admin_url('project_enhancement/milestones/edit/' . $milestone['id']); ?>" 
                                                                   class="btn btn-default btn-xs" data-toggle="tooltip" title="<?= _l('edit'); ?>">
                                                                    <i class="fa fa-edit"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                                                    <i class="fa fa-cog"></i> <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-right">
                                                                    <li><a href="<?= admin_url('project_enhancement/milestones/dependencies/' . $milestone['id']); ?>">
                                                                        <i class="fa fa-sitemap"></i> <?= _l('dependencies'); ?>
                                                                    </a></li>
                                                                    <li><a href="<?= admin_url('project_enhancement/milestones/update_progress/' . $milestone['id']); ?>">
                                                                        <i class="fa fa-tasks"></i> <?= _l('update_progress'); ?>
                                                                    </a></li>
                                                                    <?php if ($milestone['status'] !== 'completed'): ?>
                                                                        <li><a href="<?= admin_url('project_enhancement/milestones/mark_complete/' . $milestone['id']); ?>">
                                                                            <i class="fa fa-check"></i> <?= _l('mark_complete'); ?>
                                                                        </a></li>
                                                                    <?php endif; ?>
                                                                    <?php if (staff_can('delete', 'project_enhancement')): ?>
                                                                        <li class="divider"></li>
                                                                        <li><a href="<?= admin_url('project_enhancement/milestones/delete/' . $milestone['id']); ?>" 
                                                                               class="text-danger _delete">
                                                                            <i class="fa fa-trash"></i> <?= _l('delete'); ?>
                                                                        </a></li>
                                                                    <?php endif; ?>
                                                                </ul>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Card View -->
                        <div id="card-view" style="display: none;">
                            <div class="row">
                                <?php if (!empty($milestones)): ?>
                                    <?php foreach ($milestones as $milestone): ?>
                                        <div class="col-md-4 col-sm-6">
                                            <div class="panel panel-default milestone-card">
                                                <div class="panel-body">
                                                    <div class="milestone-header">
                                                        <div class="pull-right">
                                                            <span class="label label-<?= milestone_status_color($milestone['status']); ?>">
                                                                <?= _l('milestone_status_' . $milestone['status']); ?>
                                                            </span>
                                                        </div>
                                                        <h5 class="milestone-title">
                                                            <a href="<?= admin_url('project_enhancement/milestones/view/' . $milestone['id']); ?>">
                                                                <?= $milestone['name']; ?>
                                                            </a>
                                                        </h5>
                                                        <p class="text-muted"><?= $milestone['project_name']; ?></p>
                                                    </div>
                                                    
                                                    <div class="milestone-progress">
                                                        <div class="progress progress-sm">
                                                            <div class="progress-bar progress-bar-<?= $milestone['progress_percentage'] >= 75 ? 'success' : ($milestone['progress_percentage'] >= 50 ? 'warning' : 'danger'); ?>" 
                                                                 style="width: <?= $milestone['progress_percentage']; ?>%">
                                                            </div>
                                                        </div>
                                                        <small><?= $milestone['progress_percentage']; ?>% <?= _l('complete'); ?></small>
                                                    </div>
                                                    
                                                    <div class="milestone-meta">
                                                        <?php if ($milestone['due_date']): ?>
                                                            <p><i class="fa fa-calendar"></i> <?= _d($milestone['due_date']); ?></p>
                                                        <?php endif; ?>
                                                        <?php if ($milestone['assigned_to']): ?>
                                                            <p><i class="fa fa-user"></i> <?= $milestone['assigned_firstname'] . ' ' . $milestone['assigned_lastname']; ?></p>
                                                        <?php endif; ?>
                                                        <p><i class="fa fa-flag"></i> <?= _l('priority_' . $milestone['priority']); ?></p>
                                                    </div>
                                                    
                                                    <div class="milestone-actions">
                                                        <a href="<?= admin_url('project_enhancement/milestones/view/' . $milestone['id']); ?>" 
                                                           class="btn btn-default btn-xs">
                                                            <i class="fa fa-eye"></i> <?= _l('view'); ?>
                                                        </a>
                                                        <?php if (staff_can('edit', 'project_enhancement')): ?>
                                                            <a href="<?= admin_url('project_enhancement/milestones/edit/' . $milestone['id']); ?>" 
                                                               class="btn btn-info btn-xs">
                                                                <i class="fa fa-edit"></i> <?= _l('edit'); ?>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="col-md-12">
                                        <div class="text-center">
                                            <h4 class="text-muted"><?= _l('no_milestones_found'); ?></h4>
                                            <p class="text-muted"><?= _l('create_first_milestone'); ?></p>
                                            <a href="<?= admin_url('project_enhancement/milestones/create'); ?>" class="btn btn-info">
                                                <i class="fa fa-plus"></i> <?= _l('new_milestone'); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Pagination -->
                        <?php if (!empty($milestones) && $total_milestones > 25): ?>
                            <div class="row">
                                <div class="col-md-12">
                                    <?= $this->pagination->create_links(); ?>
                                </div>
                            </div>
                        <?php endif; ?>
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
    
    // Initialize DataTable
    var table = $('#milestones-table').DataTable({
        "order": [[2, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 8] }
        ]
    });
    
    // Select all checkboxes
    $('#select-all-milestones').on('change', function() {
        $('.milestone-checkbox').prop('checked', this.checked);
        toggleBulkActionButton();
    });
    
    // Individual checkbox change
    $('.milestone-checkbox').on('change', function() {
        toggleBulkActionButton();
    });
    
    // Toggle bulk action button
    function toggleBulkActionButton() {
        var checkedCount = $('.milestone-checkbox:checked').length;
        $('#bulk-action-btn').prop('disabled', checkedCount === 0);
    }
    
    // Bulk actions form submission
    $('#bulk-actions-form').on('submit', function(e) {
        var checkedMilestones = $('.milestone-checkbox:checked');
        if (checkedMilestones.length === 0) {
            e.preventDefault();
            alert('<?= _l('no_milestones_selected'); ?>');
            return false;
        }
        
        // Add selected milestone IDs to form
        checkedMilestones.each(function() {
            $(this).clone().appendTo('#bulk-actions-form');
        });
        
        var action = $('select[name="bulk_action"]').val();
        if (action === 'delete') {
            if (!confirm('<?= _l('confirm_delete_milestones'); ?>')) {
                e.preventDefault();
                return false;
            }
        }
    });
    
    // Auto-submit filter form on change
    $('#milestones-filter-form select, #milestones-filter-form input').on('change', function() {
        $('#milestones-filter-form').submit();
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

// Toggle between table and card view
function toggleTableView(view) {
    if (view === 'card') {
        $('#table-view').hide();
        $('#card-view').show();
        localStorage.setItem('milestones_view_preference', 'card');
    } else {
        $('#card-view').hide();
        $('#table-view').show();
        localStorage.setItem('milestones_view_preference', 'table');
    }
}

// Load user's view preference
$(document).ready(function() {
    var viewPreference = localStorage.getItem('milestones_view_preference');
    if (viewPreference === 'card') {
        toggleTableView('card');
    }
});
</script>

<style>
.milestone-card {
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.milestone-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.milestone-header {
    margin-bottom: 15px;
    min-height: 60px;
}

.milestone-title {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.milestone-progress {
    margin-bottom: 15px;
}

.milestone-meta {
    margin-bottom: 15px;
}

.milestone-meta p {
    margin: 5px 0;
    font-size: 12px;
}

.milestone-actions {
    text-align: right;
}

.progress-sm {
    height: 10px;
}

.dt-table {
    width: 100% !important;
}

.panel_s {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
</style>