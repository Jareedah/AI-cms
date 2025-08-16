<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Project Enhancement Module Installation Script
 * Creates all required database tables and default options
 */

$CI = &get_instance();

// Load database forge for table creation
$CI->load->dbforge();

try {
    // Start transaction for atomic installation
    $CI->db->trans_start();
    
    // 1. Create project_milestones table
    if (!$CI->db->table_exists(db_prefix() . 'project_milestones')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'project_milestones` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL,
            `name` varchar(255) NOT NULL,
            `description` text,
            `start_date` date NOT NULL,
            `due_date` date DEFAULT NULL,
            `progress_percentage` decimal(5,2) DEFAULT 0.00,
            `status` enum("not_started","in_progress","completed","on_hold","cancelled") DEFAULT "not_started",
            `priority` enum("low","medium","high","critical") DEFAULT "medium",
            `assigned_to` int(11) DEFAULT NULL,
            `estimated_hours` decimal(8,2) DEFAULT NULL,
            `actual_hours` decimal(8,2) DEFAULT 0.00,
            `order_number` int(11) DEFAULT 0,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `assigned_to` (`assigned_to`),
            KEY `status` (`status`),
            KEY `due_date` (`due_date`),
            KEY `priority` (`priority`),
            KEY `progress_percentage` (`progress_percentage`),
            FOREIGN KEY (`project_id`) REFERENCES `' . db_prefix() . 'projects`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`assigned_to`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // 2. Create milestone_dependencies table
    if (!$CI->db->table_exists(db_prefix() . 'milestone_dependencies')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'milestone_dependencies` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `milestone_id` int(11) NOT NULL,
            `depends_on_milestone_id` int(11) NOT NULL,
            `dependency_type` enum("finish_to_start","start_to_start","finish_to_finish","start_to_finish") DEFAULT "finish_to_start",
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `milestone_dependency_unique` (`milestone_id`, `depends_on_milestone_id`),
            KEY `milestone_id` (`milestone_id`),
            KEY `depends_on_milestone_id` (`depends_on_milestone_id`),
            FOREIGN KEY (`milestone_id`) REFERENCES `' . db_prefix() . 'project_milestones`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`depends_on_milestone_id`) REFERENCES `' . db_prefix() . 'project_milestones`(`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // 3. Create milestone_approvals table
    if (!$CI->db->table_exists(db_prefix() . 'milestone_approvals')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'milestone_approvals` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `milestone_id` int(11) NOT NULL,
            `approver_staff_id` int(11) NOT NULL,
            `approval_status` enum("pending","approved","rejected") DEFAULT "pending",
            `approval_date` datetime DEFAULT NULL,
            `comments` text,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `milestone_id` (`milestone_id`),
            KEY `approver_staff_id` (`approver_staff_id`),
            KEY `approval_status` (`approval_status`),
            FOREIGN KEY (`milestone_id`) REFERENCES `' . db_prefix() . 'project_milestones`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`approver_staff_id`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // 4. Create time_entries table
    if (!$CI->db->table_exists(db_prefix() . 'time_entries')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'time_entries` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL,
            `task_id` int(11) DEFAULT NULL,
            `milestone_id` int(11) DEFAULT NULL,
            `staff_id` int(11) NOT NULL,
            `date` date NOT NULL,
            `start_time` time DEFAULT NULL,
            `end_time` time DEFAULT NULL,
            `duration` int(11) DEFAULT 0 COMMENT "Duration in seconds",
            `description` text,
            `billable` tinyint(1) DEFAULT 1,
            `hourly_rate` decimal(10,2) DEFAULT 0.00,
            `category_id` int(11) DEFAULT NULL,
            `status` enum("draft","submitted","approved","rejected") DEFAULT "draft",
            `approved_by` int(11) DEFAULT NULL,
            `approved_at` datetime DEFAULT NULL,
            `invoice_id` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `task_id` (`task_id`),
            KEY `milestone_id` (`milestone_id`),
            KEY `staff_id` (`staff_id`),
            KEY `status` (`status`),
            KEY `billable` (`billable`),
            KEY `date` (`date`),
            KEY `category_id` (`category_id`),
            FOREIGN KEY (`project_id`) REFERENCES `' . db_prefix() . 'projects`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`staff_id`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE RESTRICT,
            FOREIGN KEY (`milestone_id`) REFERENCES `' . db_prefix() . 'project_milestones`(`id`) ON DELETE SET NULL,
            FOREIGN KEY (`category_id`) REFERENCES `' . db_prefix() . 'time_categories`(`id`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // 5. Create time_categories table
    if (!$CI->db->table_exists(db_prefix() . 'time_categories')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'time_categories` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(100) NOT NULL,
            `description` text,
            `default_billable` tinyint(1) DEFAULT 1,
            `color_code` varchar(7) DEFAULT "#3498db",
            `active` tinyint(1) DEFAULT 1,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `name` (`name`),
            KEY `active` (`active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // 6. Create project_resources table
    if (!$CI->db->table_exists(db_prefix() . 'project_resources')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'project_resources` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL,
            `staff_id` int(11) NOT NULL,
            `role` varchar(100) NOT NULL,
            `allocation_percentage` decimal(5,2) DEFAULT 100.00,
            `start_date` date NOT NULL,
            `end_date` date DEFAULT NULL,
            `hourly_rate` decimal(10,2) DEFAULT 0.00,
            `active` tinyint(1) DEFAULT 1,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `project_staff_unique` (`project_id`, `staff_id`),
            KEY `staff_id` (`staff_id`),
            KEY `active` (`active`),
            KEY `start_date` (`start_date`),
            FOREIGN KEY (`project_id`) REFERENCES `' . db_prefix() . 'projects`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`staff_id`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // 7. Create staff_skills table
    if (!$CI->db->table_exists(db_prefix() . 'staff_skills')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'staff_skills` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `staff_id` int(11) NOT NULL,
            `skill_name` varchar(100) NOT NULL,
            `proficiency_level` enum("beginner","intermediate","advanced","expert") DEFAULT "intermediate",
            `verified_by` int(11) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `staff_skill_unique` (`staff_id`, `skill_name`),
            KEY `staff_id` (`staff_id`),
            KEY `skill_name` (`skill_name`),
            FOREIGN KEY (`staff_id`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE CASCADE,
            FOREIGN KEY (`verified_by`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // 8. Create resource_availability table
    if (!$CI->db->table_exists(db_prefix() . 'resource_availability')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'resource_availability` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `staff_id` int(11) NOT NULL,
            `date` date NOT NULL,
            `available_hours` decimal(4,2) DEFAULT 8.00,
            `unavailable_reason` varchar(255) DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `staff_date_unique` (`staff_id`, `date`),
            KEY `staff_id` (`staff_id`),
            KEY `date` (`date`),
            FOREIGN KEY (`staff_id`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // 9. Create project_budgets table
    if (!$CI->db->table_exists(db_prefix() . 'project_budgets')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'project_budgets` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL,
            `category` varchar(100) NOT NULL,
            `allocated_amount` decimal(15,2) NOT NULL,
            `spent_amount` decimal(15,2) DEFAULT 0.00,
            `currency` varchar(3) DEFAULT "USD",
            `created_by` int(11) NOT NULL,
            `approved_by` int(11) DEFAULT NULL,
            `approved_at` datetime DEFAULT NULL,
            `created_at` datetime NOT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `category` (`category`),
            KEY `created_by` (`created_by`),
            KEY `approved_by` (`approved_by`),
            FOREIGN KEY (`project_id`) REFERENCES `' . db_prefix() . 'projects`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`created_by`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE RESTRICT,
            FOREIGN KEY (`approved_by`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // 10. Create budget_transactions table
    if (!$CI->db->table_exists(db_prefix() . 'budget_transactions')) {
        $CI->db->query('CREATE TABLE `' . db_prefix() . 'budget_transactions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `project_id` int(11) NOT NULL,
            `budget_id` int(11) NOT NULL,
            `amount` decimal(15,2) NOT NULL,
            `transaction_type` enum("expense","income","allocation","adjustment") DEFAULT "expense",
            `description` varchar(255) NOT NULL,
            `reference_id` int(11) DEFAULT NULL,
            `reference_type` varchar(50) DEFAULT NULL,
            `created_by` int(11) NOT NULL,
            `created_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `project_id` (`project_id`),
            KEY `budget_id` (`budget_id`),
            KEY `transaction_type` (`transaction_type`),
            KEY `created_by` (`created_by`),
            KEY `created_at` (`created_at`),
            FOREIGN KEY (`project_id`) REFERENCES `' . db_prefix() . 'projects`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`budget_id`) REFERENCES `' . db_prefix() . 'project_budgets`(`id`) ON DELETE CASCADE,
            FOREIGN KEY (`created_by`) REFERENCES `' . db_prefix() . 'staff`(`staffid`) ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=' . $CI->db->char_set . ';');
    }
    
    // Insert default time categories
    if ($CI->db->table_exists(db_prefix() . 'time_categories')) {
        $default_categories = [
            ['name' => 'Development', 'description' => 'Software development and coding', 'default_billable' => 1, 'color_code' => '#3498db'],
            ['name' => 'Testing', 'description' => 'Quality assurance and testing', 'default_billable' => 1, 'color_code' => '#e74c3c'],
            ['name' => 'Design', 'description' => 'UI/UX design and graphics', 'default_billable' => 1, 'color_code' => '#9b59b6'],
            ['name' => 'Meetings', 'description' => 'Client meetings and internal discussions', 'default_billable' => 0, 'color_code' => '#f39c12'],
            ['name' => 'Documentation', 'description' => 'Technical documentation and reporting', 'default_billable' => 1, 'color_code' => '#2ecc71'],
            ['name' => 'Research', 'description' => 'Research and planning', 'default_billable' => 0, 'color_code' => '#34495e'],
            ['name' => 'Support', 'description' => 'Customer support and maintenance', 'default_billable' => 1, 'color_code' => '#16a085'],
        ];
        
        foreach ($default_categories as $category) {
            $category['created_at'] = date('Y-m-d H:i:s');
            
            // Check if category already exists
            $exists = $CI->db->get_where(db_prefix() . 'time_categories', ['name' => $category['name']])->num_rows();
            if ($exists == 0) {
                $CI->db->insert(db_prefix() . 'time_categories', $category);
            }
        }
    }
    
    // Add module options with proper autoload settings
    add_option('project_enhancement_active', '1', 1);
    add_option('project_enhancement_auto_milestone_creation', '1', 1);
    add_option('project_enhancement_default_milestone_templates', 'planning,development,testing,deployment', 0);
    add_option('project_enhancement_time_tracking_enabled', '1', 1);
    add_option('project_enhancement_time_approval_required', '1', 1);
    add_option('project_enhancement_budget_tracking_enabled', '1', 1);
    add_option('project_enhancement_resource_management_enabled', '1', 1);
    add_option('project_enhancement_email_notifications', '1', 1);
    add_option('project_enhancement_client_access', '1', 1);
    add_option('project_enhancement_client_time_visibility', '1', 1);
    add_option('project_enhancement_client_budget_visibility', '0', 1);
    add_option('project_enhancement_client_team_visibility', '1', 1);
    add_option('project_enhancement_dashboard_widgets_enabled', '1', 1);
    add_option('project_enhancement_api_enabled', '0', 0);
    add_option('project_enhancement_cron_enabled', '1', 1);
    add_option('project_enhancement_default_hourly_rate', '50.00', 0);
    add_option('project_enhancement_currency', 'USD', 1);
    add_option('project_enhancement_timezone', 'UTC', 1);
    add_option('project_enhancement_working_hours_per_day', '8', 1);
    add_option('project_enhancement_working_days_per_week', '5', 1);
    
    // Complete transaction
    $CI->db->trans_complete();
    
    if ($CI->db->trans_status() === FALSE) {
        // Transaction failed
        throw new Exception('Database installation failed during transaction');
    }
    
    // Log successful installation
    log_activity('Project Enhancement Module: Database tables created successfully');
    
} catch (Exception $e) {
    // Log installation error
    log_activity('Project Enhancement Module Installation Error: ' . $e->getMessage());
    
    // Re-throw the exception to prevent module activation
    throw $e;
}