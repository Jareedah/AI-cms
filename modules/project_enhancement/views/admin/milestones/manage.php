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
                                    <i class="fa fa-flag-checkered"></i> 
                                    <?= _l('milestones_management'); ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <?php if (staff_can('create', 'project_enhancement')): ?>
                                        <a href="<?= admin_url('project_enhancement/milestones/create'); ?>" class="btn btn-info">
                                            <i class="fa fa-plus"></i> <?= _l('new_milestone'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= admin_url('project_enhancement/milestones/gantt'); ?>" class="btn btn-default">
                                        <i class="fa fa-sitemap"></i> <?= _l('gantt_view'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/milestones/templates'); ?>" class="btn btn-default">
                                        <i class="fa fa-template"></i> <?= _l('templates'); ?>
                                    </a>
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
                                <?= render_select('project_id', $projects, ['id', 'name'], 'project', $current_filters['project_id'] ?? '', ['data-width' => '100%', 'data-none-selected-text' => _l('all_projects')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_select('status', [
                                    ['id' => 'not_started', 'name' => _l('milestone_status_not_started')],
                                    ['id' => 'in_progress', 'name' => _l('milestone_status_in_progress')],
                                    ['id' => 'completed', 'name' => _l('milestone_status_completed')],
                                    ['id' => 'on_hold', 'name' => _l('milestone_status_on_hold')],
                                    ['id' => 'cancelled', 'name' => _l('milestone_status_cancelled')]
                                ], ['id', 'name'], 'status', $current_filters['status'] ?? '', ['data-width' => '100%', 'data-none-selected-text' => _l('all_statuses')]); ?>
                            </div>
                            <div class="col-md-3">
                                <?= render_select('assigned_to', $staff_members, ['staffid', ['firstname', 'lastname']], 'assigned_to', $current_filters['assigned_to'] ?? '', ['data-width' => '100%', 'data-none-selected-text' => _l('all_staff')]); ?>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-info btn-block">
                                        <i class="fa fa-search"></i> <?= _l('filter'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?= form_close(); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Milestones Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table table-striped dt-table" id="milestones-table">
                                <thead>
                                    <tr>
                                        <th><?= _l('milestone_name'); ?></th>
                                        <th><?= _l('project'); ?></th>
                                        <th><?= _l('assigned_to'); ?></th>
                                        <th><?= _l('due_date'); ?></th>
                                        <th><?= _l('progress'); ?></th>
                                        <th><?= _l('status'); ?></th>
                                        <th><?= _l('priority'); ?></th>
                                        <th><?= _l('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($milestones)): ?>
                                        <?php foreach ($milestones as $milestone): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?= admin_url('project_enhancement/milestones/view/' . $milestone['id']); ?>">
                                                        <?= $milestone['name']; ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <a href="<?= admin_url('projects/view/' . $milestone['project_id']); ?>">
                                                        <?= $milestone['project_name']; ?>
                                                    </a>
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
                                                    <?php if ($milestone['due_date']): ?>
                                                        <span class="text-<?= strtotime($milestone['due_date']) < time() && $milestone['status'] !== 'completed' ? 'danger' : 'muted'; ?>">
                                                            <?= _d($milestone['due_date']); ?>
                                                            <?php if (strtotime($milestone['due_date']) < time() && $milestone['status'] !== 'completed'): ?>
                                                                <small class="text-danger">(<?= _l('overdue'); ?>)</small>
                                                            <?php endif; ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="text-muted"><?= _l('no_due_date'); ?></span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="progress progress-sm">
                                                        <div class="progress-bar progress-bar-<?= $milestone['progress_percentage'] >= 75 ? 'success' : ($milestone['progress_percentage'] >= 50 ? 'info' : 'warning'); ?>" 
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
                                                        <?php if (staff_can('delete', 'project_enhancement')): ?>
                                                            <a href="<?= admin_url('project_enhancement/milestones/delete/' . $milestone['id']); ?>" 
                                                               class="btn btn-danger btn-xs _delete" data-toggle="tooltip" title="<?= _l('delete'); ?>">
                                                                <i class="fa fa-trash"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
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
    $('#milestones-table').DataTable({
        "responsive": true,
        "order": [[3, "asc"]], // Order by due date
        "columnDefs": [
            { "orderable": false, "targets": [7] } // Actions column
        ]
    });
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});
</script>

<style>
.progress-sm {
    height: 10px;
}

.label {
    font-size: 11px;
}

.btn-xs {
    padding: 1px 5px;
    font-size: 12px;
}
</style>