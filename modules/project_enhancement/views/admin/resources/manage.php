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
                                    <i class="fa fa-users"></i> 
                                    <?= _l('resource_management'); ?>
                                </h4>
                            </div>
                            <div class="col-md-6 text-right">
                                <div class="btn-group">
                                    <a href="<?= admin_url('project_enhancement/resources/create'); ?>" class="btn btn-info">
                                        <i class="fa fa-plus"></i> <?= _l('allocate_resource'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/resources/skills'); ?>" class="btn btn-default">
                                        <i class="fa fa-cogs"></i> <?= _l('manage_skills'); ?>
                                    </a>
                                    <a href="<?= admin_url('project_enhancement/resources/availability'); ?>" class="btn btn-default">
                                        <i class="fa fa-calendar-check"></i> <?= _l('availability'); ?>
                                    </a>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                                            <i class="fa fa-download"></i> <?= _l('export'); ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a href="<?= admin_url('project_enhancement/resources/export/csv'); ?>">CSV</a></li>
                                            <li><a href="<?= admin_url('project_enhancement/resources/export/excel'); ?>">Excel</a></li>
                                        </ul>
                                    </div>
                                    <a href="<?= admin_url('project_enhancement/resources/reports'); ?>" class="btn btn-default">
                                        <i class="fa fa-chart-bar"></i> <?= _l('reports'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resource Overview Cards -->
        <div class="row">
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-info"><?= $resource_stats['total_allocations'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('total_allocations'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-success"><?= $resource_stats['active_staff'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('active_staff'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-warning"><?= number_format($resource_stats['avg_utilization'] ?? 0, 1); ?>%</h3>
                        <p class="text-muted"><?= _l('avg_utilization'); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="panel_s">
                    <div class="panel-body text-center">
                        <h3 class="text-danger"><?= $resource_stats['overallocated'] ?? 0; ?></h3>
                        <p class="text-muted"><?= _l('overallocated'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and View Controls -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-8">
                                <?= form_open(admin_url('project_enhancement/resources'), ['method' => 'GET', 'id' => 'resource-filter-form']); ?>
                                <div class="row">
                                    <div class="col-md-3">
                                        <?= render_select('project_id', $projects, ['id', 'name'], 'project', $current_filters['project_id'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_projects')]); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <?= render_select('staff_id', $staff_members, ['staffid', ['firstname', 'lastname']], 'staff_member', $current_filters['staff_id'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_staff')]); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <?= render_select('allocation_type', [
                                            ['id' => 'full_time', 'name' => _l('full_time')],
                                            ['id' => 'part_time', 'name' => _l('part_time')],
                                            ['id' => 'contractor', 'name' => _l('contractor')]
                                        ], ['id', 'name'], 'allocation_type', $current_filters['allocation_type'], ['data-width' => '100%', 'data-none-selected-text' => _l('all_types')]); ?>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-info btn-block">
                                            <i class="fa fa-search"></i> <?= _l('filter'); ?>
                                        </button>
                                    </div>
                                </div>
                                <?= form_close(); ?>
                            </div>
                            <div class="col-md-4 text-right">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-info" onclick="toggleView('grid')">
                                        <i class="fa fa-th"></i> <?= _l('grid_view'); ?>
                                    </button>
                                    <button type="button" class="btn btn-default" onclick="toggleView('table')">
                                        <i class="fa fa-table"></i> <?= _l('table_view'); ?>
                                    </button>
                                    <button type="button" class="btn btn-default" onclick="toggleView('timeline')">
                                        <i class="fa fa-calendar"></i> <?= _l('timeline_view'); ?>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div id="grid-view">
            <div class="row">
                <?php if (!empty($resource_allocations)): ?>
                    <?php foreach ($resource_allocations as $allocation): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="resource-card">
                                <div class="resource-header">
                                    <div class="resource-avatar">
                                        <img src="<?= staff_profile_image_url($allocation['staff_id']); ?>" 
                                             alt="<?= $allocation['staff_firstname'] . ' ' . $allocation['staff_lastname']; ?>"
                                             class="img-circle">
                                    </div>
                                    <div class="resource-info">
                                        <h5 class="resource-name">
                                            <a href="<?= admin_url('staff/profile/' . $allocation['staff_id']); ?>">
                                                <?= $allocation['staff_firstname'] . ' ' . $allocation['staff_lastname']; ?>
                                            </a>
                                        </h5>
                                        <p class="resource-role"><?= $allocation['staff_role'] ?? _l('staff_member'); ?></p>
                                    </div>
                                    <div class="resource-status">
                                        <span class="label label-<?= resource_status_color($allocation['utilization_percentage']); ?>">
                                            <?= number_format($allocation['utilization_percentage'], 1); ?>%
                                        </span>
                                    </div>
                                </div>

                                <div class="resource-content">
                                    <div class="resource-project">
                                        <strong><?= _l('project'); ?>:</strong>
                                        <a href="<?= admin_url('projects/view/' . $allocation['project_id']); ?>">
                                            <?= $allocation['project_name']; ?>
                                        </a>
                                    </div>

                                    <div class="resource-allocation">
                                        <div class="allocation-info">
                                            <span class="allocation-label"><?= _l('allocation'); ?>:</span>
                                            <span class="allocation-value"><?= $allocation['allocation_percentage']; ?>%</span>
                                        </div>
                                        <div class="progress progress-sm">
                                            <div class="progress-bar progress-bar-<?= allocation_progress_color($allocation['allocation_percentage']); ?>" 
                                                 style="width: <?= $allocation['allocation_percentage']; ?>%">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="resource-period">
                                        <i class="fa fa-calendar"></i>
                                        <?= _d($allocation['start_date']); ?> - 
                                        <?= $allocation['end_date'] ? _d($allocation['end_date']) : _l('ongoing'); ?>
                                    </div>

                                    <?php if (!empty($allocation['skills'])): ?>
                                        <div class="resource-skills">
                                            <strong><?= _l('skills'); ?>:</strong>
                                            <div class="skills-list">
                                                <?php foreach (explode(',', $allocation['skills']) as $skill): ?>
                                                    <span class="skill-tag"><?= trim($skill); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>

                                    <div class="resource-availability">
                                        <div class="availability-indicator">
                                            <span class="availability-dot availability-<?= get_availability_status($allocation['current_availability']); ?>"></span>
                                            <span><?= _l('availability'); ?>: <?= $allocation['current_availability']; ?>%</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="resource-actions">
                                    <a href="<?= admin_url('project_enhancement/resources/view/' . $allocation['id']); ?>" 
                                       class="btn btn-default btn-xs">
                                        <i class="fa fa-eye"></i> <?= _l('view'); ?>
                                    </a>
                                    <?php if (staff_can('edit', 'project_enhancement')): ?>
                                        <a href="<?= admin_url('project_enhancement/resources/edit/' . $allocation['id']); ?>" 
                                           class="btn btn-info btn-xs">
                                            <i class="fa fa-edit"></i> <?= _l('edit'); ?>
                                        </a>
                                    <?php endif; ?>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                            <i class="fa fa-cog"></i> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="<?= admin_url('project_enhancement/resources/optimize/' . $allocation['project_id']); ?>">
                                                <i class="fa fa-magic"></i> <?= _l('optimize_allocation'); ?>
                                            </a></li>
                                            <li><a href="<?= admin_url('project_enhancement/resources/availability/' . $allocation['staff_id']); ?>">
                                                <i class="fa fa-calendar-check"></i> <?= _l('manage_availability'); ?>
                                            </a></li>
                                            <li><a href="<?= admin_url('project_enhancement/resources/skills/' . $allocation['staff_id']); ?>">
                                                <i class="fa fa-cogs"></i> <?= _l('manage_skills'); ?>
                                            </a></li>
                                            <?php if (staff_can('delete', 'project_enhancement')): ?>
                                                <li class="divider"></li>
                                                <li><a href="<?= admin_url('project_enhancement/resources/delete/' . $allocation['id']); ?>" 
                                                       class="text-danger _delete">
                                                    <i class="fa fa-trash"></i> <?= _l('remove_allocation'); ?>
                                                </a></li>
                                            <?php endif; ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-md-12">
                        <div class="text-center">
                            <h4 class="text-muted"><?= _l('no_resource_allocations'); ?></h4>
                            <p class="text-muted"><?= _l('create_first_allocation'); ?></p>
                            <a href="<?= admin_url('project_enhancement/resources/create'); ?>" class="btn btn-info">
                                <i class="fa fa-plus"></i> <?= _l('allocate_resource'); ?>
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Table View -->
        <div id="table-view" style="display: none;">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-striped dt-table" id="resources-table">
                                    <thead>
                                        <tr>
                                            <th><?= _l('staff_member'); ?></th>
                                            <th><?= _l('project'); ?></th>
                                            <th><?= _l('allocation'); ?></th>
                                            <th><?= _l('utilization'); ?></th>
                                            <th><?= _l('period'); ?></th>
                                            <th><?= _l('availability'); ?></th>
                                            <th><?= _l('skills'); ?></th>
                                            <th><?= _l('actions'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($resource_allocations)): ?>
                                            <?php foreach ($resource_allocations as $allocation): ?>
                                                <tr>
                                                    <td>
                                                        <div class="staff-info">
                                                            <img src="<?= staff_profile_image_url($allocation['staff_id']); ?>" 
                                                                 alt="<?= $allocation['staff_firstname'] . ' ' . $allocation['staff_lastname']; ?>"
                                                                 class="img-circle staff-avatar-sm">
                                                            <div class="staff-details">
                                                                <a href="<?= admin_url('staff/profile/' . $allocation['staff_id']); ?>">
                                                                    <?= $allocation['staff_firstname'] . ' ' . $allocation['staff_lastname']; ?>
                                                                </a>
                                                                <br><small class="text-muted"><?= $allocation['staff_role'] ?? _l('staff_member'); ?></small>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <a href="<?= admin_url('projects/view/' . $allocation['project_id']); ?>">
                                                            <?= $allocation['project_name']; ?>
                                                        </a>
                                                    </td>
                                                    <td>
                                                        <div class="allocation-display">
                                                            <span class="allocation-percentage"><?= $allocation['allocation_percentage']; ?>%</span>
                                                            <div class="progress progress-xs">
                                                                <div class="progress-bar progress-bar-<?= allocation_progress_color($allocation['allocation_percentage']); ?>" 
                                                                     style="width: <?= $allocation['allocation_percentage']; ?>%">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <span class="label label-<?= resource_status_color($allocation['utilization_percentage']); ?>">
                                                            <?= number_format($allocation['utilization_percentage'], 1); ?>%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <small>
                                                            <?= _d($allocation['start_date']); ?><br>
                                                            <?= $allocation['end_date'] ? _d($allocation['end_date']) : _l('ongoing'); ?>
                                                        </small>
                                                    </td>
                                                    <td>
                                                        <div class="availability-indicator">
                                                            <span class="availability-dot availability-<?= get_availability_status($allocation['current_availability']); ?>"></span>
                                                            <?= $allocation['current_availability']; ?>%
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <?php if (!empty($allocation['skills'])): ?>
                                                            <div class="skills-compact">
                                                                <?php 
                                                                $skills = explode(',', $allocation['skills']);
                                                                $displaySkills = array_slice($skills, 0, 2);
                                                                ?>
                                                                <?php foreach ($displaySkills as $skill): ?>
                                                                    <span class="skill-tag-sm"><?= trim($skill); ?></span>
                                                                <?php endforeach; ?>
                                                                <?php if (count($skills) > 2): ?>
                                                                    <span class="text-muted">+<?= count($skills) - 2; ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <span class="text-muted">-</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <div class="btn-group">
                                                            <a href="<?= admin_url('project_enhancement/resources/view/' . $allocation['id']); ?>" 
                                                               class="btn btn-default btn-xs" data-toggle="tooltip" title="<?= _l('view'); ?>">
                                                                <i class="fa fa-eye"></i>
                                                            </a>
                                                            <?php if (staff_can('edit', 'project_enhancement')): ?>
                                                                <a href="<?= admin_url('project_enhancement/resources/edit/' . $allocation['id']); ?>" 
                                                                   class="btn btn-info btn-xs" data-toggle="tooltip" title="<?= _l('edit'); ?>">
                                                                    <i class="fa fa-edit"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                            <div class="btn-group">
                                                                <button type="button" class="btn btn-default btn-xs dropdown-toggle" data-toggle="dropdown">
                                                                    <i class="fa fa-cog"></i> <span class="caret"></span>
                                                                </button>
                                                                <ul class="dropdown-menu dropdown-menu-right">
                                                                    <li><a href="<?= admin_url('project_enhancement/resources/optimize/' . $allocation['project_id']); ?>">
                                                                        <i class="fa fa-magic"></i> <?= _l('optimize'); ?>
                                                                    </a></li>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline View -->
        <div id="timeline-view" style="display: none;">
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div id="resource-timeline"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Resource Optimization Panel -->
        <?php if (!empty($optimization_suggestions)): ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s panel-warning">
                        <div class="panel-header">
                            <h5 class="panel-title">
                                <i class="fa fa-lightbulb"></i> <?= _l('optimization_suggestions'); ?>
                            </h5>
                        </div>
                        <div class="panel-body">
                            <div class="optimization-suggestions">
                                <?php foreach ($optimization_suggestions as $suggestion): ?>
                                    <div class="suggestion-item">
                                        <div class="suggestion-icon">
                                            <i class="fa <?= $suggestion['icon']; ?> text-<?= $suggestion['type']; ?>"></i>
                                        </div>
                                        <div class="suggestion-content">
                                            <h6><?= $suggestion['title']; ?></h6>
                                            <p><?= $suggestion['description']; ?></p>
                                            <?php if (!empty($suggestion['action_url'])): ?>
                                                <a href="<?= $suggestion['action_url']; ?>" class="btn btn-sm btn-<?= $suggestion['type']; ?>">
                                                    <?= $suggestion['action_text']; ?>
                                                </a>
                                            <?php endif; ?>
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
    
    // Initialize DataTable
    $('#resources-table').DataTable({
        "order": [[0, "asc"]],
        "columnDefs": [
            { "orderable": false, "targets": [7] }
        ]
    });
    
    // Initialize view management
    initializeViewToggle();
    
    // Initialize timeline if available
    if (typeof vis !== 'undefined') {
        initializeTimeline();
    }
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
    
    // Auto-submit filter form
    $('#resource-filter-form select').on('change', function() {
        $('#resource-filter-form').submit();
    });
});

/**
 * Initialize view toggle functionality
 */
function initializeViewToggle() {
    var viewPreference = localStorage.getItem('resource_view_preference') || 'grid';
    toggleView(viewPreference);
}

/**
 * Toggle between different views
 */
function toggleView(view) {
    $('#grid-view, #table-view, #timeline-view').hide();
    
    switch(view) {
        case 'table':
            $('#table-view').show();
            break;
        case 'timeline':
            $('#timeline-view').show();
            if (typeof timeline !== 'undefined') {
                timeline.redraw();
            }
            break;
        default:
            $('#grid-view').show();
            view = 'grid';
    }
    
    localStorage.setItem('resource_view_preference', view);
    
    // Update button states
    $('.btn-group button').removeClass('btn-info').addClass('btn-default');
    $('button[onclick="toggleView(\'' + view + '\')"]').removeClass('btn-default').addClass('btn-info');
}

/**
 * Initialize timeline view
 */
function initializeTimeline() {
    var container = document.getElementById('resource-timeline');
    if (!container) return;
    
    // Prepare timeline data
    var items = new vis.DataSet();
    var groups = new vis.DataSet();
    
    // Add staff groups
    <?php if (!empty($staff_members)): ?>
        <?php foreach ($staff_members as $staff): ?>
            groups.add({
                id: <?= $staff['staffid']; ?>,
                content: '<?= $staff['firstname'] . ' ' . $staff['lastname']; ?>'
            });
        <?php endforeach; ?>
    <?php endif; ?>
    
    // Add allocation items
    <?php if (!empty($resource_allocations)): ?>
        <?php foreach ($resource_allocations as $allocation): ?>
            items.add({
                id: <?= $allocation['id']; ?>,
                group: <?= $allocation['staff_id']; ?>,
                content: '<?= $allocation['project_name']; ?> (<?= $allocation['allocation_percentage']; ?>%)',
                start: '<?= $allocation['start_date']; ?>',
                end: '<?= $allocation['end_date'] ?: date('Y-m-d', strtotime('+1 year')); ?>',
                className: 'allocation-<?= get_allocation_class($allocation['allocation_percentage']); ?>'
            });
        <?php endforeach; ?>
    <?php endif; ?>
    
    var options = {
        stack: true,
        margin: {
            item: 10,
            axis: 5
        },
        orientation: 'top'
    };
    
    window.timeline = new vis.Timeline(container, items, groups, options);
    
    // Handle item selection
    timeline.on('select', function(selection) {
        if (selection.items.length > 0) {
            var allocationId = selection.items[0];
            window.location.href = admin_url + 'project_enhancement/resources/view/' + allocationId;
        }
    });
}

/**
 * Quick optimize allocation
 */
function quickOptimize(projectId) {
    $.ajax({
        url: admin_url + 'project_enhancement/resources/quick_optimize/' + projectId,
        type: 'POST',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                if (response.suggestions && response.suggestions.length > 0) {
                    showOptimizationModal(response.suggestions);
                } else {
                    alert('<?= _l('allocation_already_optimized'); ?>');
                }
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
 * Show optimization suggestions modal
 */
function showOptimizationModal(suggestions) {
    var modal = $('<div class="modal fade" tabindex="-1" role="dialog">');
    var modalContent = `
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title"><?= _l('optimization_suggestions'); ?></h4>
                </div>
                <div class="modal-body">
                    <div class="optimization-suggestions">
    `;
    
    suggestions.forEach(function(suggestion) {
        modalContent += `
            <div class="suggestion-item">
                <div class="suggestion-icon">
                    <i class="fa ${suggestion.icon} text-${suggestion.type}"></i>
                </div>
                <div class="suggestion-content">
                    <h6>${suggestion.title}</h6>
                    <p>${suggestion.description}</p>
                </div>
            </div>
        `;
    });
    
    modalContent += `
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal"><?= _l('close'); ?></button>
                </div>
            </div>
        </div>
    `;
    
    modal.html(modalContent);
    modal.modal('show');
    
    modal.on('hidden.bs.modal', function() {
        modal.remove();
    });
}
</script>

<style>
.resource-card {
    border: 1px solid #e4e7ea;
    border-radius: 8px;
    background: #fff;
    margin-bottom: 20px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.resource-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.resource-header {
    display: flex;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border-bottom: 1px solid #e4e7ea;
}

.resource-avatar {
    margin-right: 15px;
}

.resource-avatar img {
    width: 50px;
    height: 50px;
    object-fit: cover;
}

.resource-info {
    flex: 1;
}

.resource-name {
    margin: 0 0 5px 0;
    font-weight: 600;
}

.resource-name a {
    color: #333;
    text-decoration: none;
}

.resource-role {
    margin: 0;
    color: #666;
    font-size: 12px;
}

.resource-status {
    text-align: right;
}

.resource-content {
    padding: 20px;
}

.resource-project {
    margin-bottom: 15px;
    font-size: 14px;
}

.resource-allocation {
    margin-bottom: 15px;
}

.allocation-info {
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 12px;
}

.allocation-value {
    font-weight: 600;
    color: #007bff;
}

.progress-sm {
    height: 8px;
}

.resource-period {
    margin-bottom: 15px;
    font-size: 12px;
    color: #666;
}

.resource-skills {
    margin-bottom: 15px;
}

.skills-list {
    margin-top: 5px;
}

.skill-tag {
    display: inline-block;
    background: #007bff;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 11px;
    margin: 2px 3px 2px 0;
}

.skill-tag-sm {
    display: inline-block;
    background: #6c757d;
    color: white;
    padding: 1px 6px;
    border-radius: 8px;
    font-size: 10px;
    margin: 1px 2px 1px 0;
}

.resource-availability {
    margin-bottom: 15px;
}

.availability-indicator {
    display: flex;
    align-items: center;
    font-size: 12px;
}

.availability-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    margin-right: 8px;
}

.availability-high { background-color: #28a745; }
.availability-medium { background-color: #ffc107; }
.availability-low { background-color: #dc3545; }

.resource-actions {
    padding: 15px 20px;
    background: #f8f9fa;
    border-top: 1px solid #e4e7ea;
    text-align: right;
}

.staff-info {
    display: flex;
    align-items: center;
}

.staff-avatar-sm {
    width: 32px;
    height: 32px;
    margin-right: 10px;
    object-fit: cover;
}

.staff-details {
    flex: 1;
}

.allocation-display {
    text-align: center;
}

.allocation-percentage {
    font-weight: 600;
    color: #007bff;
}

.progress-xs {
    height: 4px;
    margin-top: 3px;
}

.skills-compact {
    max-width: 120px;
}

.optimization-suggestions {
    max-height: 400px;
    overflow-y: auto;
}

.suggestion-item {
    display: flex;
    align-items: flex-start;
    padding: 15px;
    border: 1px solid #e4e7ea;
    border-radius: 6px;
    margin-bottom: 10px;
    background: #fff;
}

.suggestion-icon {
    margin-right: 15px;
    font-size: 24px;
    width: 40px;
    text-align: center;
}

.suggestion-content {
    flex: 1;
}

.suggestion-content h6 {
    margin: 0 0 8px 0;
    font-weight: 600;
}

.suggestion-content p {
    margin: 0 0 10px 0;
    color: #666;
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

/* Timeline styles */
#resource-timeline {
    height: 400px;
    border: 1px solid #e4e7ea;
    border-radius: 4px;
}

.allocation-high {
    background-color: #dc3545 !important;
    border-color: #dc3545 !important;
}

.allocation-medium {
    background-color: #ffc107 !important;
    border-color: #ffc107 !important;
}

.allocation-low {
    background-color: #28a745 !important;
    border-color: #28a745 !important;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .resource-header {
        flex-direction: column;
        text-align: center;
    }
    
    .resource-avatar {
        margin-right: 0;
        margin-bottom: 10px;
    }
    
    .resource-status {
        text-align: center;
        margin-top: 10px;
    }
    
    .staff-info {
        flex-direction: column;
        text-align: center;
    }
    
    .staff-avatar-sm {
        margin-right: 0;
        margin-bottom: 5px;
    }
}

/* Status color helpers */
.label-success { background-color: #28a745; }
.label-warning { background-color: #ffc107; }
.label-danger { background-color: #dc3545; }
.label-info { background-color: #17a2b8; }

.progress-bar-success { background-color: #28a745; }
.progress-bar-warning { background-color: #ffc107; }
.progress-bar-danger { background-color: #dc3545; }
.progress-bar-info { background-color: #17a2b8; }
</style>