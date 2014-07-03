<?php
/**
 * @author Seungchul Lee
 */

class Index extends Controller {

    function __construct() 
    {
        parent::__construct();
    }

    public function index() 
    {
        if (Session::get('loggedIn') == true)
        {
            $this->view->render('index/main');
        }
        else
        {
            if(isset($_SESSION['loginFailed'])){
                $this->view->login_failed = Session::get('loginFailed');
            }
            $this->view->render('index/signup');
        }
    }
    
    public function login($login = false, $password = false) 
    {
        $this->model->login($login, $password);
    }
    
    public function logout()
    {
        $this->model->logout();
    }
}