# Development Notes & Observations

## Critical Points to Remember

### 1. Module Naming Conventions
- **Folder name** must match **init file name** exactly
- Use lowercase with underscores for folder/file names
- Use PascalCase for class names
- Constants should be UPPERCASE with module prefix

### 2. Security Must-Haves
- `defined('BASEPATH') or exit('No direct script access allowed');` in ALL PHP files
- Empty `index.html` files in ALL directories
- Always use `$this->input->post()` and `$this->input->get()` for user input
- Never trust user input - validate everything
- Use `db_prefix()` for all table names

### 3. Permission System Gotchas
- Always check permissions in controller methods
- Use `staff_can('action', 'feature')` for permission checks
- Use `access_denied('Module Name')` for unauthorized access
- Register permissions with `register_staff_capabilities()`

### 4. Database Best Practices
- Always use `db_prefix()` function for table names
- Use CodeIgniter Active Record for queries
- Include created_at/updated_at timestamps
- Add proper indexes for performance
- Handle foreign key relationships properly

### 5. Hook System Patterns
- Use `admin_init` for admin area setup
- Use `clients_init` for client area setup  
- Use `before_` and `after_` hooks for entity operations
- Priority matters - higher numbers execute later
- Return modified data in filter hooks

### 6. Common Pitfalls
- Forgetting to register language files
- Not including security files (index.html)
- Missing permission checks in controllers
- Hardcoding table names instead of using db_prefix()
- Not handling module activation/deactivation properly

## Development Workflow

### 1. Module Planning Phase
- [ ] Define module purpose and scope
- [ ] Plan database schema
- [ ] Identify required permissions
- [ ] Plan menu structure
- [ ] Identify integration points

### 2. Basic Structure Setup
- [ ] Create module folder with proper name
- [ ] Create init file with headers
- [ ] Add security files (index.html) to all directories
- [ ] Set up basic MVC structure

### 3. Core Implementation
- [ ] Implement database schema in install.php
- [ ] Create model with CRUD operations
- [ ] Implement controller with permission checks
- [ ] Create views with proper styling
- [ ] Add language files

### 4. Integration Phase
- [ ] Add menu items (admin/client)
- [ ] Register permissions
- [ ] Add dashboard widgets (if needed)
- [ ] Implement global search (if applicable)
- [ ] Add cron tasks (if needed)

### 5. Testing & Polish
- [ ] Test all CRUD operations
- [ ] Test permission system
- [ ] Test with different user roles
- [ ] Validate input handling
- [ ] Check for PHP errors/warnings

## Code Snippets for Quick Reference

### Basic Controller Structure
```php
class Your_module extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('your_module/your_module_model');
    }
    
    public function index()
    {
        if (!staff_can('view', 'your_module')) {
            access_denied('Your Module');
        }
        // Implementation
    }
}
```

### Basic Model Structure
```php
class Your_module_model extends App_Model
{
    public function get($id = '', $where = [])
    {
        $this->db->select('*');
        $this->db->from(db_prefix() . 'your_module_items');
        
        if (is_numeric($id)) {
            $this->db->where('id', $id);
            return $this->db->get()->row();
        }
        
        return $this->db->get()->result_array();
    }
}
```

### Menu Registration
```php
function your_module_init_admin_menu()
{
    $CI = &get_instance();
    $CI->app_menu->add_sidebar_menu_item('your-module', [
        'name'     => _l('your_module'),
        'href'     => admin_url('your_module'),
        'position' => 55,
        'icon'     => 'fa fa-cog',
    ]);
}
```

## Testing Checklist

### Functionality Tests
- [ ] Module activates without errors
- [ ] Module deactivates cleanly
- [ ] All CRUD operations work
- [ ] Permission system functions correctly
- [ ] Menu items appear properly
- [ ] Dashboard widgets display correctly

### Security Tests
- [ ] Unauthorized access is blocked
- [ ] Input validation works
- [ ] SQL injection protection
- [ ] XSS protection
- [ ] CSRF protection (where applicable)

### Compatibility Tests
- [ ] Works with different PHP versions
- [ ] Compatible with PerfexCRM version requirements
- [ ] No conflicts with other modules
- [ ] Responsive design works on mobile

## Performance Considerations

### Database Optimization
- Add indexes on frequently queried columns
- Use LIMIT clauses for large datasets
- Avoid N+1 query problems
- Use database caching where appropriate

### Code Optimization
- Minimize hook usage (only register what you need)
- Cache expensive operations
- Optimize asset loading
- Use lazy loading for large datasets

## Debugging Tips

### Common Issues
1. **Module not appearing**: Check folder/file naming
2. **Permission errors**: Verify permission registration
3. **Database errors**: Check table prefix usage
4. **Menu not showing**: Verify hook registration
5. **Styling issues**: Check asset loading

### Debugging Tools
- Enable development mode for error visibility
- Use browser developer tools for JS/CSS issues
- Check PHP error logs
- Use database query profiling
- Test with different user roles