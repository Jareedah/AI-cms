<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Project_mgmt extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        
        // Check if module is active
        if (!get_option('project_mgmt_active')) {
            show_404();
        }
    }

    /**
     * Main dashboard
     */
    public function index()
    {
        // Check permissions
        if (!staff_can('view', 'project_mgmt')) {
            access_denied('Project Management Plus');
        }

        $data['title'] = _l('project_mgmt_plus');
        
        $this->load->view('admin/project_mgmt/dashboard', $data);
    }
}