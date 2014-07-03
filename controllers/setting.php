<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class Setting extends Controller {

    function __construct() {
        parent::__construct();
        
        if(Session::get('loggedIn') == null)
        {
            $this->view->render('error/index');
            exit;
        }
    }

    public function index()
    {
        $this->view->render('setting/index');
    }
    
    public function changePassword()
    {
        $this->view->render('setting/changePassword');
    }
}