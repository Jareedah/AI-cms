# System Requirements Specification (SRS)
## PerfexCRM Project Management Enhancement Module

### Document Information
- **Document Version**: 1.0
- **Date**: Current
- **Module Name**: Project Management Enhancement (PME)
- **Module System Name**: `project_enhancement`
- **Target PerfexCRM Version**: 2.3.0+
- **Author**: Development Team Lead
- **Status**: Ready for Development

---

## 1. Executive Summary

### 1.1 Purpose
The Project Management Enhancement (PME) module extends PerfexCRM's core project functionality with advanced project tracking, resource management, time tracking, milestone management, and comprehensive reporting capabilities. This module demonstrates complete integration with all PerfexCRM systems including database, permissions, menus, themes, cron jobs, dashboard widgets, global search, and payment processing.

### 1.2 Scope
This module encompasses:
- Advanced project milestone tracking
- Resource allocation and management
- Time tracking with billing integration
- Project budget management
- Team collaboration tools
- Advanced reporting and analytics
- Client portal enhancements
- Email notifications and automation
- Payment integration for project billing
- Mobile-responsive interface

### 1.3 Success Criteria
- Seamless integration with existing PerfexCRM projects
- Zero conflicts with core functionality
- Performance impact < 5% on system load
- 100% security compliance
- Full mobile responsiveness
- Comprehensive test coverage

---

## 2. System Overview

### 2.1 Module Architecture
```
project_enhancement/
├── project_enhancement.php           # Module init file
├── install.php                      # Installation script
├── uninstall.php                    # Uninstallation script
├── upgrade.php                      # Version upgrade handler
├── config/
│   ├── csrf_exclude_uris.php        # CSRF exclusions for API
│   └── module_config.php            # Module configuration
├── controllers/
│   ├── Project_enhancement.php      # Main admin controller
│   ├── Api.php                      # REST API controller
│   ├── Reports.php                  # Reporting controller
│   └── Client_portal.php            # Client area controller
├── models/
│   ├── Project_enhancement_model.php # Main model
│   ├── Milestones_model.php         # Milestones management
│   ├── Time_tracking_model.php      # Time tracking
│   ├── Resources_model.php          # Resource management
│   └── Reports_model.php            # Reporting data
├── views/
│   ├── admin/                       # Admin interface views
│   ├── client/                      # Client portal views
│   ├── reports/                     # Report templates
│   └── widgets/                     # Dashboard widgets
├── libraries/
│   ├── Project_enhancement_module.php # Core business logic
│   ├── Time_tracker.php            # Time tracking engine
│   ├── Resource_manager.php        # Resource allocation
│   ├── Budget_calculator.php       # Budget management
│   ├── Notification_manager.php    # Email notifications
│   └── gateways/
│       └── Project_billing_gateway.php # Payment processing
├── helpers/
│   ├── project_enhancement_helper.php # Utility functions
│   └── reporting_helper.php        # Report generation
├── language/
│   ├── english/
│   │   └── project_enhancement_lang.php
│   └── [other_languages]/
├── assets/
│   ├── css/
│   │   ├── project_enhancement.css
│   │   └── mobile.css
│   ├── js/
│   │   ├── project_enhancement.js
│   │   ├── time_tracker.js
│   │   └── charts.js
│   └── images/
├── migrations/
│   ├── 001_initial_tables.php
│   ├── 002_add_budget_tracking.php
│   └── 003_add_api_tokens.php
├── vendor/                          # Composer dependencies
├── composer.json
└── index.html                       # Security file
```

### 2.2 Technology Stack
- **Backend**: PHP 8.1+, CodeIgniter Framework
- **Database**: MySQL 5.7+/8.0+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Charts**: Chart.js for analytics
- **API**: RESTful API with JWT authentication
- **Real-time**: WebSocket for live updates (optional)

---

## 3. Functional Requirements

### 3.1 Core Features

#### 3.1.1 Milestone Management
**Feature ID**: F001
**Priority**: High
**Description**: Advanced milestone tracking with dependencies and progress monitoring

**Requirements**:
- Create, edit, delete project milestones
- Set milestone dependencies (predecessor/successor relationships)
- Track milestone progress with percentage completion
- Automatic milestone status updates based on task completion
- Visual milestone timeline with Gantt-like display
- Milestone approval workflow
- Email notifications for milestone events

