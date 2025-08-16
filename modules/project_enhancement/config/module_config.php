<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Project Enhancement Module Configuration
 */

// Module version and compatibility
$config['project_enhancement_version'] = '1.0.0';
$config['project_enhancement_min_perfex_version'] = '2.3.0';

// Default settings
$config['project_enhancement_defaults'] = [
    'auto_milestone_creation' => true,
    'time_tracking_enabled' => true,
    'time_approval_required' => true,
    'budget_tracking_enabled' => true,
    'resource_management_enabled' => true,
    'email_notifications' => true,
    'client_portal_enabled' => true,
    'dashboard_widgets_enabled' => true,
    'api_enabled' => false,
    'cron_enabled' => true,
    'default_hourly_rate' => 50.00,
    'currency' => 'USD',
    'timezone' => 'UTC',
    'working_hours_per_day' => 8,
    'working_days_per_week' => 5,
];

// Default milestone templates
$config['default_milestone_templates'] = [
    'planning' => [
        'name' => 'Project Planning',
        'description' => 'Initial project planning and requirements gathering',
        'order' => 1,
        'priority' => 'high',
    ],
    'development' => [
        'name' => 'Development Phase',
        'description' => 'Main development and implementation work',
        'order' => 2,
        'priority' => 'high',
    ],
    'testing' => [
        'name' => 'Testing & QA',
        'description' => 'Quality assurance and testing phase',
        'order' => 3,
        'priority' => 'medium',
    ],
    'deployment' => [
        'name' => 'Deployment',
        'description' => 'Final deployment and go-live',
        'order' => 4,
        'priority' => 'critical',
    ],
];

// Time tracking categories with colors
$config['time_categories'] = [
    'development' => ['color' => '#3498db', 'billable' => true],
    'testing' => ['color' => '#e74c3c', 'billable' => true],
    'design' => ['color' => '#9b59b6', 'billable' => true],
    'meetings' => ['color' => '#f39c12', 'billable' => false],
    'documentation' => ['color' => '#2ecc71', 'billable' => true],
    'research' => ['color' => '#34495e', 'billable' => false],
    'support' => ['color' => '#16a085', 'billable' => true],
];

// Budget categories
$config['budget_categories'] = [
    'development',
    'design',
    'testing',
    'infrastructure',
    'third_party_services',
    'marketing',
    'project_management',
    'miscellaneous',
];

// Dashboard widget configuration
$config['dashboard_widgets'] = [
    'project_progress' => [
        'container' => 'left-4',
        'position' => 1,
        'enabled' => true,
    ],
    'time_tracking_summary' => [
        'container' => 'right-4',
        'position' => 1,
        'enabled' => true,
    ],
    'budget_status' => [
        'container' => 'left-4',
        'position' => 2,
        'enabled' => true,
    ],
    'upcoming_milestones' => [
        'container' => 'right-4',
        'position' => 2,
        'enabled' => true,
    ],
    'resource_utilization' => [
        'container' => 'left-4',
        'position' => 3,
        'enabled' => true,
    ],
    'project_health' => [
        'container' => 'right-4',
        'position' => 3,
        'enabled' => true,
    ],
];

// Email notification settings
$config['email_notifications'] = [
    'milestone_created' => true,
    'milestone_due_reminder' => true,
    'milestone_completed' => true,
    'time_entry_approval_required' => true,
    'time_entry_approved' => true,
    'time_entry_rejected' => true,
    'budget_threshold_exceeded' => true,
    'resource_allocation_changed' => true,
];

// API configuration
$config['api_settings'] = [
    'rate_limit' => 100, // requests per hour
    'jwt_expiry' => 3600, // 1 hour
    'enabled_endpoints' => [
        'milestones',
        'time_entries',
        'resources',
        'budgets',
    ],
];

// Security settings
$config['security'] = [
    'csrf_exclude_uris' => [
        'project_enhancement/api/*',
        'project_enhancement/webhooks/*',
    ],
    'allowed_file_types' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
    'max_file_size' => 10485760, // 10MB
];

// Performance settings
$config['performance'] = [
    'cache_enabled' => true,
    'cache_ttl' => 3600, // 1 hour
    'pagination_limit' => 25,
    'max_records_per_export' => 10000,
];

// Feature flags
$config['feature_flags'] = [
    'gantt_chart' => true,
    'advanced_reporting' => true,
    'mobile_app_support' => true,
    'webhook_integration' => false,
    'ai_suggestions' => false,
];