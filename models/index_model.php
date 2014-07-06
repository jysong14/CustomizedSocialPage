<?php
/**
 * @author Seungchul
 * @date   July 5, 2014
 */

class Index_Model extends Model {

    function __construct() 
    {
        parent::__construct();
    }
    
    public function checkReturnUser()
    {
        $key = pack("H*", MYKEY);
        $rememberMe = new RememberMe($key);

        // Check if remember me is present
        if ($data = $rememberMe->auth()) 
        {
            $rememberMe->remember($data['user']);
            Session::set('loggedIn', true);
            Session::set('username', $data['user']);
            return true;
        } 
        else
        {
            echo 'Not returned User</br>';
            return false;
        }
    }

    public function login($login, $password)
    {
        if(empty($login) && empty($_POST['login']))
        {
            header('location: '.URL);
            exit;
        }
        elseif(!empty($_POST['login']))
        {
            $login = $_POST['login'];
            $password = $_POST['password'];
        }

        $statement = $this->db->select(array("id"), "users", array("login", "password"), array($login, MD5($password)));
        if(!$statement){
            throw new Exception('Query failed.');
        }
               
        $count = $statement->rowCount();
        if($count > 0)
        {
            Session::set('loggedIn', true);
            Session::set('username', $login);
            
            if (!empty($_POST['keep_loggedIn']) || isset($_POST['keep_loggedIn']))
            {
                $key = pack("H*", MYKEY); 
                $rememberMe = new RememberMe($key);
                $rememberMe->remember($login);
            }
            
            header('location: '.URL);
        }
        else
        {
            Session::set('loginFailed', true);
            header('location: '.URL);
        }
    }
    
    public function logout()
    {
        $this->db->update("users", 
                        array("remember_info"), 
                        array(null), 
                        array("login"), 
                        array(Session::get('username')));
        
        Cookie::remove('auto');
        
        Session::destroy();
        header('location: '.URL);
        exit;
    }
}