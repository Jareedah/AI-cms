/**
 * Project Enhancement Dashboard JavaScript
 * Handles charts, real-time updates, and interactive features
 */

$(document).ready(function() {
    'use strict';

    // Initialize dashboard
    initializeDashboard();
    
    // Initialize charts
    initializeCharts();
    
    // Set up real-time updates
    setupRealTimeUpdates();
    
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();
});

/**
 * Initialize dashboard functionality
 */
function initializeDashboard() {
    // Auto-refresh dashboard every 5 minutes
    setInterval(function() {
        refreshDashboardStats();
    }, 300000); // 5 minutes
    
    // Handle quick action clicks
    $('.quick-actions .list-group-item').on('click', function(e) {
        var href = $(this).attr('href');
        if (href && href !== '#') {
            window.location.href = href;
        }
    });
    
    // Handle alert dismissals
    $('.alert-dismissible .close').on('click', function() {
        $(this).parent().fadeOut();
    });
}

/**
 * Initialize all dashboard charts
 */
function initializeCharts() {
    if (typeof chartData === 'undefined') {
        console.warn('Chart data not available');
        return;
    }
    
    // Project Progress Chart
    initProjectProgressChart();
    
    // Time Tracking Chart
    initTimeTrackingChart();
    
    // Budget Chart
    initBudgetChart();
    
    // Resource Utilization Chart
    initResourceChart();
}

/**
 * Initialize Project Progress Chart
 */
function initProjectProgressChart() {
    var ctx = document.getElementById('projectProgressChart');
    if (!ctx) return;
    
    var data = chartData.projectProgress || {
        labels: ['Planning', 'In Progress', 'Testing', 'Completed'],
        datasets: [{
            data: [0, 0, 0, 0],
            backgroundColor: ['#f39c12', '#3498db', '#e67e22', '#27ae60']
        }]
    };
    
    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            legend: {
                position: 'bottom'
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.labels[tooltipItem.index];
                        var value = data.datasets[0].data[tooltipItem.index];
                        var total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                        var percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' (' + percentage + '%)';
                    }
                }
            }
        }
    });
}

/**
 * Initialize Time Tracking Chart
 */
function initTimeTrackingChart() {
    var ctx = document.getElementById('timeTrackingChart');
    if (!ctx) return;
    
    var data = chartData.timeTracking || {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Hours Logged',
            data: [0, 0, 0, 0, 0, 0, 0],
            borderColor: '#27ae60',
            backgroundColor: 'rgba(39, 174, 96, 0.1)',
            fill: true
        }]
    };
    
    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {
                            return value + 'h';
                        }
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        return data.datasets[tooltipItem.datasetIndex].label + ': ' + 
                               tooltipItem.yLabel + ' hours';
                    }
                }
            }
        }
    });
}

/**
 * Initialize Budget Chart
 */
function initBudgetChart() {
    var ctx = document.getElementById('budgetChart');
    if (!ctx) return;
    
    var data = chartData.budget || {
        labels: ['Allocated', 'Spent', 'Remaining'],
        datasets: [{
            data: [0, 0, 0],
            backgroundColor: ['#3498db', '#e74c3c', '#95a5a6']
        }]
    };
    
    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        callback: function(value) {
                            return '$' + value.toLocaleString();
                        }
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.labels[tooltipItem.index];
                        var value = data.datasets[0].data[tooltipItem.index];
                        return label + ': $' + value.toLocaleString();
                    }
                }
            }
        }
    });
}

/**
 * Initialize Resource Utilization Chart
 */
function initResourceChart() {
    var ctx = document.getElementById('resourceChart');
    if (!ctx) return;
    
    var data = chartData.resources || {
        labels: [],
        datasets: [{
            label: 'Utilization %',
            data: [],
            backgroundColor: 'rgba(52, 152, 219, 0.8)',
            borderColor: '#3498db',
            borderWidth: 1
        }]
    };
    
    new Chart(ctx, {
        type: 'horizontalBar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                xAxes: [{
                    ticks: {
                        beginAtZero: true,
                        max: 100,
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }]
            },
            tooltips: {
                callbacks: {
                    label: function(tooltipItem, data) {
                        var label = data.labels[tooltipItem.index];
                        var value = data.datasets[0].data[tooltipItem.index];
                        return label + ': ' + value + '%';
                    }
                }
            }
        }
    });
}

/**
 * Set up real-time updates
 */
function setupRealTimeUpdates() {
    // Update statistics every 2 minutes
    setInterval(function() {
        updateStatistics();
    }, 120000); // 2 minutes
    
    // Update activity feed every minute
    setInterval(function() {
        updateActivityFeed();
    }, 60000); // 1 minute
}