**Database Tables**:
```sql
tblproject_milestones (
    id, project_id, name, description, start_date, due_date, 
    completion_percentage, status, priority, staff_id, 
    predecessor_milestone_id, created_at, updated_at
)

tblmilestone_dependencies (
    id, milestone_id, depends_on_milestone_id, dependency_type, 
    created_at
)

tblmilestone_approvals (
    id, milestone_id, approver_staff_id, approval_status, 
    approval_date, comments, created_at
)
```

#### 3.1.2 Time Tracking System
**Feature ID**: F002
**Priority**: High
**Description**: Comprehensive time tracking with billing integration

**Requirements**:
- Start/stop timer functionality
- Manual time entry with validation
- Time categorization (development, testing, meetings, etc.)
- Billable vs non-billable time tracking
- Time approval workflow
- Integration with invoice generation
- Time tracking reports and analytics
- Mobile-friendly time tracking interface

**Database Tables**:
```sql
tbltime_entries (
    id, project_id, task_id, staff_id, start_time, end_time, 
    duration_minutes, description, billable, hourly_rate, 
    category, status, approved_by, approved_at, created_at
)

tbltime_categories (
    id, name, description, default_billable, color_code, 
    active, created_at
)
```

#### 3.1.3 Resource Management
**Feature ID**: F003
**Priority**: Medium
**Description**: Staff allocation and resource planning

**Requirements**:
- Staff allocation to projects with percentage allocation
- Resource availability calendar
- Skill-based resource matching
- Resource utilization reports
- Capacity planning tools
- Resource conflict detection
- Workload balancing recommendations

**Database Tables**:
```sql
tblproject_resources (
    id, project_id, staff_id, role, allocation_percentage, 
    start_date, end_date, hourly_rate, active, created_at
)

tblstaff_skills (
    id, staff_id, skill_name, proficiency_level, 
    verified_by, created_at
)

tblresource_availability (
    id, staff_id, date, available_hours, 
    unavailable_reason, created_at
)
```

#### 3.1.4 Budget Management
**Feature ID**: F004
**Priority**: High
**Description**: Project budget tracking and cost analysis

**Requirements**:
- Project budget allocation by category
- Real-time budget consumption tracking
- Budget vs actual cost analysis
- Budget alerts and notifications
- Cost forecasting based on current burn rate
- Multi-currency support
- Budget approval workflow

**Database Tables**:
```sql
tblproject_budgets (
    id, project_id, category, allocated_amount, spent_amount, 
    currency, created_by, approved_by, approved_at, created_at
)

tblbudget_transactions (
    id, project_id, budget_id, amount, transaction_type, 
    description, reference_id, reference_type, created_by, 
    created_at
)
```

### 3.2 Integration Features

#### 3.2.1 Dashboard Widgets
**Feature ID**: F005
**Priority**: Medium
**Description**: Project management dashboard widgets

**Requirements**:
- Project progress overview widget
- Time tracking summary widget
- Budget status widget
- Upcoming milestones widget
- Resource utilization widget
- Project health indicators

#### 3.2.2 Global Search Integration
**Feature ID**: F006
**Priority**: Low
**Description**: Search integration for all module entities

**Requirements**:
- Search milestones by name and description
- Search time entries by description and staff
- Search resources by skills and availability
- Search budget transactions

#### 3.2.3 Email Notifications
**Feature ID**: F007
**Priority**: Medium
**Description**: Automated email notifications

**Requirements**:
- Milestone deadline reminders
- Budget threshold alerts
- Time tracking approval notifications
- Resource allocation notifications
- Project status updates

### 3.3 API Features

#### 3.3.1 REST API
**Feature ID**: F008
**Priority**: Medium
**Description**: RESTful API for external integrations

**Requirements**:
- JWT-based authentication
- CRUD operations for all entities
- Rate limiting and throttling
- API documentation
- Webhook support for real-time updates

**Endpoints**:
```
GET/POST/PUT/DELETE /api/projects/{id}/milestones
GET/POST/PUT/DELETE /api/projects/{id}/time-entries
GET/POST/PUT/DELETE /api/projects/{id}/resources
GET/POST/PUT/DELETE /api/projects/{id}/budget
GET /api/reports/time-tracking
GET /api/reports/budget-analysis
```

---

## 4. Non-Functional Requirements

### 4.1 Performance Requirements
- Page load time < 2 seconds
- Database queries optimized with proper indexing
- Support for 1000+ concurrent users
- Efficient caching implementation
- Minimal memory footprint

### 4.2 Security Requirements
- Input validation and sanitization
- SQL injection prevention
- XSS protection
- CSRF token validation
- Role-based access control
- Audit logging for sensitive operations
- Secure API authentication

