<?php
/**
 * @author Seungchul Lee, Jiwwong Yoon
 */

class Forget_Model extends Model {
    
    var $email;
    
    function __construct() 
    {
        parent::__construct();
    }

    public function askPassword()
    {
        if($this->email_checker($_POST['email']))
        {  
            $this->email = $_POST['email'];
            $reset_code = $this->randomPasswordGenetator(32);
            $to  = $_POST['email'];
            $subject = 'Password Reset';
            $message = '<html>
                        <head>
                          <title>Password Reset</title>
                        </head>
                        <body>
                          <p>Your new password: '.
                          $reset_code
                         .'</p>
                        <a href="https://github.com/sclee8611/CustomizedSocialPage">Go To Website</a>
                        </body>
                        </html>';

            $headers  = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'Reply-To: Admin<' . ADMIN_EMAIL . ">\r\n";

            $success = mail($to, $subject, $message, $headers);
            $this->resetCode($reset_code);
            header('location: ../forget/success');
            exit;
        }
        else
        {
            header('location: ../forget/fail');
            exit;
        }
    }
    
    public function resetPassword()
    {
        $state = $this->db->prepare("UPDATE users SET password = :password, reset = :new WHERE reset = :reset");
        $state->execute(array(
            ':password' => md5($_POST['new_password']),
            ':new' => null,
            ':reset' => $_POST['reset']
        ));
        
        header('location: '. URL);
    }
    
    private function resetCode($reset_code = null)
    {
        $state = $this->db->prepare("UPDATE users SET reset = :reset WHERE email = :email");
        $state->execute(array(
            ':reset' => $reset_code,
            ':email' => $this->email
        ));
    }
    
    private function email_checker($email = null)
    {
        if (empty($email) || !isset($email))
        {
            return false;
        }
        
        $state = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $state->execute(array(
            ':email' => $email
        ));
        
        $count = $state->rowCount();
        if($count > 0)
        {
            // change password in db
            
            return true;
        }
        
        return false;
    }
    
    private function randomPasswordGenetator($length = 8)
    {
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $password = substr( str_shuffle( $chars ), 0, $length );
        return $password;
    }
}