/**
 * Refresh dashboard statistics
 */
function refreshDashboardStats() {
    $.ajax({
        url: admin_url + 'project_enhancement/dashboard_stats',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                updateStatisticsDisplay(response.stats);
            }
        },
        error: function() {
            console.error('Failed to refresh dashboard statistics');
        }
    });
}

/**
 * Update statistics display
 */
function updateStatisticsDisplay(stats) {
    // Update statistic cards
    $('.widget-drilldown').each(function() {
        var $widget = $(this);
        var statKey = $widget.data('stat-key');
        
        if (stats[statKey] !== undefined) {
            $widget.find('h3').text(stats[statKey]);
        }
    });
}

/**
 * Update statistics with animation
 */
function updateStatistics() {
    $.ajax({
        url: admin_url + 'project_enhancement/get_statistics',
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                animateStatistics(response.stats);
            }
        }
    });
}

/**
 * Animate statistics update
 */
function animateStatistics(stats) {
    Object.keys(stats).forEach(function(key) {
        var $element = $('[data-stat="' + key + '"]');
        if ($element.length) {
            var currentValue = parseInt($element.text()) || 0;
            var newValue = stats[key];
            
            // Animate the number change
            $({ counter: currentValue }).animate({ counter: newValue }, {
                duration: 1000,
                step: function() {
                    $element.text(Math.ceil(this.counter));
                }
            });
        }
    });
}

/**
 * Update activity feed
 */
function updateActivityFeed() {
    $.ajax({
        url: admin_url + 'project_enhancement/get_recent_activities',
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
    var $activityFeed = $('.activity-feed');
    var currentActivities = $activityFeed.find('.activity-item').length;
    
    // Add new activities to the top
    activities.forEach(function(activity) {
        var activityHtml = createActivityItem(activity);
        $activityFeed.prepend(activityHtml);
    });
    
    // Remove old activities if we have too many
    var maxActivities = 10;
    $activityFeed.find('.activity-item').slice(maxActivities).remove();
    
    // Animate new items
    $activityFeed.find('.activity-item').slice(0, activities.length).hide().fadeIn();
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
                <div class="activity-text">
                    ${activity.description}
                </div>
                <div class="activity-time text-muted">
                    <small>${activity.time_ago}</small>
                </div>
            </div>
        </div>
    `;
}

/**
 * Handle chart data updates
 */
function updateChartData(chartType, newData) {
    var chart = Chart.instances[chartType];
    if (chart) {
        chart.data = newData;
        chart.update();
    }
}

/**
 * Show notification
 */
function showNotification(message, type = 'info') {
    var alertClass = 'alert-' + type;
    var iconClass = type === 'success' ? 'fa-check-circle' : 
                   type === 'danger' ? 'fa-exclamation-triangle' : 
                   type === 'warning' ? 'fa-exclamation-circle' : 'fa-info-circle';
    
    var notification = `
        <div class="alert ${alertClass} alert-dismissible fade in" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <i class="fa ${iconClass}"></i> ${message}
        </div>
    `;
    
    $('#notifications-container').append(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(function() {
        $('.alert').last().fadeOut(function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Format number with commas
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

/**
 * Format currency
 */
function formatCurrency(amount, currency = '$') {
    return currency + formatNumber(amount.toFixed(2));
}

/**
 * Format duration
 */
function formatDuration(minutes) {
    var hours = Math.floor(minutes / 60);
    var mins = minutes % 60;
    return hours + 'h ' + mins + 'm';
}

/**
 * Export dashboard data
 */
function exportDashboard(format = 'pdf') {
    var exportUrl = admin_url + 'project_enhancement/export_dashboard/' + format;
    window.open(exportUrl, '_blank');
}

/**
 * Print dashboard
 */
function printDashboard() {
    window.print();
}

/**
 * Refresh specific widget
 */
function refreshWidget(widgetId) {
    var $widget = $('#' + widgetId);
    $widget.addClass('loading');
    
    $.ajax({
        url: admin_url + 'project_enhancement/refresh_widget/' + widgetId,
        type: 'GET',
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                $widget.html(response.html);
            }
        },
        complete: function() {
            $widget.removeClass('loading');
        }
    });
}

// Global functions for external access
window.ProjectEnhancementDashboard = {
    refreshStats: refreshDashboardStats,
    updateChart: updateChartData,
    showNotification: showNotification,
    exportDashboard: exportDashboard,
    printDashboard: printDashboard,
    refreshWidget: refreshWidget
};