### 4.3 Usability Requirements
- Intuitive user interface
- Mobile-responsive design
- Accessibility compliance (WCAG 2.1)
- Consistent with PerfexCRM UI/UX
- Contextual help and tooltips

### 4.4 Compatibility Requirements
- PerfexCRM version 2.3.0+
- PHP 8.1+
- MySQL 5.7+/8.0+
- Modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile browsers (iOS Safari, Chrome Mobile)

---

## 5. Database Design

### 5.1 Entity Relationship Diagram
```
[Projects] 1----* [Milestones] 1----* [Milestone_Dependencies]
    |                   |
    |                   * [Milestone_Approvals]
    |
    |----* [Time_Entries] *----1 [Time_Categories]
    |           |
    |           *----1 [Staff]
    |
    |----* [Project_Resources] *----1 [Staff]
    |           |
    |           *----* [Staff_Skills]
    |
    |----* [Project_Budgets] 1----* [Budget_Transactions]
    |
    |----* [Resource_Availability]
```

### 5.2 Database Schema

#### 5.2.1 Core Tables
```sql
-- Project Milestones
CREATE TABLE `tblproject_milestones` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `project_id` int(11) NOT NULL,
    `name` varchar(255) NOT NULL,
    `description` text,
    `start_date` date NOT NULL,
    `due_date` date NOT NULL,
    `completion_percentage` decimal(5,2) DEFAULT 0.00,
    `status` enum('not_started','in_progress','completed','on_hold','cancelled') DEFAULT 'not_started',
    `priority` enum('low','medium','high','critical') DEFAULT 'medium',
    `staff_id` int(11) NOT NULL,
    `predecessor_milestone_id` int(11) DEFAULT NULL,
    `order_number` int(11) DEFAULT 0,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `project_id` (`project_id`),
    KEY `staff_id` (`staff_id`),
    KEY `status` (`status`),
    KEY `due_date` (`due_date`),
    FOREIGN KEY (`project_id`) REFERENCES `tblprojects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`staff_id`) REFERENCES `tblstaff`(`staffid`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Time Tracking
CREATE TABLE `tbltime_entries` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `project_id` int(11) NOT NULL,
    `task_id` int(11) DEFAULT NULL,
    `milestone_id` int(11) DEFAULT NULL,
    `staff_id` int(11) NOT NULL,
    `start_time` datetime NOT NULL,
    `end_time` datetime DEFAULT NULL,
    `duration_minutes` int(11) DEFAULT 0,
    `description` text,
    `billable` tinyint(1) DEFAULT 1,
    `hourly_rate` decimal(10,2) DEFAULT 0.00,
    `category_id` int(11) DEFAULT NULL,
    `status` enum('draft','submitted','approved','rejected') DEFAULT 'draft',
    `approved_by` int(11) DEFAULT NULL,
    `approved_at` datetime DEFAULT NULL,
    `invoice_id` int(11) DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `project_id` (`project_id`),
    KEY `staff_id` (`staff_id`),
    KEY `status` (`status`),
    KEY `billable` (`billable`),
    KEY `start_time` (`start_time`),
    FOREIGN KEY (`project_id`) REFERENCES `tblprojects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`staff_id`) REFERENCES `tblstaff`(`staffid`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Resource Management
CREATE TABLE `tblproject_resources` (
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
    FOREIGN KEY (`project_id`) REFERENCES `tblprojects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`staff_id`) REFERENCES `tblstaff`(`staffid`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Budget Management
CREATE TABLE `tblproject_budgets` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `project_id` int(11) NOT NULL,
    `category` varchar(100) NOT NULL,
    `allocated_amount` decimal(15,2) NOT NULL,
    `spent_amount` decimal(15,2) DEFAULT 0.00,
    `currency` varchar(3) DEFAULT 'USD',
    `created_by` int(11) NOT NULL,
    `approved_by` int(11) DEFAULT NULL,
    `approved_at` datetime DEFAULT NULL,
    `created_at` datetime NOT NULL,
    `updated_at` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `project_id` (`project_id`),
    KEY `category` (`category`),
    FOREIGN KEY (`project_id`) REFERENCES `tblprojects`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`created_by`) REFERENCES `tblstaff`(`staffid`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### 5.3 Indexing Strategy
- Primary keys on all tables
- Foreign key constraints with proper cascading
- Composite indexes for frequently queried combinations
- Indexes on status and date fields for reporting
- Full-text indexes on description fields for search

---

## 6. User Interface Requirements

### 6.1 Admin Interface

#### 6.1.1 Project Enhancement Dashboard
- Overview of all enhanced projects
- Quick stats: active milestones, time tracked, budget status
- Recent activities feed
- Resource utilization chart
- Budget consumption trends

#### 6.1.2 Milestone Management Interface
- Gantt chart view of project milestones
- Kanban board for milestone status tracking
- Milestone dependency visualization
- Bulk milestone operations
- Milestone templates for common project types

#### 6.1.3 Time Tracking Interface
- Timer widget with start/stop functionality
- Time entry forms with auto-completion
- Time approval interface for managers
- Time tracking reports with filtering
- Billable hours analysis

#### 6.1.4 Resource Management Interface
- Resource allocation calendar
- Staff workload visualization
- Skill matrix management
- Resource conflict resolution
- Capacity planning tools

### 6.2 Client Portal Interface

#### 6.2.1 Project Progress View
- Visual project timeline with milestones
- Progress indicators and completion percentages
- Time tracking summaries (if permitted)
- Budget status (if permitted)
- Milestone approval interface

#### 6.2.2 Communication Tools
- Project-specific messaging
- File sharing with version control
- Milestone comment threads
- Status update notifications

### 6.3 Mobile Interface
- Responsive design for all screen sizes
- Touch-optimized time tracking
- Offline capability for time entries
- Push notifications for important updates

---

## 7. Integration Requirements

### 7.1 PerfexCRM Core Integration

#### 7.1.1 Menu Integration
```php
// Admin menu items
$CI->app_menu->add_sidebar_menu_item('project-enhancement', [
    'name'     => _l('project_enhancement'),
    'collapse' => true,
    'position' => 31,
    'icon'     => 'fa fa-project-diagram',
]);

$CI->app_menu->add_sidebar_children_item('project-enhancement', [
    'slug'     => 'milestones',
    'name'     => _l('milestones'),
    'href'     => admin_url('project_enhancement/milestones'),
    'position' => 1,
]);

// Client area menu
add_theme_menu_item('project-progress', [
    'name'     => _l('project_progress'),
    'href'     => site_url('project_enhancement/progress'),
    'position' => 12,
]);
```

#### 7.1.2 Permission Integration
```php
register_staff_capabilities('project_enhancement', [
    'capabilities' => [
        'view'              => _l('permission_view'),
        'create'            => _l('permission_create'),
        'edit'              => _l('permission_edit'),
        'delete'            => _l('permission_delete'),
        'manage_milestones' => _l('manage_milestones'),
        'track_time'        => _l('track_time'),
        'approve_time'      => _l('approve_time'),
        'manage_resources'  => _l('manage_resources'),
        'view_budget'       => _l('view_budget'),
        'manage_budget'     => _l('manage_budget'),
    ]
], _l('project_enhancement'));
```

#### 7.1.3 Hook Integration
```php
// Project lifecycle hooks
hooks()->add_action('after_project_added', 'pe_setup_default_milestones');
hooks()->add_action('project_status_changed', 'pe_update_milestone_status');
hooks()->add_action('task_status_changed', 'pe_update_milestone_progress');

// Invoice integration
hooks()->add_filter('invoice_items_data', 'pe_add_time_entries_to_invoice');
hooks()->add_action('after_invoice_added', 'pe_mark_time_entries_invoiced');

// Dashboard widgets
hooks()->add_filter('get_dashboard_widgets', 'pe_add_dashboard_widgets');

// Global search
hooks()->add_filter('global_search_result_query', 'pe_global_search', 10, 3);
hooks()->add_filter('global_search_result_output', 'pe_search_output', 10, 2);
```

### 7.2 Third-Party Integrations

#### 7.2.1 Payment Gateway Integration
- Custom payment gateway for project billing
- Integration with existing payment methods
- Recurring billing for retainer projects
- Multi-currency support

#### 7.2.2 Calendar Integration
- Google Calendar sync for milestones
- Outlook integration for resource scheduling
- iCal export for project timelines

#### 7.2.3 External Tool Integration
- Slack notifications for project updates
- Jira integration for issue tracking
- GitHub integration for development projects

---

## 8. Security Requirements

### 8.1 Authentication & Authorization
- Integration with PerfexCRM's role-based access control
- API token-based authentication for external access
- Session management and timeout handling
- Multi-factor authentication support (optional)

### 8.2 Data Protection
- Encryption of sensitive data at rest
- Secure transmission of all data (HTTPS)
- Input validation and sanitization
- SQL injection prevention
- XSS protection

### 8.3 Audit & Logging
- Comprehensive audit trail for all operations
- User action logging
- Data change tracking
- Security event monitoring
- GDPR compliance for data handling

### 8.4 Access Control Matrix

| Role | View Projects | Manage Milestones | Track Time | Approve Time | Manage Budget | View Reports |
|------|---------------|-------------------|------------|--------------|---------------|--------------|
| Admin | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Project Manager | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Team Lead | ✓ | ✓ | ✓ | ✓ | ✗ | ✓ |
| Developer | ✓ | ✗ | ✓ | ✗ | ✗ | ✗ |
| Client | ✓ (own) | ✗ | ✗ | ✗ | ✗ | ✓ (own) |

---

## 9. Testing Requirements

### 9.1 Unit Testing
- Model method testing
- Helper function testing
- Library class testing
- API endpoint testing
- Minimum 80% code coverage

### 9.2 Integration Testing
- Database integration testing
- PerfexCRM core integration testing
- Third-party service integration testing
- Email notification testing
- Payment gateway testing

### 9.3 User Acceptance Testing
- Admin interface testing
- Client portal testing
- Mobile interface testing
- Performance testing
- Security testing

### 9.4 Test Data Requirements
- Sample projects with various statuses
- Test users with different roles
- Mock time entries and milestones
- Test budget and resource data
- Performance test datasets

---

## 10. Deployment Requirements

### 10.1 Installation Process
1. Module file upload to `/modules/project_enhancement/`
2. Database migration execution
3. Permission setup and role assignment
4. Default configuration setup
5. Cache clearing and optimization

### 10.2 Configuration Requirements
- Module activation in admin panel
- Permission assignment to user roles
- Email template configuration
- Payment gateway setup (if applicable)
- API configuration and token generation

### 10.3 Migration Strategy
- Backward compatibility with existing projects
- Data migration from core project tables
- Gradual feature rollout capability
- Rollback procedures for failed deployments

---

## 11. Maintenance & Support

### 11.1 Version Management
- Semantic versioning (MAJOR.MINOR.PATCH)
- Database migration scripts for upgrades
- Backward compatibility maintenance
- Deprecation notices for breaking changes

### 11.2 Documentation Requirements
- Technical documentation for developers
- User manual for administrators
- API documentation with examples
- Installation and configuration guide
- Troubleshooting guide

### 11.3 Support Procedures
- Bug reporting and tracking system
- Feature request management
- Performance monitoring and optimization
- Security update procedures
- Community support forum

---

## 12. Success Metrics

### 12.1 Performance Metrics
- Module activation success rate: > 95%
- Page load time impact: < 10%
- Database query efficiency: < 100ms average
- Memory usage impact: < 50MB additional
- User adoption rate: > 70% within 3 months

### 12.2 Quality Metrics
- Bug density: < 1 bug per 1000 lines of code
- Code coverage: > 80%
- Security vulnerabilities: 0 critical, < 5 medium
- User satisfaction: > 4.5/5 rating
- Documentation completeness: 100%

---

## 13. Risk Assessment

### 13.1 Technical Risks
- **Database Performance**: Large time tracking datasets may impact performance
  - *Mitigation*: Implement data archiving and indexing strategies
- **Integration Conflicts**: Potential conflicts with other modules
  - *Mitigation*: Comprehensive testing and namespace isolation
- **Security Vulnerabilities**: Complex permission system may introduce security gaps
  - *Mitigation*: Security audits and penetration testing

### 13.2 Business Risks
- **User Adoption**: Complex interface may hinder user adoption
  - *Mitigation*: Intuitive UI design and comprehensive training materials
- **Performance Impact**: Module may slow down core system
  - *Mitigation*: Performance optimization and caching strategies
- **Maintenance Overhead**: Complex module requires ongoing maintenance
  - *Mitigation*: Comprehensive documentation and automated testing

---

## 14. Conclusion

This SRS document outlines a comprehensive test module that demonstrates mastery of all PerfexCRM module development aspects. The Project Management Enhancement module integrates deeply with the platform's core systems while providing significant value to users.

The module showcases:
- ✅ Complex database design with proper relationships
- ✅ Complete PerfexCRM integration (menus, permissions, hooks)
- ✅ Advanced features (time tracking, budgeting, reporting)
- ✅ Security best practices and audit compliance
- ✅ Modern UI/UX with mobile responsiveness
- ✅ API development and third-party integrations
- ✅ Comprehensive testing and deployment strategies

This specification serves as both a development roadmap and a demonstration of the technical expertise required for professional PerfexCRM module development.

---

**Document Status**: Ready for Development Review and Approval