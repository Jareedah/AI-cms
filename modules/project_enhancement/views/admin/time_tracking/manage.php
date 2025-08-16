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
                                    <?= _l('time_entries_management'); ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <a href="<?= admin_url('project_enhancement/time_tracking/timer'); ?>" class="btn btn-success">
                                        <i class="fa fa-play"></i> <?= _l('start_timer'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/time_tracking/create'); ?>" class="btn btn-info">
                                        <i class="fa fa-plus"></i> <?= _l('new_time_entry'); ?>
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                            <i class="fa fa-download"></i> <?= _l('export'); ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="<?= admin_url('project_enhancement/time_tracking/export/csv'); ?>">CSV</a></li>
                                            <li><a href="<?= admin_url('project_enhancement/time_tracking/export/excel'); ?>">Excel</a></li>
                                            <li><a href="<?= admin_url('project_enhancement/time_tracking/export/pdf'); ?>">PDF</a></li>
                                        </ul>
                                    </div>
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

        <!-- Filters -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?= form_open(admin_url('project_enhancement/time_tracking'), ['method' => 'GET', 'id' => 'time-entries-filter-form']); ?>
                        <div class="row">
                            <div class="col-md-2">
                                <?= render_select('project_id', $projects, ['id', 'name'], 'project', $current_filters['project_id'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_projects')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_select('milestone_id', $milestones, ['id', 'name'], 'milestone', $current_filters['milestone_id'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_milestones')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_select('staff_id', $staff_members, ['staffid', ['firstname', 'lastname']], 'staff_member', $current_filters['staff_id'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_staff')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_select('status', [
                                    ['id' => 'draft', 'name' => _l('time_entry_status_draft')],
                                    ['id' => 'submitted', 'name' => _l('time_entry_status_submitted')],
                                    ['id' => 'approved', 'name' => _l('time_entry_status_approved')],
                                    ['id' => 'rejected', 'name' => _l('time_entry_status_rejected')]
                                ], ['id', 'name'], 'status', $current_filters['status'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_statuses')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_date_input('date_from', 'date_from', $current_filters['date_from'], ['placeholder' => _l('date_from')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_date_input('date_to', 'date_to', $current_filters['date_to'], ['placeholder' => _l('date_to')]); ?>
                            </div>
                        </div>
                        <div class="row mtop15">
                            <div class="col-md-2">
                                <?= render_select('category_id', $time_categories, ['id', 'name'], 'category', $current_filters['category_id'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_categories')]); ?>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="billable" class="form-control selectpicker" data-width="100%" data-none-selected-text="<?= _l('all_entries'); ?>">
                                        <option value=""><?= _l('all_entries'); ?></option>
                                        <option value="1" <?= $current_filters['billable'] === '1' ? 'selected' : ''; ?>><?= _l('billable_only'); ?></option>
                                        <option value="0" <?= $current_filters['billable'] === '0' ? 'selected' : ''; ?>><?= _l('non_billable_only'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <?= render_input('min_duration', 'min_duration_hours', $current_filters['min_duration'], 'number', ['step' => '0.25', 'min' => '0', 'placeholder' => _l('min_hours')]); ?>
                            </div>
                            <div class="col-md-2">
                                <?= render_input('max_duration', 'max_duration_hours', $current_filters['max_duration'], 'number', ['step' => '0.25', 'min' => '0', 'placeholder' => _l('max_hours')]); ?>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-info btn-block">
                                    <i class="fa fa-search"></i> <?= _l('filter'); ?>
                                </button>
                            </div>
                            <div class="col-md-2">
                                <a href="<?= admin_url('project_enhancement/time_tracking'); ?>" class="btn btn-default btn-block">
                                    <i class="fa fa-refresh"></i> <?= _l('clear'); ?>
                                </a>
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
                        <h3 class="text-info"><?= $time_stats['total_entries'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('total_entries'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-success"><?= number_format($time_stats['total_hours'] ?? 0, 2); ?>h</h3>
                        <p class="text-muted"><?= _l('total_hours'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-warning"><?= $time_stats['pending_approval'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('pending_approval'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-danger">$<?= number_format($time_stats['total_value'] ?? 0, 2); ?></h3>
                        <p class="text-muted"><?= _l('total_value'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Time Entries Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <!-- Bulk Actions -->
                        <div class="row">
                            <div class="col-md-6">
                                <?= form_open(admin_url('project_enhancement/time_tracking/bulk_action'), ['id' => 'bulk-actions-form']); ?>
                                <div class="form-group">
                                    <div class="input-group">
                                        <select name="bulk_action" class="form-control" required>
                                            <option value=""><?= _l('bulk_actions'); ?></option>
                                            <?php if (staff_can('manage_time_approvals', 'project_enhancement')): ?>
                                                <option value="approve"><?= _l('approve_selected'); ?></option>
                                                <option value="reject"><?= _l('reject_selected'); ?></option>
                                            <?php endif; ?>
                                            <option value="submit"><?= _l('submit_for_approval'); ?></option>
                                            <option value="mark_billable"><?= _l('mark_as_billable'); ?></option>
                                            <option value="mark_non_billable"><?= _l('mark_as_non_billable'); ?></option>
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
                                    <button type="button" class="btn btn-default" onclick="toggleTableView('list')">
                                        <i class="fa fa-list"></i> <?= _l('list_view'); ?>
                                    </button>
                                    <button type="button" class="btn btn-info" onclick="toggleTableView('table')">
                                        <i class="fa fa-table"></i> <?= _l('table_view'); ?>
                                    </button>
                                    <button type="button" class="btn btn-default" onclick="toggleTableView('calendar')">
                                        <i class="fa fa-calendar"></i> <?= _l('calendar_view'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Table View -->
                        <div id="table-view">
                            <div class="table-responsive">
                                <table class="table table-striped dt-table" id="time-entries-table">
                                    <thead>
                                        <tr>
                                            <th>
                                                <input type="checkbox" id="select-all-entries">
                                            </th>
                                            <th><?= _l('date'); ?></th>
                                            <th><?= _l('staff_member'); ?></th>
                                            <th><?= _l('project'); ?></th>
                                            <th><?= _l('milestone'); ?></th>
                                            <th><?= _l('category'); ?></th>
                                            <th><?= _l('duration'); ?></th>
                                            <th><?= _l('description'); ?></th>
                                            <th><?= _l('billable'); ?></th>
                                            <th><?= _l('status'); ?></th>
                                            <th><?= _l('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($time_entries)): ?>
                                            <?php foreach ($time_entries as $entry): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="entry_ids[]" value="<?= $entry['id']; ?>" class="entry-checkbox">
                                                    </td>
                                                    <td>
                                                        <span data-toggle="tooltip" title="<?= _dt($entry['created_at']); ?>">
                                                            <?= _d($entry['date']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <a href="<?= admin_url('staff/profile/' . $entry['staff_id']); ?>">
                                                            <?= $entry['staff_firstname'] . ' ' . $entry['staff_lastname']; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <a href="<?= admin_url('projects/view/' . $entry['project_id']); ?>">
                                                            <?= $entry['project_name']; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <?php if ($entry['milestone_id']): ?>
                                                            <a href="<?= admin_url('project_enhancement/milestones/view/' . $entry['milestone_id']); ?>">
                                                                <?= $entry['milestone_name']; ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($entry['category_name']): ?>
                                                            <span class="label" style="background-color: <?= $entry['category_color'] ?? '#6c757d'; ?>">
                                                                <?= $entry['category_name']; ?>
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="font-medium">
                                                            <?= format_duration($entry['duration']); ?>
                                                        </span>
                                                        <?php if ($entry['hourly_rate']): ?>
                                                            <br><small class="text-muted">
                                                                $<?= number_format($entry['hourly_rate'], 2); ?>/h
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span data-toggle="tooltip" title="<?= $entry['description']; ?>">
                                                            <?= character_limiter($entry['description'], 40); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <?php if ($entry['billable']): ?>
                                                            <span class="label label-success"><?= _l('yes'); ?></span>
                                                        <?php else: ?>
                                                            <span class="label label-default"><?= _l('no'); ?></span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="label label-<?= time_entry_status_color($entry['status']); ?>">
                                                            <?= _l('time_entry_status_' . $entry['status']); ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= admin_url('project_enhancement/time_tracking/view/' . $entry['id']); ?>" 
                                                               class="btn btn-default btn-xs" data-toggle="tooltip" title="<?= _l('view'); ?>">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                            <?php if (staff_can('edit', 'project_enhancement') && ($entry['status'] === 'draft' || is_admin())): ?>
                                                                <a href="<?= admin_url('project_enhancement/time_tracking/edit/' . $entry['id']); ?>" 
                                                                   class="btn btn-default btn-xs" data-toggle="tooltip" title="<?= _l('edit'); ?>">
                                                                    <i class="fa fa-edit"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                                                    <i class="fa fa-cog"></i> <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-right">
                                                                    <?php if ($entry['status'] === 'draft'): ?>
                                                                        <li><a href="<?= admin_url('project_enhancement/time_tracking/submit/' . $entry['id']); ?>">
                                                                            <i class="fa fa-paper-plane"></i> <?= _l('submit_for_approval'); ?>
                                                                        </a></li>
                                                                    <?php endif; ?>
                                                                    <?php if ($entry['status'] === 'submitted' && staff_can('manage_time_approvals', 'project_enhancement')): ?>
                                                                        <li><a href="<?= admin_url('project_enhancement/time_tracking/approve/' . $entry['id']); ?>">
                                                                            <i class="fa fa-check text-success"></i> <?= _l('approve'); ?>
                                                                        </a></li>
                                                                        <li><a href="<?= admin_url('project_enhancement/time_tracking/reject/' . $entry['id']); ?>">
                                                                            <i class="fa fa-times text-danger"></i> <?= _l('reject'); ?>
                                                                        </a></li>
                                                                    <?php endif; ?>
                                                                    <li><a href="<?= admin_url('project_enhancement/time_tracking/duplicate/' . $entry['id']); ?>">
                                                                        <i class="fa fa-copy"></i> <?= _l('duplicate'); ?>
                                                                    </a></li>
                                                                    <li><a href="<?= admin_url('project_enhancement/time_tracking/continue/' . $entry['id']); ?>">
                                                                        <i class="fa fa-play"></i> <?= _l('continue_timer'); ?>
                                                                    </a></li>
                                                                    <?php if (staff_can('delete', 'project_enhancement') && ($entry['status'] === 'draft' || is_admin())): ?>
                                                                        <li class="divider"></li>
                                                                        <li><a href="<?= admin_url('project_enhancement/time_tracking/delete/' . $entry['id']); ?>" 
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

                        <!-- List View -->
                        <div id="list-view" style="display: none;">
                            <div class="time-entries-list">
                                <?php if (!empty($time_entries)): ?>
                                    <?php foreach ($time_entries as $entry): ?>
                                        <div class="time-entry-card">
                                            <div class="entry-header">
                                                <div class="entry-checkbox">
                                                    <input type="checkbox" name="entry_ids[]" value="<?= $entry['id']; ?>" class="entry-checkbox">
                                                </div>
                                                <div class="entry-date">
                                                    <?= _d($entry['date']); ?>
                                                </div>
                                                <div class="entry-duration">
                                                    <?= format_duration($entry['duration']); ?>
                                                </div>
                                                <div class="entry-status">
                                                    <span class="label label-<?= time_entry_status_color($entry['status']); ?>">
                                                        <?= _l('time_entry_status_' . $entry['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="entry-content">
                                                <div class="entry-project">
                                                    <strong><?= $entry['project_name']; ?></strong>
                                                    <?php if ($entry['milestone_name']): ?>
                                                        <span class="text-muted"> • <?= $entry['milestone_name']; ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="entry-description">
                                                    <?= $entry['description']; ?>
                                                </div>
                                                <div class="entry-meta">
                                                    <span class="entry-staff">
                                                        <i class="fa fa-user"></i> <?= $entry['staff_firstname'] . ' ' . $entry['staff_lastname']; ?>
                                                    </span>
                                                    <?php if ($entry['category_name']): ?>
                                                        <span class="entry-category">
                                                            <i class="fa fa-tag"></i> <?= $entry['category_name']; ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <?php if ($entry['billable']): ?>
                                                        <span class="entry-billable">
                                                            <i class="fa fa-dollar-sign text-success"></i> <?= _l('billable'); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <div class="entry-actions">
                                                <a href="<?= admin_url('project_enhancement/time_tracking/view/' . $entry['id']); ?>" 
                                                   class="btn btn-default btn-xs">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <?php if (staff_can('edit', 'project_enhancement') && ($entry['status'] === 'draft' || is_admin())): ?>
                                                    <a href="<?= admin_url('project_enhancement/time_tracking/edit/' . $entry['id']); ?>" 
                                                       class="btn btn-info btn-xs">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-center">
                                        <h4 class="text-muted"><?= _l('no_time_entries_found'); ?></h4>
                                        <p class="text-muted"><?= _l('create_first_time_entry'); ?></p>
                                        <a href="<?= admin_url('project_enhancement/time_tracking/timer'); ?>" class="btn btn-success">
                                            <i class="fa fa-play"></i> <?= _l('start_timer'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Calendar View -->
                        <div id="calendar-view" style="display: none;">
                            <div id="time-entries-calendar"></div>
                        </div>

                        <!-- Pagination -->
                        <?php if (!empty($time_entries) && $total_entries > 25): ?>
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
    var table = $('#time-entries-table').DataTable({
        "order": [[1, "desc"]],
        "columnDefs": [
            { "orderable": false, "targets": [0, 10] }
        ]
    });
    
    // Initialize view management
    initializeViewToggle();
    
    // Initialize bulk actions
    initializeBulkActions();
    
    // Initialize calendar if FullCalendar is available
    if (typeof FullCalendar !== 'undefined') {
        initializeCalendar();
    }
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-submit filter form on change
    $('#time-entries-filter-form select, #time-entries-filter-form input').on('change', function() {
        $('#time-entries-filter-form').submit();
    });
    
    // Project change handler for milestone loading
    $('select[name="project_id"]').on('change', function() {
        loadProjectMilestones($(this).val());
    });
});

/**
 * Initialize view toggle functionality
 */
function initializeViewToggle() {
    // Load user's view preference
    var viewPreference = localStorage.getItem('time_entries_view_preference') || 'table';
    toggleTableView(viewPreference);
}

/**
 * Toggle between different views
 */
function toggleTableView(view) {
    $('#table-view, #list-view, #calendar-view').hide();
    
    switch(view) {
        case 'list':
            $('#list-view').show();
            break;
        case 'calendar':
            $('#calendar-view').show();
            if (typeof calendar !== 'undefined') {
                calendar.render();
            }
            break;
        default:
            $('#table-view').show();
            view = 'table';
    }
    
    localStorage.setItem('time_entries_view_preference', view);
    
    // Update button states
    $('.btn-group button').removeClass('btn-info').addClass('btn-default');
    $('button[onclick="toggleTableView(\'' + view + '\')"]').removeClass('btn-default').addClass('btn-info');
}

/**
 * Initialize bulk actions
 */
function initializeBulkActions() {
    // Select all checkboxes
    $('#select-all-entries').on('change', function() {
        $('.entry-checkbox').prop('checked', this.checked);
        toggleBulkActionButton();
    });
    
    // Individual checkbox change
    $('.entry-checkbox').on('change', function() {
        toggleBulkActionButton();
    });
    
    // Bulk actions form submission
    $('#bulk-actions-form').on('submit', function(e) {
        var checkedEntries = $('.entry-checkbox:checked');
        if (checkedEntries.length === 0) {
            e.preventDefault();
            alert('<?= _l('no_entries_selected'); ?>');
            return false;
        }
        
        // Add selected entry IDs to form
        checkedEntries.each(function() {
            $(this).clone().appendTo('#bulk-actions-form');
        });
        
        var action = $('select[name="bulk_action"]').val();
        if (action === 'delete') {
            if (!confirm('<?= _l('confirm_delete_time_entries'); ?>')) {
                e.preventDefault();
                return false;
            }
        } else if (action === 'approve') {
            if (!confirm('<?= _l('confirm_approve_time_entries'); ?>')) {
                e.preventDefault();
                return false;
            }
        } else if (action === 'reject') {
            if (!confirm('<?= _l('confirm_reject_time_entries'); ?>')) {
                e.preventDefault();
                return false;
            }
        }
    });
}

/**
 * Toggle bulk action button
 */
function toggleBulkActionButton() {
    var checkedCount = $('.entry-checkbox:checked').length;
    $('#bulk-action-btn').prop('disabled', checkedCount === 0);
}

/**
 * Initialize calendar view
 */
function initializeCalendar() {
    var calendarEl = document.getElementById('time-entries-calendar');
    
    if (!calendarEl) return;
    
    window.calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            $.ajax({
                url: admin_url + 'project_enhancement/time_tracking/get_calendar_events',
                type: 'GET',
                data: {
                    start: fetchInfo.startStr,
                    end: fetchInfo.endStr
                },
                success: function(response) {
                    if (response.success) {
                        successCallback(response.events);
                    } else {
                        failureCallback();
                    }
                },
                error: failureCallback
            });
        },
        eventClick: function(info) {
            window.location.href = admin_url + 'project_enhancement/time_tracking/view/' + info.event.id;
        },
        eventDidMount: function(info) {
            $(info.el).tooltip({
                title: info.event.extendedProps.description,
                placement: 'top'
            });
        }
    });
}

/**
 * Load project milestones
 */
function loadProjectMilestones(projectId) {
    var $milestoneSelect = $('select[name="milestone_id"]');
    
    if (!projectId) {
        $milestoneSelect.empty().append('<option value=""><?= _l('all_milestones'); ?></option>').selectpicker('refresh');
        return;
    }
    
    $.ajax({
        url: admin_url + 'project_enhancement/milestones/get_by_project/' + projectId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            $milestoneSelect.empty();
            $milestoneSelect.append('<option value=""><?= _l('all_milestones'); ?></option>');
            
            if (response.success && response.milestones) {
                response.milestones.forEach(function(milestone) {
                    $milestoneSelect.append('<option value="' + milestone.id + '">' + milestone.name + '</option>');
                });
            }
            
            $milestoneSelect.selectpicker('refresh');
        }
    });
}

/**
 * Quick approve entry
 */
function quickApprove(entryId) {
    if (!confirm('<?= _l('confirm_approve_time_entry'); ?>')) {
        return;
    }
    
    $.ajax({
        url: admin_url + 'project_enhancement/time_tracking/approve/' + entryId,
        type: 'POST',
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
 * Quick reject entry
 */
function quickReject(entryId) {
    var reason = prompt('<?= _l('rejection_reason'); ?>');
    if (!reason) {
        return;
    }
    
    $.ajax({
        url: admin_url + 'project_enhancement/time_tracking/reject/' + entryId,
        type: 'POST',
        data: { reason: reason },
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
</script>

<style>
.time-entry-card {
    border: 1px solid #e4e7ea;
    border-radius: 6px;
    margin-bottom: 15px;
    background: #fff;
    transition: all 0.3s ease;
}

.time-entry-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transform: translateY(-1px);
}

.entry-header {
    display: flex;
    align-items: center;
    padding: 15px 20px 10px;
    border-bottom: 1px solid #f0f0f0;
    background: #f8f9fa;
}

.entry-checkbox {
    margin-right: 15px;
}

.entry-date {
    font-weight: 600;
    color: #333;
    margin-right: 15px;
}

.entry-duration {
    font-size: 18px;
    font-weight: 600;
    color: #007bff;
    margin-right: auto;
}

.entry-status {
    margin-left: 15px;
}

.entry-content {
    padding: 15px 20px;
}

.entry-project {
    font-size: 16px;
    margin-bottom: 8px;
}

.entry-description {
    color: #666;
    margin-bottom: 10px;
    line-height: 1.4;
}

.entry-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    font-size: 12px;
    color: #999;
}

.entry-meta span {
    display: flex;
    align-items: center;
    gap: 5px;
}

.entry-actions {
    position: absolute;
    top: 15px;
    right: 20px;
}

.time-entry-card {
    position: relative;
}

#time-entries-calendar {
    height: 600px;
}

.dt-table {
    width: 100% !important;
}

.panel_s {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

@media (max-width: 768px) {
    .entry-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .entry-meta {
        flex-direction: column;
        gap: 5px;
    }
    
    .entry-actions {
        position: static;
        margin-top: 10px;
    }
}

/* Filter form responsive adjustments */
@media (max-width: 992px) {
    #time-entries-filter-form .col-md-2 {
        margin-bottom: 10px;
    }
}

/* Status colors */
.label-draft { background-color: #6c757d; }
.label-submitted { background-color: #ffc107; }
.label-approved { background-color: #28a745; }
.label-rejected { background-color: #dc3545; }

/* Calendar event styling */
.fc-event {
    cursor: pointer;
}

.fc-event:hover {
    opacity: 0.8;
}
</style>